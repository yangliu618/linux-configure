linux-configure
===============
VIM 配置文件

ubuntu 12.04 x64

安装vim中文手册包

下载文件:vimcdoc-1.8.0.tar.gz

$:tar -zxvf vimcdoc-1.8.0.tar.gz

$:cd vimcdoc-1.8.0

$:sh vimcdoc.sh -i

安装完成，进入vim 输入:help就能看到中文手册

1:基本修改
编辑文件内容:

$:sudo vim /etc/vim/vimrc 

" Source a global configuration file if available

原内容:

if filereadable("/etc/vim/vimrc.local")

  source /etc/vim/vimrc.local

endif

编辑后:

if filereadable($HOME."/.vim/.vimrc")

  source $HOME/.vim/.vimrc

endif

2: 安装ctags插件

$:sudo apt-get install ctags



