#!/bin/bash
# get bash path
function get_base_path(){
    #bash get current file directory
    local DIR;
    DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
    echo "$DIR";
}

#######################

# $1 input content
# return {type name}
# check input type
function core_check_input_type(){
    local a="$1"
    printf "%d" "$a" &>/dev/null && echo "integer" && return
    printf "%d" "$(echo $a|sed 's/^[+-]\?0\+//')" &>/dev/null && echo "integer" && return
    printf "%f" "$a" &>/dev/null && echo "number" && return
    [ ${#a} -eq 1 ] && echo "char" && return
    echo "string"
}

# print and exit Usage
function core_print_usage()
{
    local usage
    if [ $# -eq 1 ];then
        usage=" * Usage: $1"
        echo "$usage"
        exit
    fi
}

# print message
function core_print_message()
{
    local message
    if [ $# -eq 1 ];then
        message=" * $1"
        echo "$message"
    else
        core_print_usage "core_print_message [message]..."
    fi
}

#
#execute character string

function core_execute_character_string()
{
    local usage="core_execute_character_string [command]... [result] "
    local core_exec_result=FALSE check_result
    if [ $# -gt 2 ] || [ $# -eq 0 ];then
        core_print_usage "$usage"
    else
        check_result=`core_check_input_type "$1"`
        if [ $check_result = "string" ];then
            eval "$1"
            if [ $? -eq 0 ];then
                core_exec_result=TRUE
                core_print_message "the result is successful  by running comand which is \"$1\" "
            else 
                core_print_message "the result is failed by running comand which is \"$1\" "
            fi
        else
            core_print_usage "$usage"
        fi
    fi
    if [ -n "$2" ];then
        eval "$2=$exec_result"
    fi
}


