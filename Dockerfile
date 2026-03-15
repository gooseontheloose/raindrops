# Use the official PHP image with Apache
FROM php:8.2-apache

# Enable Apache rewrite module and configure MPM
RUN a2dismod mpm_event && \
    a2dismod mpm_worker && \
    a2enmod mpm_prefork && \
    a2enmod rewrite

# Copy all website files into the container's web directory
COPY . /var/www/html/

# Create the data folder if it doesn't exist and set permissions
# Railway volumes map over this, but this ensures it has proper permissions built-in
RUN mkdir -p /var/www/html/data && \
    chown -R www-data:www-data /var/www/html/data && \
    chmod -R 777 /var/www/html/data

# Update Apache configuration to use the PORT environment variable (or 8080 as fallback)
RUN sed -i 's/80/${PORT:-8080}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Expose port (metadata)
EXPOSE 8080
