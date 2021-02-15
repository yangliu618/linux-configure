#!/bin/bash
set -x 

function kill_process_by_name()
{
    local name sum process_id kill_id
    name="$1"
    sum=`ps -ef | grep $name | grep -v "grep" | wc -l`
    if [ $sum -gt 0 ];then
        process_id=`ps -ef | grep $name | grep -v "grep" | awk '{print $2}'`
        for kill_id in $process_id;do
            if [ $$ -eq $kill_id ];then
                echo $name'正在重新启动'
            else
                ps aux | awk '{print $2 }' | grep -q $kill_id 2> /dev/null
                if [ $? -eq 0  ];then
                    kill $kill_id
                fi
            fi
        done
    fi
}

kill_process_by_name "maven"
sleep 1

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
tail -f /tmp/start.$port.log

