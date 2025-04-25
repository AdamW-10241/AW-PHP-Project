FROM mcr.microsoft.com/devcontainers/php:1-8.2-bookworm

# Install MariaDB client
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y mariadb-client \ 
    && apt-get clean -y && rm -rf /var/lib/apt/lists/*

# Install php-mysql driver and GD extension
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql \
    && apt-get clean -y && rm -rf /var/lib/apt/lists/* \
    && echo "error_log = /var/log/php_errors.log" >> /usr/local/etc/php/conf.d/error-logging.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/error-logging.ini \
    && echo "display_errors = Off" >> /usr/local/etc/php/conf.d/error-logging.ini \
    && touch /var/log/php_errors.log \
    && chmod 666 /var/log/php_errors.log

# Configure Apache
RUN rm -rf /var/www/html && mkdir -p /var/www/html
WORKDIR /var/www/html

# Start Apache in the foreground
CMD ["apache2-foreground"]

# [Optional] Uncomment this section to install additional OS packages.
# RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
#     && apt-get -y install --no-install-recommends <your-package-list-here>

# [Optional] Uncomment this line to install global node packages.
# RUN su vscode -c "source /usr/local/share/nvm/nvm.sh && npm install -g <your-package-here>" 2>&1

