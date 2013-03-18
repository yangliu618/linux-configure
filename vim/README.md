linux-configure
===============

环境 ubuntu 12.04 x64

下载仓库

$: git clone https://github.com/rockywu/linux-configure.git

$: cd linux-configure

开始VIM IED的配置之旅

安装vim中文手册包(英文好的可以略过）

下载文件:vimcdoc-1.8.0.tar.gz

$:wget https://github.com/rockywu/linux-configure/blob/master/vim/vimcdoc-1.8.0.tar.gz

$:tar -zxvf vimcdoc-1.8.0.tar.gz

$:cd vimcdoc-1.8.0

$:sh vimcdoc.sh -i

安装完成，进入vim 输入:help就能看到中文手册

1、基本修改

编辑文件内容:

$: sudo vim /etc/vim/vimrc 

原内容:

if filereadable("/etc/vim/vimrc.local")

  source /etc/vim/vimrc.local

endif

编辑后:

if filereadable($HOME."/.vim/.vimrc")

  source $HOME/.vim/.vimrc

endif

2、安装ctags插件

$:sudo apt-get install ctags

3、复制插件包(将vimfile目录下所有的文件复制到~/.vim/下
$: cp -R vim/vimfile/* ~/.vim/

打开vim 就能看到成果了。

介绍几个快速开发的快捷键

(1) ,tags 用于生成项目的索引标签

(2) ,ww ,qq ,qw ，qf 这是快速保存，退出，保存后退出, 强制不保存退, 强制不保存退出

(3) F5 可以开启一个文件目录所有框，用于快速检索项目中的文件,快速跳入

(4) ctrl+] 用于快速跳转置方法标签, ctrl+o 返回跳转之前的位置

(5) ,wm 快速开启项目框架， ,1 切换之文件目录列表 ,2 切换置方法和变量列表

(6) ctrl+p 自动补全命,（请在插入模式下使用）

(7) 如果出现浮动框 可以使用 ctrl+p 或ctrl+n 来进行上下移动，按enter进行选中

<strong>还有很多快捷键，可以从.vimrc文件中的快捷键一栏中进行查看，里面有详细的解释</strong>
