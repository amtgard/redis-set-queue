#!/bin/sh

redis-server /etc/redis/redis.conf --daemonize yes

while true; do sleep 1; done
