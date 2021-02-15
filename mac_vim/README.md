my mac Vim configure

1、删除本地.vimrc文件。

2、安装spf13-vim github  https://github.com/spf13/spf13-vim

    git clone https://github.com/spf13/spf13-vim.git

install
    
    cd spf13-vim
    curl https://j.mp/spf13-vim3 -L > spf13-vim.sh && sh spf13-vim.sh


3、安装ctags 

for ubuntu 

    sudo apt-get install 
for mac

    brew install ctags

4、安装cscope

for ubuntu 

    sudo apt-get install 
for mac

    brew install cscope

5、安装本地配置
    
    git clone  https://github.com/rockywu/linux-configure.git
    cd linux-configure
    ln -s ./mac_vim/* ~/.vim/
    cp ./mac_vim/.vimrc.local ~/.vimrc.local

6、配置本地tags生成器
    
for mac

    echo "alias uptags='/bin/bash ~/.vim/scripts/uptags.sh'" >> ~/.bash_profile
for ubuntu

    echo "alias uptags='/bin/bash ~/.vim/scripts/uptags.sh'" >> ~/.bashrc


### 参考文章

[https://www.cnblogs.com/sybboy/p/8989342.html](https://www.cnblogs.com/sybboy/p/8989342.html)
[https://segmentfault.com/a/1190000006894422](https://segmentfault.com/a/1190000006894422)


