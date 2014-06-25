#!/bin/bash

declare -a sync
if ! [ $# -eq 1 ];then
    echo "没有可用的配置信息";
    exit;
elif [ $1 -eq 0 ];then
    sync[0]='' 
elif [ $1 -eq 1 ];then
    sync[2]='/home/www/aifang/kfs/,evans@192.168.1.167:/home/www/releases/php/rockywu/kfs/kfs' 
    sync[3]='/home/www/aifang/twpages/,evans@192.168.1.167:/home/www/releases/php/rockywu/kfs/twpages' 
    sync[4]='/home/www/aifang/system/,evans@192.168.1.167:/home/www/releases/php/rockywu/kfs/system' 
else
    echo "没有可用的配置信息";
    exit;
fi

