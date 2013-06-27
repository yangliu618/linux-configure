#!/bin/bash

git fetch --all
if [ -z $1 ] && [ -z $2 ];then
    git rebase origin/master
elif [ -n $1 ] && [ -z $2 ];then
    git rebase origin/$1
else
    git rebase $1/$2
fi

