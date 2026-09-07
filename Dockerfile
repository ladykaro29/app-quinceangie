FROM php:8.2-apache

# Instalar extensiones requeridas por la aplicación (MySQL y SQLite)
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite

# Habilitar módulos necesarios de Apache
RUN a2enmod rewrite headers

# Permitir que .htaccess funcione dentro de /var/www/html
RUN sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

# Copiar el código del proyecto
COPY . /var/www/html/

# Ajustar permisos para el usuario del servidor web y directorio de base de datos
RUN mkdir -p /var/www/html/database && chown -R www-data:www-data /var/www/html && chmod -R 775 /var/www/html/database

# Exponer el puerto estándar HTTP
EXPOSE 80

CMD ["apache2-foreground"]
