#!/bin/bash

if [ -z $1 ] && [ -z $2 ];then
    git push origin master:master
elif [ -n $1 ] && [ -z $2 ];then
    git push origin $1:$1
else 
    git push $1 $2:$2
fi

