#!/bin/sh -e
# upstart-job
#
# rsync_real_time

COMMAND="$1"

case $COMMAND in
stop)
    /bin/bash /home/rockywu/rsync+inotify/kill_rsync_real_time.sh
    exit 0;
    ;;
start)
    echo "current $$"
    echo '同步进程'$1 $2'已经重新启动'
    nohup /bin/bash /home/rockywu/rsync+inotify/rsync_real_time.sh $2 > /home/www/log/rsync_real_time.log 2>&1 &
    exit 0;
    ;;
*)
    echo "参数不正确(stop|start [type])"
    exit 1
esac
