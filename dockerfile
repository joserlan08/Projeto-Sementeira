FROM php:8.4-apache

# Instala dependências do sistema e extensões PHP
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip

# Habilita mod_rewrite do Apache
RUN a2enmod rewrite

# Libera acesso ao diretório no Apache
RUN echo "<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>" >> /etc/apache2/apache2.conf

# Cria usuário devuser e define permissões
RUN useradd -u 1000 -d /home/devuser -m devuser && \
    usermod -aG www-data devuser && \
    mkdir -p /var/www/html && \
    chown -R www-data:www-data /var/www && \
    chmod -R 775 /var/www

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia arquivos do Composer
COPY composer.json composer.lock ./

# Instala dependências
RUN rm -rf vendor/ && \
    composer install --no-interaction --no-dev --optimize-autoloader

# Copia todo o projeto
COPY . .

# Ajusta permissões e recompila autoloader
RUN rm -rf .git vendor && \
    composer install --no-interaction --no-dev --optimize-autoloader && \
    chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www

# ⏩ Agora sim, troca para usuário não-root no final
USER devuser
