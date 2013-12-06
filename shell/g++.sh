#!/bin/bash
echo ">>>>>>>Run : g++ execute start"
#$1 执行文件名
#$2 编辑生成的执行文件
#$3 编辑生成的执行路径
if [ $# -eq 3 ];then
    g++ "$1" -o "$3$2"
    echo ">>>>>>>Run : g++ $1 -o $3$2"
if [ $# -eq 2 ];then
    g++ "$1" -o "$2"
    echo ">>>>>>>Run : g++ $1 -o $2
elif [ $# -eq 1 ];then
    name=$1
    exec_name=${name%.*}
    g++ "$1" -o "$exec_name"
    echo ">>>>>>>Run : g++ $1 -o $exec_name"
fi
echo ">>>>>>>g++ execute end"
