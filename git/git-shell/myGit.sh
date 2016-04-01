#!/bin/bash
set -x
message()
{
    echo -e "\033[""$1""m""$2""\033[0m";
}

format()
{
    message "$1" "<<< $2 >>>"
}
#使用信息
usage_message()
{
    message 31 "$1"
}
#错误信息
error_message()
{
    message 31 "fatal: $1"
}
#成功信息
success_message()
{
    message 34 "success: $1"
}
#黄色信息
yellow_message()
{
    format 33 "$1"
}
#绿色信息
green_message()
{
    format 32 "$1"
}
#存在错误则中断并提升
hasError()
{
    if [ $? -eq 0 ];then
        if ! [ -z "$2" ];then
            success_message "$2"
        fi
    else
        error_message "$1"
        exit
    fi
}
#结束符
# ;
#基本命令集
# add    -> a
a_usage="
comman a :
Usage [FileName]
FileName <unnecessary>
    . run \"git add .\"
    FileName run \"git add filename\"
"
# commit -> c
c_usage="
comman c :
Usage [Option] [Message]
Option <unnecessary>
    -a run \"git add .\"
Message <necessary> commit message
"
# fetch  -> f
f_usage="
comman f :
Usage [Option] [Message]
Option <unnecessary>
    -a run \"git add .\"
Message <necessary> commit message
"
# rebase -> r
# push   -> p
# 操作指令
#Y c -m "" fr remote branch
#Y c -m "" fp remote branch
#Y r remote
#Y r remote branch
#Y p remote branch
# 操作 add
do_anything_a()
{
    fileName=''
    if [ $# -eq 1 ]; then
        fileName="$1"
    elif [ $# -eq 0 ]; then
        fileName="."
    else
        error_message "$a_usage"
        exit
    fi
    git add "$fileName"
}
# 操作 c
do_anything_c()
{
    msg=""
    if [ $# -eq 2 ] && [ "$1" == "-a" ]; then
        do_anything "a";
        msg="$2"
    elif [ $# -eq 1 ]; then
        msg="$1"
    else
        error_message "$c_usage"
        exit
    fi
    git commit -m "$msg"
}
# 操作 f
do_anything_f()
{
    create_branch=""
    exist_branch=""
    remote_name=""
    if ! [ -z "$3" ]; then
        create_branch="$3"
    fi
    if ! [ -z "$2" ]; then
        exist_branch="$2"
    fi
    if ! [ -z "$1" ];then
        remote_name="$1"
    fi
}
# 操作
do_anything()
{
    local command="$1"
    case $command in
        "a")
            do_anything_a "$2"
            ;;
        "c")
            do_anything_c "$2" "$3"
            ;;
        "f")
            do_anything_f "$2" "$3" "$4"
            echo "$1"
            ;;
        "r")
            echo "$1"
            ;;
        "p")
            echo "$1"
            ;;
        *)
            ;;
    esac
}
# 获取当前所属的分支名
get_current_branch()
{
    local branch
    branch=$(git symbolic-ref HEAD 2>/dev/null \
        || git rev-parse HEAD 2>/dev/null | cut -c1-10 \
    )
    branch=${branch#refs/heads/}
    if [ -n "$1" ];then
        eval "$1=$branch"
    else
        echo $branch
    fi
}
