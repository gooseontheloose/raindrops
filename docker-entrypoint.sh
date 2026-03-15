#!/bin/bash
# Patch Apache port at container RUNTIME when $PORT is an actual resolved value
PORT="${PORT:-8080}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf
exec "$@"
