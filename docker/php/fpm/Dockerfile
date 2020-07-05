#ARG PHP_VERSION=7.4
FROM php:7.4-fpm

ARG APCU_VERSION=5.1.18
ARG APCU_BC_VERSION=1.0.5

ENV DEBIAN_FRONTEND noninteractive
ENV DOCKERIZE_VERSION v0.6.1

# Some corporate partners may use a service to monitor encrypted network traffic.  When
# this is the case, add the intermediary's certificate to the container's trust authority
# for the duration of the build so that PHP Community Extensions may be uploaded
COPY ./firewall.crt /tmp/firewall.crt

# Add tools to install PHP extensions
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/

# Copy intermediary's certificates to /usr/share/ca-certificates, then add to
# the ca-certificates.conf file and update-ca-certificates
RUN	apt-get -o 'Acquire::CompressionTypes::Order::="gz"' update \
	&& cp /tmp/firewall.crt /usr/share/ca-certificates/firewall.crt \
	&& cp /etc/ca-certificates.conf /etc/ca-certifcates.conf.0 \
	&& echo firewall.crt >>/etc/ca-certificates.conf \
	&& /usr/sbin/update-ca-certificates \
	&& rm /tmp/firewall.crt

# Install, enable and configure Postgres extension and PDO_pgsql
RUN chmod uga+x /usr/local/bin/install-php-extensions \
	&& sync \
    && install-php-extensions pdo_pgsql pgsql

# add extensions
RUN	apt-get -o 'Acquire::CompressionTypes::Order::="gz"' update \
	&& apt-get install -y --no-install-recommends \
	  libbz2-dev \
	  libfreetype6-dev \
	  libpng-dev \
	  libjpeg-dev \
	  libicu-dev \
	  libzip-dev \
	  unzip \
	  curl \
	  wget \
	  git \
	  gnupg2

# Add the Postgres client
RUN echo "deb http://apt.postgresql.org/pub/repos/apt buster-pgdg main" >/etc/apt/sources.list.d/pgdg.list \
	&& wget --quiet --no-check-certificate -O /tmp/postges_key.asc https://www.postgresql.org/media/keys/ACCC4CF8.asc \
	&& apt-key add /tmp/postges_key.asc \
	&& rm /tmp/postges_key.asc \
	&& apt-get -o 'Acquire::CompressionTypes::Order::="gz"' update \
	&& apt-get install -y --no-install-recommends postgresql-client \
	&& apt-get -o 'Acquire::CompressionTypes::Order::="gz"' update

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
	  bz2 \
	  gd \
	  zip \
	  pdo_mysql \
	  mysqli \
	  pcntl \
	  bcmath \
	  opcache \
	  intl \
	&& pecl install \
	  apcu-${APCU_VERSION} \
	  apcu_bc-${APCU_BC_VERSION} \
	&& pecl clear-cache \
	&& docker-php-ext-enable --ini-name 0-apc.ini apcu apc \
	&& apt-get purge --auto-remove -y \
	  libbz2-dev \
	  libfreetype6-dev \
	  libpng-dev \
	  libicu-dev \
	  libzip-dev \
	&& apt-get install -y --no-install-recommends \
	  libbz2-1.0 \
	  libpng16-16 \
	  libfreetype6 \
	  libicu63 \
	  libzip4 \
	&& rm -r /var/lib/apt/lists/* \
	&& wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
	  && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
	  && rm dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz

COPY \
    memory_limit.ini \
    upload_size.ini \
    short_tag.ini \
    opcache.ini \
    log.ini \
    no-expose.ini \
        /usr/local/etc/php/conf.d/

# Install symfony-cli
RUN wget --no-check-certificate https://get.symfony.com/cli/installer -O - | bash \
    && mv ~/.symfony/bin/symfony /usr/local/bin/symfony

# Install and configure composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PATH=/composer/vendor/bin:$PATH \
    COMPOSER_HOME=/composer \
    COMPOSER_MEMORY_LIMIT=-1

RUN composer global require \
        hirak/prestissimo \
        maglnet/composer-require-checker \
        insolita/unused-scanner \
            --prefer-dist --no-progress --no-suggest --classmap-authoritative \
        && composer clear-cache \
	&& chmod -R a+w ${COMPOSER_HOME}

# Remove intermediary's certificate from the container's trust authority
RUN rm /usr/share/ca-certificates/firewall.crt \
	&& rm /etc/ca-certificates.conf \
	&& mv /etc/ca-certifcates.conf.0 /etc/ca-certificates.conf \
	&& /usr/sbin/update-ca-certificates


