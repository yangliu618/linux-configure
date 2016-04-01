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
# get bash path
function get_base_path(){
    #bash get current file directory
    local DIR;
    DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
    echo "$DIR";
}
basePath=`get_base_path`
echo $NODE_PATH | grep $basePath > /dev/null 2>&1
if [ ! $? -eq "0" ];then
    exportCommand="export NODE_PATH=\"$basePath;$NODE_PATH\""
    if [ -a ~/.bashrc ];then
        greencolor "~/.bashrc is exist"
        echo $exportCommand >> ~/.bashrc 2>&1
        source ~/.bashrc
    elif [ -a ~/.bash_profile ];then
        greencolor "~/.bash_profile is exist"
        echo $exportCommand >> ~/.bash_profile 2>&1
        source ~/.bash_profile
    else 
        yellowcolor "~/.bashrc do create"
        echo $exportCommand >> ~/.bashrc 2>&1
        source ~/.bashrc
    fi
else
    yellowcolor "NODE_PATH do not reset"
fi
