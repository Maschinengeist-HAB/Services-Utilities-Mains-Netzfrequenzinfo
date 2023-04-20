FROM php:8.1-alpine

ENV MQTT_HOST=message-broker \
    MQTT_PORT=1883 \
    TZ=Europe/Berlin


LABEL org.opencontainers.image.source="https://github.com/Maschinengeist-HAB/Services-Utilities-Mains-Netzfrequenzinfo"
LABEL org.opencontainers.image.description="Gateway from the Netzfrequenz.info european power grid frequency to MQTT"
LABEL org.opencontainers.image.licenses="MIT"

COPY Service /opt/Service
COPY Library /opt/Library

VOLUME [ "/opt/Service" ]
WORKDIR "/opt/Service/"
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
CMD ["sh", "./Entry.sh"]