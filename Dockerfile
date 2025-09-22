# Dockerfile
FROM dunglas/frankenphp AS app


# Herramientas del sistema útiles para composer/zip
RUN apt-get update \
 && apt-get install -y --no-install-recommends unzip p7zip-full git \
 && rm -rf /var/lib/apt/lists/*

RUN echo "Building with rdkafka..." && \
    apt-get update && \
    apt-get install -y --no-install-recommends \
      librdkafka-dev \
      build-essential \
      pkg-config \
      libssl-dev \
      nodejs \
      npm \
      zlib1g-dev && \
    pecl install rdkafka-6.0.5 && \
    docker-php-ext-enable rdkafka && \
    # limpiar toolchain que ya no se necesita + cachés
    apt-get purge -y --auto-remove build-essential php-dev php-pear pkg-config && \
    rm -rf /var/lib/apt/lists/*


# Extensiones PHP necesarias para Laravel/Composer/Octane
RUN install-php-extensions \
    pdo_mysql \
    zip \
    pcntl \
    bcmath \
    intl \
    opcache \
    redis \
    opentelemetry

# Dependencias del sistema útiles (si las necesitas)
# RUN apt-get update && apt-get install -y git unzip && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Exponer no es necesario para frankenphp en Compose, pero no estorba
EXPOSE 80

# La imagen de frankenphp ya inicia Caddy/FrankenPHP leyendo /etc/frankenphp/Caddyfile
