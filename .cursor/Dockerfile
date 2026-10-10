FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive
ENV LANG=C.UTF-8

RUN apt-get update && apt-get install -y --no-install-recommends ca-certificates curl git gnupg software-properties-common unzip mysql-server mysql-client && add-apt-repository -y ppa:ondrej/php && apt-get update && apt-get install -y --no-install-recommends php8.4-cli php8.4-bcmath php8.4-curl php8.4-gd php8.4-gmp php8.4-intl php8.4-mbstring php8.4-mysql php8.4-readline php8.4-sqlite3 php8.4-xml php8.4-zip && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && apt-get install -y --no-install-recommends nodejs && curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && mkdir -p /var/run/mysqld && chown mysql:mysql /var/run/mysqld && rm -rf /var/lib/apt/lists/*  # pragma: allowlist secret

WORKDIR /workspace
