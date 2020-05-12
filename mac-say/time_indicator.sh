#!/bin/bash
#set -x
H=`date +%H`
macHour=`date +%l"点整"`
A=`expr $H - 12`
B=0
M="上午"
if [ $A -gt $B ];then
    M="下午"
fi

say "现在是北京时间""$M""$macHour"
#/usr/local/bin/node /Users/rocky/www/linux-configure/mac-say/weather.js | xargs say

