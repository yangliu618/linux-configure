#!/bin/bash
function ip() {
  echo `date +%Y`"年第"`date +%W`"周,""本机IP: "`ifconfig en0 | grep "inet"|awk -F " " '{print $2}'`
}
ip
export ip=ip
source $HOME/www/linux-configure/openShell.sh
source $HOME/www/linux-configure/shell/auto_login_ssh.sh
source $HOME/www/linux-configure/shell/git-completion.bash
alias ll='ls -al'
alias l='ls'
PS1="$PS1\$($( cat /Users/rocky/.git/git-ps1/git-ps1.sh))"
alias uptags='/bin/bash $HOME/www/linux-configure/mac_vim/scripts/uptags.sh'
export PATH="/usr/local/sbin:/usr/local/bin:$PATH"
alias python="/usr/bin/python2.7"
alias gs="/bin/bash $HOME/www/linux-configure/git/git-shell/gitShell.sh"
sublime_open_file () {
    sof_app="Sublime Text"
    open_soft "$sof_app" "$1"
}

idea_open_file() {
    sof_app="IntelliJ IDEA"
    open_soft "$sof_app" "$1"
}

webstorm_open_file() {
    sof_app="webstorm"
    open_soft "$sof_app" "$1"
}

open_soft() {
    soft_app="$1"
    file="$2"
    `open -a "$soft_app" "$file"`
}
alias sublime=sublime_open_file

alias sed=gsed

alias weather="/usr/local/bin/node /Users/rocky/www/linux-configure/mac-say/weather.js | xargs say"

