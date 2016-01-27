#!/bin/bash
#set -x
auto_login_ssh () {
    #auto_login_ssh password username@xxx.xxx.xxx.xxx
    local time=1;
    expect -c "
        set timeout $time;
        spawn -noecho ssh -o StrictHostKeyChecking=no $2 ${@:3};
        expect {
        \"*yes/no*\" {send \"yes\r\";exp_continue}
        \"*password*\" {send \"$1\r\"}
    }
    interact;"
}


