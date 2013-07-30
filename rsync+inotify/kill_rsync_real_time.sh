#!/bin/bash

declare -a name
name[0]="rsync_real_time_module.sh"
name[1]="rsync_real_time_path.sh"
name[2]="inotifywait"
for k in ${name[@]};do
    num=`ps -ef | grep $k | grep -v "grep" | wc -l`
    if [ $num -gt 0 ];then
        process_id=`ps -ef | grep $k | grep -v "grep" | awk '{print $2}'`
        for kill_id in $process_id;do
            sudo kill $kill_id
        done
    fi
done
