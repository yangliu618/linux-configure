#!/bin/bash

if [ $# -eq 2 ];then
    /bin/bash /home/rockywu/git-ssh/gitfr.sh $1 $2
    git push $1 $2:$2
elif [ $# -eq 1 ];then
    /bin/bash /home/rockywu/git-ssh/gitfr.sh $1
    git push origin $1:$1
else 
    /bin/bash /home/rockywu/git-ssh/gitfr.sh 
    git push origin master:master
fi

#if [ -z $1 ] && [ -z $2 ];then
#    git push origin master:master
#elif [ -n $1 ] && [ -z $2 ];then
#    git push origin $1:$1
#else 
#    git push $1 $2:$2
#fi

