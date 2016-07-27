FROM debian:jessie

RUN apt-get update && apt-get -y install php5 libapache2-mod-php5 php5-mcrypt php5-pgsql php5-mysql php5-curl curl git rsyslog vim nano --fix-missing

COPY .env artisan composer.json phpunit.xml server.php phpspec.yml /var/www/headsup/

RUN groupadd -g 1000 www && \
    useradd -g www -u 1000 -r -M www && \
    cd /var/www/headsup && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

COPY . /var/www/headsup

RUN chmod 777 -R /var/www/headsup/bootstrap 
RUN chmod 777 -R /var/www/headsup/storage 

WORKDIR /var/www/headsup

RUN composer -n install --no-plugins --no-scripts

COPY cron/bin/start-cron.sh /usr/bin/start-cron.sh
RUN chmod +x /usr/bin/start-cron.sh
RUN touch /var/log/cron.log

VOLUME ["/var/www/headsup"]

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
