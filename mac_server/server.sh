#!/bin/bash
#set -x
function isOK() 
{
    if [ $? -eq 0 ];then
        echo $1" is success";
    else
        echo $1" is failed";
    fi
}
function run_command()
{
    command="launchctl "$1" -w  ~/Library/LaunchAgents/homebrew.mxcl."$2".plist"
    if [ "$2" == "nginx" ] && [ "$1" == "unload" ];then
        eval "sudo pkill -9 nginx"
    fi
    eval "$command"
    isOK "result >>> $1 $2"
    echo ""
}
Usage="Usage [Option] [Type] 
Option 
    start   trun on web-server 
    stop    turn off web-server 
    reload  reload web-server
Type
    nginx   start or stop nginx server
    mysql   start or stop mysql server
    php  start or stop phpfpm server
    -a | --all  start or stop all server"

as_echo='printf %s\n'

function doAnyThing()
{
    if [ -z $2 ];then
         $as_echo "$Usage";
         exit
    fi
    local servername=''
    case $2 in
        nginx | mysql  )
            echo "run    >>> $1 $2 server"
            run_command "$1" "$2";;
        php )
            echo "run    >>> $1 $2 server"
            run_command "$1" "php55";;
        -a | --all )
            list=("nginx" "php55" "mysql")
            for i in "${list[@]}"
            do
                echo "run    >>> $1 $i server"
                run_command "$1" "$i"
            done;;
        *   )
         $ac_echo "$Usage";exit;;
    esac
}
command=''
case $1 in
    start   )
        doAnyThing "load" "$2";;
    stop    )
        doAnyThing "unload" "$2";;
    reload  )
        doAnyThing "unload" "$2"
        doAnyThing "load" "$2";;
    * | --help )
        $as_echo "$Usage";exit;;
esac

