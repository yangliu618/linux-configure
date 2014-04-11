#!/bin/bash
set -x 

gitdir=/home/www/kfstouch/

if [ ! -d $gitdir ];then
    echo "$gitdir is no exists"
    exit;
fi
port=8080;
cd $gitdir;
nohup /usr/bin/mvn clean jetty:run -Dajf.config.path=$gitdir -Djetty.port=$port > /tmp/start.$port.log 2>&1 &
echo "starting ..."
echo $?

