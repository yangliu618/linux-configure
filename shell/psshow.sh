#!/bin/bash 
if ! [ $# -eq 1 ];then
    echo "not grep parameter";
    exit;
fi;
while true;
do
    clear;
    ps -ef | grep -v 'grep' | grep $1;
    sleep 2;
done;
