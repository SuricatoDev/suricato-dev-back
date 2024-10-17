FROM php:8.2-fpm

# Instalar as dependências necessárias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Instalar o Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Definir o diretório de trabalho
WORKDIR /var/www

# Copiar os arquivos do aplicativo Laravel para o contêiner
COPY . .

# Definir as permissões das pastas storage e bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache
