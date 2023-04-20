#! /usr/bin/env php
<?php
# ------------------------------------------------------------------------------------------ global
namespace Maschinengeist\Services\Utilities\Mains\Netzfrequenzinfo;

use Exception;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

error_reporting(E_ALL);
date_default_timezone_set($_ENV['TZ'] ?? 'Europe/Berlin');
define('SERVICE_NAME', 'maschinengeist-services-utilities-mains-netzfrequenzinfo');
# ------------------------------------------------------------------------------------------ resolve dependencies
require_once __DIR__ . '/vendor/autoload.php';

spl_autoload_register(
/**
 * @param $class_name
 * @return void
 */
    function ($class_name) {
        $class_name = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
        require '/opt/Library/' . $class_name . '.php';
    }
);

set_error_handler(
    function ($severity, $message, $file, $line) {
        throw new \ErrorException($message, $severity, $severity, $file, $line);
    }
);

# ------------------------------------------------------------------------------------------ configuration
require_once 'Config.php';

# ------------------------------------------------------------------------------------------ helper

/**
 * @param float $frequency
 * @return string|bool
 */
function getLoadSheddingLevel(float $frequency) : string|bool {

    if ($frequency >= 47.5) {
        return 'BLACKOUT';
    }

    if ($frequency >= 48.4) {
        return 'LAS4';
    }

    if ($frequency >= 48.6) {
        return 'LAS3';
    }

    if ($frequency >= 48.8) {
        return 'LAS2';
    }

    if ($frequency >= 49.0) {
        return 'LAS1';
    }

    if ($frequency >= 49.2) {
        return 'LAS0.2';
    }

    if ($frequency >= 49.8) {
        return 'LAS0.1';
    }

    return false;
}

function average(array $array, bool $includeEmpties = true): float {
    $array = array_filter($array, fn($v) => (
        $includeEmpties ? is_numeric($v) : is_numeric($v) && ($v > 0)
    ));

    return array_sum($array) / count($array);
}

# ------------------------------------------------------------------------------------------ banner
error_log(sprintf('Welcome to the %s, v%s', SERVICE_NAME, Config::getVersion()));
error_log("Config is:");
error_log(print_r(Config::getCurrentConfig(), true));

# ------------------------------------------------------------------------------------------ main

$mqttConnectionSettings = (new ConnectionSettings)
    ->setKeepAliveInterval(Config::getMqttKeepAlive());

if (Config::getMqttUsername()) {
    $mqttConnectionSettings->setUsername(Config::getMqttUsername());
}

if (Config::getMqttPassword()) {
    $mqttConnectionSettings->setPassword(Config::getMqttPassword());
}

try {
    $mqttClient = new MqttClient(Config::getMqttHost(), Config::getMqttPort(), SERVICE_NAME);
} catch (\Exception $exception) {
    trigger_error('Cannot create mqtt object: ' . $exception->getMessage());
}

$stored_frequencies         = array();
$stored_frequencies_count   = 0;
$last_las                   = false;
$lost_values                = 0;
$contact_error_count        = 0;

do {

    if ($contact_error_count > Config::getMaxConnectionErrorCount()) {
        error_log("Too much remote connection errors. Aborting");
        exit(121);
    }

    try {
        $current_frequency = file_get_contents(Config::getNetzfrequenzInfoApiUrl());
    } catch (\ErrorException $e) {
        error_log("Error retrieving current mains frequency: " . $e->getMessage());
        $contact_error_count++;
        $lost_values++;
        continue;
    }

    if (!$current_frequency) {
        $lost_values++;
        sleep(Config::getSleepTime());
        continue;
    }

    $stored_frequencies[time()] = $current_frequency;
    $stored_frequencies_count++;

    if ($current_frequency < Config::getUnderFrequencyHz()) {
        $last_las = getLoadSheddingLevel($current_frequency);
    }

    if ($stored_frequencies_count == Config::getMaxCollectedFrequencies()) {

        $publish_data = array(
            'LAS'           => $last_las,
            'lost_values'   => $lost_values,
            'frequency'     => array(
                'values'        => $stored_frequencies,
                'count'         => $stored_frequencies_count,
                'max'           => (float) max(array_values($stored_frequencies)),
                'min'           => (float) min(array_values($stored_frequencies)),
                'avg'           => average(array_values($stored_frequencies)),
            )
        );

        try {
            $mqttClient->connect($mqttConnectionSettings);
        } catch (Exception $e) {
            error_log("Can't connect to MQTT broker: " . $e->getMessage());
        }

        try {
            $mqttClient->publish(Config::getNetzfrequenzDataTopic(), json_encode($publish_data), false);
        } catch (Exception $e) {
            error_log(
                sprintf(
                "Publishing to %s is not possible, reason is %s",
                Config::getNetzfrequenzDataTopic(), $e->getMessage()
                )
            );
        }

        try {
            $mqttClient->disconnect();
        } catch (Exception $e) {
            error_log("Disconnecting from MQTT broker was not successful: " . $e->getMessage());
        }

        # reset values
        $stored_frequencies = array();
        $stored_frequencies_count = 0;
        $last_las = false;
        $lost_values = 0;
        $contact_error_count = 0;
    }

    sleep(Config::getSleepTime());

} while ( true );