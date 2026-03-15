FROM php:8.2-cli

# Copy website files
COPY . /var/www/html/

WORKDIR /var/www/html

# Create the persistent data directory and set permissions
RUN mkdir -p /var/www/html/data && \
    chmod -R 777 /var/www/html/data

EXPOSE 8080

CMD php -S 0.0.0.0:${PORT:-8080} -t /var/www/html
