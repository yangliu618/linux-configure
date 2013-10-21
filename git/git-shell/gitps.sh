#!/bin/bash

echo ">>>>>>>Run : git fetch --all"
git fetch --all
if [ $# -eq 2 ];then
    echo ">>>>>>>Run : git rebase $1/$2"
    git rebase $1/$2
    echo ">>>>>>>Run : git push $1 $2:$2"
    git push $1 $2:$2
elif [ $# -eq 1 ];then
    echo ">>>>>>>Run : git rebase origin/$1"
    git rebase origin/$1
    echo ">>>>>>>Run : git push origin $1:$1"
    git push origin $1:$1
else 
    echo ">>>>>>>Run : git rebase origin/master"
    git rebase origin/master
    echo ">>>>>>>Run : git push origin master:master"
    git push origin master:master
fi
echo ">>>>>>>Run : command execution"
