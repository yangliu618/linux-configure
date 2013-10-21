#!/bin/bash

echo ">>>>>>>Run : git fetch --all"
git fetch --all
if [ $# -eq 2 ];then
    echo ">>>>>>>Run : git rebase $1/$2"
    git rebase $1/$2
elif [ $# -eq 1 ];then
    echo ">>>>>>>Run : git rebase origin/$1"
    git rebase origin/$1
else
    echo ">>>>>>>Run : git rebase origin/master"
    git rebase origin/master
fi
