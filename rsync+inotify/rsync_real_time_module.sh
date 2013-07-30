#!/bin/bash

###########################
# 添加监控shell是否已经开启，开启则kill掉之前的重新启动
##########################
sum=`ps -ef | grep $0 | grep -v "grep" | wc -l`
if [ $sum -gt 2 ];then
    process_id=`ps -ef | grep $0 | grep -v "grep" | awk '{print $2}'`
    for kill_id in $process_id;do
        if [ $$ -eq $kill_id ];then
            echo '同步进程'$0'正在重新启动'
        else
            echo "kill $kill_id"
        fi
    done
fi
sleep 1

###########################
# 在这里配置本地文件夹,目标host,目标的rsync_module。rsync_module在同步机器的/etc/rsyncd.conf文件中配置
# 逗号前后不要有空格
sync[0]='/home/aifang/site/,root@10.10.3.208,aifang-site' # localdir,host,rsync_module
# sync[1]='/path/to/local/dir,host,rsync_module'
###########################,qq

for item in ${sync[@]}; do

dir=`echo $item | awk -F"," '{print $1}'`
host=`echo $item | awk -F"," '{print $2}'`
module=`echo $item | awk -F"," '{print $3}'`

inotifywait -mrq --timefmt '%d/%m/%y %H:%M' --format  '%T %w%f %e' \
 --event CLOSE_WRITE,create,move,delete  $dir | while read  date time file event
    do
        echo $event'-'$file
        case $event in
            MODIFY|CREATE|MOVE|MODIFY,ISDIR|CREATE,ISDIR|MODIFY,ISDIR)
                if [ "${file: -4}" != '4913' ]  && [ "${file: -1}" != '~' ]; then
                    cmd="rsync -avz --exclude='*' --include=$file $dir $host::$module"
                    # echo $cmd
                    $cmd
                fi
                ;;

            MOVED_FROM|MOVED_FROM,ISDIR|DELETE|DELETE,ISDIR)
                if [ "${file: -4}" != '4913' ]  && [ "${file: -1}" != '~' ]; then
                    cmd="rsync -avz --delete-excluded --exclude="$file" $dir $host::$module"
                    # echo $cmd
                    $cmd
                fi
                ;;
        esac
    done &
done
