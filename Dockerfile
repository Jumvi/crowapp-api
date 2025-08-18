# Utiliser une image PHP officielle
FROM php:8.2-fpm

# Installer les dépendances système + extensions PHP
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www

# Copier les fichiers du projet
COPY . .

# Installer les dépendances Laravel pour l'nv dev
RUN composer install  --optimize-autoloader


# Installer les dépendances Laravel pour l'env prod en retirant TelescopeServiceProvider dans config/app.php et TelescopeServiceProvider.php.
#RUN composer install --no-dev --optimize-autoloader

# Donner les permissions à Laravel
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Lancer PHP-FPM (process manager pour PHP)
CMD ["php-fpm"]

EXPOSE 9000
