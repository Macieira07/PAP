FROM php:8.2-apache

# Instalar dependências e extensões PHP de uma forma mais simples
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    default-mysql-client \
    && docker-php-ext-install -j$(nproc) mysqli pdo_mysql \
    && docker-php-ext-enable mysqli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configurar o PHP para exibir erros em desenvolvimento
RUN echo "display_errors = On" > /usr/local/etc/php/conf.d/error-reporting.ini \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/error-reporting.ini

# Copia os arquivos do projeto para o contêiner
COPY . /var/www/html/

# Define permissões corretas
RUN chown -R www-data:www-data /var/www/html
