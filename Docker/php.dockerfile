FROM php:8.1-fpm

# Mise à jour des paquets et installation des dépendances nécessaires
RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip curl \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

# Répertoire de travail
WORKDIR /var/www/symfony_docker

# Installation de Composer (outil PHP pour les dépendances)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installation de Symfony CLI (facultatif mais recommandé pour Symfony)
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Augmentation de la mémoire PHP (pour Symfony)
RUN echo 'memory_limit = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini
