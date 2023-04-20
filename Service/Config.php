<?php
namespace Maschinengeist\Services\Utilities\Mains\Netzfrequenzinfo;
class Config {

    public static function getVersion() : string {
        return '1.0.0';
    }

    public static function getMqttHost() : string {
        return $_ENV['MQTT_HOST'] ?? 'message-broker';
    }

    public static function getMqttPort() : int {
        return (int) ($_ENV['MQTT_PORT'] ?? 1883);
    }

    public static function getMqttUsername() : string {
        return $_ENV['MQTT_USERNAME'] ?? '';
    }

    public static function getMqttPassword() : string {
        return $_ENV['MQTT_PASSWORD'] ?? '';
    }

    public static function getMqttKeepAlive() : bool {
        return $_ENV['MQTT_KEEP_ALIVE'] ?? true;
    }

    public static function getNetzfrequenzInfoApiUrl() : string {
        return 'https://www.netzfrequenz.info/json/act.json';
    }

    public static function getNetzfrequenzInfoBaseTopic() : string {
        return $_ENV['MQTT_BASE_TOPIC'] ?? 'maschinengeist/services/utilities/mains/netzfrequenzinfo';
    }

    public static function getNetzfrequenzDataTopic() : string {
        return self::getNetzfrequenzInfoBaseTopic() . '/data';
    }

    public static function getSleepTime() : int {
        return (int) ($_ENV['SERVICE_SLEEP_TIME'] ?? 5);
    }

    public static function getMaxCollectedFrequencies() : int {
        return (int) ($_ENV['MAX_COLLECTED_FREQUENCIES'] ?? 20);
    }

    public static function getUnderFrequencyHz() : float {
        return 49.9;
    }

    public static function getMaxConnectionErrorCount() : int {
        return (int) ($_ENV['MAX_CONNECTION_ERROR_COUNT'] ?? 10);
    }

    public static function getCurrentConfig(): array {
        return array(
            'mqtt topics' => array(
                'base' => self::getNetzfrequenzInfoBaseTopic(),
                'result' => self::getNetzfrequenzDataTopic(),
            ),
            'connection data' => array(
                'host' => self::getMqttHost(),
                'port' => self::getMqttPort(),
                'user' => self::getMqttUsername(),
                'password' => self::getMqttPassword(),
                'keep alive' => self::getMqttKeepAlive(),
            ),
            'application config' => array(
                'max collected values before transmission' => self::getMaxCollectedFrequencies(),
                'under frequency threshold in Hz' => self::getUnderFrequencyHz(),
                'max api connection error threshold before aborting' => self::getMaxConnectionErrorCount(),
                'sleep time' => self::getSleepTime(),
            ),
        );
    }
}