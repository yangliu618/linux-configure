git  config

下载git-ps1插件

wget git-ps1

git使用提示插件

在~/.bashrc 中添加一下内容

PS1="$PS1\$($( cat /home/rockywu/software/git-ps1/git-ps1.sh ))"



