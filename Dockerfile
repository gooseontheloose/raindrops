FROM php:8.2-apache

# Fix MPM conflict: delete ALL MPM symlinks and re-enable only prefork
RUN rm -f /etc/apache2/mods-enabled/mpm_*.conf \
    /etc/apache2/mods-enabled/mpm_*.load && \
    ln -s /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf && \
    ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load && \
    a2enmod rewrite

# Copy website files
COPY . /var/www/html/

# Create the persistent data directory and set permissions
RUN mkdir -p /var/www/html/data && \
    chown -R www-data:www-data /var/www/html/data && \
    chmod -R 775 /var/www/html/data

EXPOSE 8080

# Inline the port patching into CMD so we avoid any CRLF shell script issues
# Double quotes ensure $PORT is properly expanded at runtime
CMD sed -i "s/Listen 80/Listen ${PORT:-8080}/" /etc/apache2/ports.conf && \
    sed -i "s/:80>/:${PORT:-8080}>/" /etc/apache2/sites-available/000-default.conf && \
    apache2-foreground
