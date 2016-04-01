#!/bin/bash
#set -x

myecho() {
    echo -e "\033[""$1""m""$2""\033[0m";
}

mkcolor()
{
    myecho "$1" "<<< $2 >>>"
}

errorcolor() {
    myecho 31 "fatal: $1"
}

usagecolor() {
    myecho 31 "$1"
}

yellowcolor() {
    mkcolor 33 "$1"
}

greencolor() {
    mkcolor 32 "$1"
}

successcolor() {
    myecho 34 "success: $1"
}

hasError() 
{
    if [ $? -eq 0 ];then
        if ! [ -z "$2" ];then
            successcolor "$2"
        fi
    else
        errorcolor "$1"
        exit
    fi
}

Usage="
Usage [Option] [Remote] [Branch]
Option   <Necessary>
    ps   git fetch & rebase & push 
    fr   git fetch & rebase
Remote   Remote repository of nickname
Branch   Development branch name
"

branch=$(git symbolic-ref HEAD 2>/dev/null \
    || git rev-parse HEAD 2>/dev/null | cut -c1-10 \
)

if [ -z "$branch" ]; then
    errorcolor "Not a git repository (or any of the parent directories): .git"
    exit
fi

#执行 代码更新
if ! [ "$1" == "ps" ] && ! [ "$1" == "fr" ]; then
    usagecolor "$Usage"
    exit
fi

#行为type
type="$1" 

if [ $# -gt 3 ]; then
    usagecolor "$Usage"
    exit
elif [ $# -eq 3 ]; then
    #参数个数为3个
    git remote show "$2" 1> /dev/null 2>&1
    hasError "Remote $2 does not exist"
    git show-ref "$2" "$3" 1> /dev/null 2>&1
    hasError "Branch $3 does not exist"
    branch="$3"
    remote="$2"
elif [ $# -eq 2 ]; then
    #参数个数为2个
    git remote show "$2" 1> /dev/null 2>&1
    hasError "Remote $2 does not exist"
    remote="$2"
    branch=${branch#refs/heads/}
elif [ $# -eq 1 ]; then
    #参数个数为1个

    #git rev-parse --git-dir  ###获得当前目录下得.git地址
    #`git remote -v | awk '{print $1}' | sort | uniq`
    #`git ls-remote`
    #`git show-ref * branchname`
    branch=${branch#refs/heads/}

    tmp=`git show-ref * $branch | grep 'refs/remotes/' | awk '{print $2}'`
    eval "list=($tmp)"
    if [ ${#list[@]} -gt 1 ];then
        choose=""
        for a in $tmp ; do
            a=${a#refs/remotes/}
            a=${a%%/$branch}
            choose="$choose $a"
        done
        errorcolor "The same name exists multiple remote \033[33m$branch\033[31m, Please choose remote \033[32m[$choose ]\033[0m"
        usagecolor "$Usage"
        exit
    fi
    remote=${tmp#refs/remotes/}
    remote=${remote%%/$branch}
fi

#启动
fetch="git fetch $remote"
rebase="git rebase $remote/$branch"
push="git push $remote $branch"

greencolor "Run start"
greencolor "The current \033[31mRemote \033[32mto \033[33m$remote\033[32m"
greencolor "The current \033[31mBranch \033[32mto \033[33m$branch\033[32m"

yellowcolor "Run $fetch"
eval "$fetch"
hasError "$fetch" "$fetch"

yellowcolor "Run $rebase"
eval "$rebase"
hasError "$rebase" "$rebase"

if [ "$type" == "ps" ]; then
    yellowcolor "Run $push"
    eval "$push"
    hasError "$push" "$push"
fi
greencolor "Run end"
