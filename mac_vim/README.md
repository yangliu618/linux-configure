my mac Vim configure

1、删除本地.vimrc文件。

2、安装spf13-vim github  https://github.com/spf13/spf13-vim

    git clone https://github.com/spf13/spf13-vim.git

install

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
    
    ln -s ./mac_vim/* ~/.vim/
    ln -s ./mac_vim/.vimrc.local ~/.vimrc.local
