linux-configure
===============

环境 ubuntu 12.04 x64

<strong>如果想使用系统寄存器</strong>

    $： sudo apt-get install vim-gnome

这样就可以在vim中使用"\* 或 "+ 来调用系统寄存器

<strong>下载仓库</strong>

    $：git clone https://github.com/rockywu/linux-configure.git

    $：cd linux-configure

<strong>开始VIM IED的配置之旅</strong>

<strong>如果不需要一些高级功能，则可以将easy-vimrc复制到~/.vim/.vimrc 就完成了简单配置</strong>

<strong>安装vim中文手册包(英文好的可以略过）</strong>

下载文件:vimcdoc-1.8.0.tar.gz

    $：tar -zxvf vimcdoc-1.8.0.tar.gz

    $：cd vimcdoc-1.8.0

    $：sh vimcdoc.sh -i

安装完成，进入vim 输入:help就能看到中文手册

<strong>1、复制.vimrc 文件到用户根目录 ~/.vim/</strong>

    $：cp vim/.vimrc ~/.vim/.vimrc

<strong>2、基本修改</strong>

编辑文件内容:

    $：sudo vim /etc/vim/vimrc 

原内容:

    if filereadable("/etc/vim/vimrc.local")
        source /etc/vim/vimrc.local
    endif

编辑后:

    if filereadable($HOME."/.vim/_vimrc")
        source $HOME/.vim/_vimrc
    endif

<strong>3、安装ctags插件</strong>

    $：sudo apt-get install ctags

<strong>4、复制插件包(将vimfile目录下所有的文件复制到~/.vim/下</strong>

    $：cp -R vim/\* ~/.vim/

<strong>5、安装cscope插件

    $：sudo apt-get install cscope

_打开vim 就能看到成果了。_

介绍几个快速开发的快捷键

> (1) ,tags 用于生成项目的索引标签

> (2) ,ww ,qq ,qw ，qf 这是快速保存，退出，保存后退出, 强制不保存退, 强制不保存退出

> (3) F5 可以开启一个文件目录所有框，用于快速检索项目中的文件,快速跳入

> (4) ctrl+] 用于快速跳转置方法标签, ctrl+o 返回跳转之前的位置

> (5) ,wm 快速开启项目框架， ,1 切换之文件目录列表 ,2 切换置方法和变量列表

> (6) ctrl+p 自动补全命,（请在插入模式下使用）

> (7) 如果出现浮动框 可以使用 ctrl+p 或ctrl+n 来进行上下移动，按enter进行选中

<strong>还有很多快捷键，可以从.vimrc文件中的快捷键一栏中进行查看，里面有详细的解释</strong>
