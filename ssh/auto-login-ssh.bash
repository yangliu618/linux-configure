#!/bin/bash
auto_login_ssh () {
    expect -c "
    set timeout 1;
    spawn -noecho ssh -o StrictHostKeyChecking=no $2 ${@:3};
    expect {
        \"*yes/no*\" {send \"yes\r\";exp_continue}
        \"*password*\" {send \"$1\r\"}
    }
    interact;"
}
#auto_login_ssh password username@xxx.xxx.xxx.xxx
