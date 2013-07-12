#!/bin/bash

if [ $# -eq 2 ];then
    git fetch $1 $2
    git rebase $1/$2
elif [ $# -eq 1 ];then
    git fetch origin $1
    git rebase origin/$1
else
    git fetch origin master
    git rebase origin/master
fi

#git fetch --all
#if [ -z $1 ] && [ -z $2 ];then
#    git rebase origin/master
#elif [ -n $1 ] && [ -z $2 ];then
#    git rebase origin/$1
#else
#    git rebase $1/$2
#fi

