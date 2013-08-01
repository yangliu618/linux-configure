#!/bin/bash

git fetch --all
if [ $# -eq 2 ];then
    git rebase $1/$2
    git push $1 $2:$2
elif [ $# -eq 1 ];then
    git rebase origin/$1
    git push origin $1:$1
else 
    git rebase origin/master
    git push origin master:master
fi

