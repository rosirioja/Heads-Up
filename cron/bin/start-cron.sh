#!/bin/sh
# start-cron.sh

rsyslogd
cron
touch /var/log/cron.log
