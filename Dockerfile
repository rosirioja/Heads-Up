FROM debian:jessie

RUN apt-get update && apt-get -y install php5 libapache2-mod-php5 php5-mcrypt php5-mysql php5-curl curl git rsyslog vim nano --fix-missing

COPY .env artisan composer.json phpunit.xml server.php phpspec.yml /var/www/headsup/

RUN groupadd -g 1000 www && \
    useradd -g www -u 1000 -r -M www && \
    cd /var/www/headsup && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

COPY app/ /var/www/headsup/app/
COPY bootstrap/ /var/www/headsup/bootstrap/
COPY config/ /var/www/headsup/config/
COPY database/ /var/www/headsup/database/
COPY public/ /var/www/headsup/public/
COPY resources/ /var/www/headsup/resources/
COPY storage/ /var/www/headsup/storage/
COPY tests/ /var/www/headsup/tests/

RUN chmod 775 -R /var/www/headsup/bootstrap
RUN chmod 775 -R /var/www/headsup/storage

RUN mkdir /var/www/headsup/bootstrap/cache && touch /var/www/headsup/bootstrap/cache/services.json

RUN cd /var/www/headsup && composer -n install 

COPY cron/crontab /app/crontab
RUN cron /app/crontab
COPY cron/bin/start-cron.sh /usr/bin/start-cron.sh
RUN chmod +x /usr/bin/start-cron.sh
RUN touch /var/log/cron.log

WORKDIR /var/www/headsup

VOLUME ["/var/www/headsup"]

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
