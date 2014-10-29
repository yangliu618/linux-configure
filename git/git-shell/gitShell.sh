#!/bin/bash
#set -x

myecho() {
    echo "\033[""$1""m""$2""\033[0m";
}

mkcolor()
{
    myecho "$1" "<<< $2 >>>"
}

errorcolor() {
    myecho 31 "fatal: $1";exit;
}

usagecolor() {
    myecho 31 "$1";exit;
}

yellowcolor() {
    mkcolor 33 "$1"
}

greencolor() {
    mkcolor 32 "$1"
}

whitecolor() {
    mkcolor 37 "$1"
}


Usage="
Usage [Option] [remote] [branch]
Option   <Necessary>
    ps   git fetch & rebase & push 
    fr   git fetch & rebase
Remote   Remote repository of nickname
Branch   Development branch name
"

branch=$(git symbolic-ref HEAD 2>/dev/null \
    || git rev-parse HEAD 2>/dev/null | cut -c1-10 \
)

branch=${branch#refs/heads/}

if [ -z "$branch" ]; then
    errorcolor "Not a git repository (or any of the parent directories): .git"
    exit
fi



#获得当前目录下得git地址 .git rev-parse --git-dir
#`git remote -v | awk '{print $1}' | sort | uniq`
#`git ls-remote`
#`git show-ref * branchname`

list=`git show-ref * $branch | grep 'refs/remotes/' | awk '{print $2}'`
list=${list#refs/remotes/}

remote=${list%%/$branch}


#执行 代码更新
if ! [ "$1" == "ps" ] && ! [ "$1" == "fr" ]; then
    usagecolor "$Usage"
fi
type="$1"
if [ $# -gt 3 ] || [ $# -eq 2 ]; then 
    usagecolor "$Usage"
elif [ $# -eq 3 ]; then
    branch="$3"
    remote="$2"
fi

#启动
fetch="git fetch $remote"
rebase="git rebase $remote/$branch"
push="git push $remote $branch"

greencolor "Run start"
greencolor "The current remote to $remote"
greencolor "The current branch to $branch"

yellowcolor "Run $fetch"
eval "$fetch"

yellowcolor "Run $rebase"
eval "$rebase"

if [ "$type" == "ps" ]; then
    yellowcolor "Run $push"
    eval "$push"
fi

greencolor "Run end"
