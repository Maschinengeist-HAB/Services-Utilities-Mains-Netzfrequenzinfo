# Services-Utilities-Mains-Netzfrequenzinfo
Gateway from the Netzfrequenz.info european power grid frequency to MQTT

## Service description
[Netzfrequenz.info](https://www.netzfrequenz.info/) provides an [API](https://www.netzfrequenz.info/json/act.json) to a self hosted mains frequency monitor for the German/European Grid. This service collects an amount of values and
transmitts them via MQTT, enriched with some meta information (min, max, average and the 
[load shedding level](https://de.wikipedia.org/wiki/Unterfrequenz)).

## Configuration
The service uses a set of environment variables for configuration in the Dockerfile:

### Connection settings

| Variable                     | Usage                                                                          | Default value                                              |
|------------------------------|--------------------------------------------------------------------------------|------------------------------------------------------------|
| `MQTT_HOST`                  | Specifies the MQTT broker host name                                            | `message-broker`                                           |
| `MQTT_PORT`                  | Specifies the MQTT port                                                        | `1883`                                                     |
| `MQTT_USERNAME`              | Username for the MQTT connection                                               | none                                                       |
| `MQTT_PASSWORD`              | Password for the MQTT connection                                               | none                                                       |
| `MQTT_KEEP_ALIVE`            | Keep alive the connection to the MQTT broker every *n* seconds                 | `120`                                                      |
| `MQTT_BASE_TOPIC`            | MQTT base topic, will prepend to the defined topics, i.e. `base_topic/command` | `maschinengeist/services/utilities/mains/netzfrequenzinfo` |
| `TZ`                         | Timezone                                                                       | `Europe/Berlin`                                            |
| `MAX_COLLECTED_FREQUENCIES`  | Maximum collected frequencies before transmit                                  | `20`                                                       |
| `MAX_CONNECTION_ERROR_COUNT` | Maximum connection errors to the API before aborting                           | `10`                                                       |
| `SERVICE_SLEEP_TIME`         | Sleep time between two requests                                                | `5`                                                        |

## How to pull and run this image
Pull this image by

    docker pull ghcr.io/maschinengeist-hab/services-utilities-mains-netzfrequenzinfo:latest

Run this image by

    docker run -d --name mg-netzfrequenzifo-service ghcr.io/maschinengeist-hab/services-utilities-mains-netzfrequenzinfo:latest

## Result example
    
    {
      "LAS": false,
      "lost_values": 1,
      "frequency": {
        "values": {
          "1681997039": "49.972",
          "1681997044": "49.978",
          "1681997050": "49.983",
          "1681997055": "49.988",
          "1681997060": "49.984",
          "1681997066": "49.986",
          "1681997071": "49.986",
          "1681997076": "49.993",
          "1681997082": "49.987",
          "1681997087": "49.984",
          "1681997092": "49.987",
          "1681997098": "49.99",
          "1681997103": "49.984",
          "1681997109": "49.98",
          "1681997114": "49.972",
          "1681997119": "49.976",
          "1681997125": "49.974",
          "1681997130": "49.98",
          "1681997135": "49.98",
          "1681997141": "49.977"
        },
        "count": 20,
        "max": 49.993,
        "min": 49.972,
        "avg": 49.98205
      }
    }

### Glossary

<dl>
    <dt>LAS</dt>
    <dd>Lastabwurfstufe, load shedding level</dd>
    <dt>Lost values</dt>
    <dd>If there is an connection error to the API, the requests result can not be collected, but will by counted in
        the <code>lost_values</code> field. Resets after <code>MAX_COLLECTED_FREQUENCIES</code> successful calls.
    </dd>
    <dt>max</dt>
    <dd>Maximum measured frequency in Hertz in the last collection period</dd>
    <dt>min</dt>
    <dd>Minimum measured frequency in Hertz in the last collection period</dd>
    <dt>avg</dt>
    <dd>Average measured frequency in Hertz in the last collection period</dd>
    <dt>count</dt>
    <dd>Count of collected frequency values</dd>
</dl>

## License

    Copyright 2023 Christoph 'knurd' Morrison

    Licensed under the MIT license:

    http://www.opensource.org/licenses/mit-license.php

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:
    
    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.
    
    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.