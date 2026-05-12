FROM php:8.2-apache
RUN docker-php-ext-install mysqli
RUN a2enmod ssl && a2enmod rewrite
RUN echo "<VirtualHost *:443>\n\
    DocumentRoot /var/www/html\n\
    SSLEngine on\n\
    SSLCertificateFile /etc/apache2/ssl/server.crt\n\
    SSLCertificateKeyFile /etc/apache2/ssl/server.key\n\
</VirtualHost>" > /etc/apache2/sites-available/default-ssl.conf
RUN a2ensite default-ssl