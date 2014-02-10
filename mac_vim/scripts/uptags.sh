#!/bin/bash
# get bash path
function get_base_path(){
    #bash get current file directory
    local DIR;
    DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
    echo "$DIR";
}

function get_src_dir() {
    if [ $# -eq 1 ];then
        echo "$1";
    else
        echo `get_base_path`;
    fi
}
PRJ_TYPE=php
case ${PRJ_TYPE} in
    php)
    SRC_DIR=`get_src_dir`;
    echo $SRC_DIR;
    find ${SRC_DIR}             \
        -name ".git" -prune     \
        -or -name ".sh" -prune   \
        -or -name "*.php"       \
        -or -name "*.js"        \
        -or -name "*.phtml"     \
        -or -name "*.css"       \
        -or -name "*.html"      \
        -or -name "*.sh"      \
        > cscope.files
    ;;
    *)
    ;;
esac
