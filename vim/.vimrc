" All system-wide defaults are set in $VIMRUNTIME/debian.vim (usually just
" /usr/share/vim/vimcurrent/debian.vim) and sourced by the call to :runtime
" you can find below.  If you wish to change any of those settings, you should
" do it in this file (/etc/vim/vimrc), since debian.vim will be overwritten
" everytime an upgrade of the vim packages is performed.  It is recommended to
" make changes after sourcing debian.vim since it alters the value of the
" 'compatible' option.

" This line should not be removed as it ensures that various options are
" properly set to work with the Vim-related packages available in Debian.
runtime! debian.vim

" Uncomment the next line to make Vim more Vi-compatible
" NOTE: debian.vim sets 'nocompatible'.  Setting 'compatible' changes numerous
" options, so any other options should be set AFTER setting 'compatible'.
"set compatible

" Vim5 and later versions support syntax highlighting. Uncommenting the next
" line enables syntax highlighting by default.
if has("syntax")
  syntax on
endif

" If using a dark background within the editing area and syntax highlighting
" turn on this option as well
"set background=dark

" Uncomment the following to have Vim jump to the last position when
" reopening a file
"if has("autocmd")
"  au BufReadPost * if line("'\"") > 1 && line("'\"") <= line("$") | exe "normal! g'\"" | endif
"endif

" Uncomment the following to have Vim load indentation rules and plugins
" according to the detected filetype.
"if has("autocmd")
"  filetype plugin indent on
"endif

" The following are commented out as they cause vim to behave a lot
" differently from regular Vi. They are highly recommended though.
"set showcmd		" Show (partial) command in status line.
"set showmatch		" Show matching brackets.
"set ignorecase		" Do case insensitive matching
"set smartcase		" Do smart case matching
"set incsearch		" Incremental search
"set autowrite		" Automatically save before commands like :next and :make
"set hidden             " Hide buffers when they are abandoned
"set mouse=a		" Enable mouse usage (all modes)

" Source a global configuration file if available
"if filereadable("/etc/vim/vimrc.local")
"  source /etc/vim/vimrc.local
"endif

"不要vim模仿vi模式，建议设置，否则会有很多不兼容的问题 
set nocompatible

"--------------------------------状态栏设置--------------------------------
"总是显示状态栏status line
set laststatus=2

highlight StatusLine cterm=bold ctermfg=yellow ctermbg=blue

function! CurDir()
	let curdir = substitute(getcwd(), $HOME, "~", "g")
	return curdir
endfunction

set statusline=[%n]\ %f%m%r%h\ \|\ \ CWD:\ %{CurDir()}/\ %=\|\ %l,%c\ %p%%\ \|\ ascii=%b%{((&fenc==\"\")?\"\":\"\ \|\ \".&fenc)}\ \|\ %{$USER}\ @\ %{hostname()}\

"--------------------------------------------------------------------------


"--------------------------------Tab按钮设置-------------------------------
"第一行设置tab键为4个空格，第二行设置当行之间交错时使用4个空格
set shiftwidth=4

"让一个tab等于4个空格 
set tabstop=4  

" 使用4个空格来代替tab 简写 set sts=4
set softtabstop=4 

" 将插入状态下的tab 更改为空格 简写 set et
set expandtab

"--------------------------------------------------------------------------


"高亮显示结果
set hlsearch
  
"取消使用SWP文件缓冲
set noswapfile

"当vim进行编辑时，如果命令错误，会发出一个响声，该设置去掉响声
set vb t_vb=

"允许退格键的使用 
set backspace=indent,eol,start

"左右光标移动到头时可以自动下移
set whichwrap=b,s,<,>,[,]

"设置为>-格式,用$结尾
set listchars=tab:>-,eol:$

