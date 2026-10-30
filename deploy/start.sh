#!/bin/sh
set -eu
app_port="${PORT:-8080}"
case "$app_port" in ''|*[!0-9]*) echo 'PORT must be numeric' >&2; exit 1 ;; esac
if [ "$app_port" -lt 1 ] || [ "$app_port" -gt 65535 ]; then
    echo 'PORT must be between 1 and 65535' >&2
    exit 1
fi
install -d -m 0700 -o www-data -g www-data "$SKILLSWAP_DATA_DIR" /var/lib/skillswap/sessions
sed -i "s/^Listen 80$/Listen $app_port/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$app_port>/" /etc/apache2/sites-available/000-default.conf
exec apache2-foreground
