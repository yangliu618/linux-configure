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
set showcmd		" Show (partial) command in status line.
"set showmatch		" Show matching brackets.
"set ignorecase		" Do case insensitive matching
"set smartcase		" Do smart case matching
"set incsearch		" Incremental search
"set autowrite		" Automatically save before commands like :next and :make
set hidden             " Hide buffers when they are abandoned
"set mouse=a		" Enable mouse usage (all modes)

" Source a global configuration file if available
if filereadable("/etc/vim/vimrc.local")
  source /etc/vim/vimrc.local
endif

"第一行设置tab键为4个空格，第二行设置当行之间交错时使用4个空格
set tabstop=4
set shiftwidth=4

set number

set noswapfile

set nocompatible "不要vim模仿vi模式，建议设置，否则会有很多不兼容的问题 
if has("autocmd")  
    filetype plugin indent on "根据文件进行缩进  
    augroup vimrcEx  
        au!  
        autocmd FileType text setlocal textwidth=78  
        autocmd BufReadPost *  
                    \ if line("'\"") > 1 && line("'\"") <= line("$") |  
                    \ exe "normal! g`\"" |  
                    \ endif  
    augroup END  
else  
    "智能缩进，相应的有cindent，官方说autoindent可以支持各种文件的缩进，但是效果会比只支持C/C++的cindent效果会差一点，但笔者并没有看出来  
set autoindent " always set autoindenting on   
endif " has("autocmd")  
set tabstop=4 "让一个tab等于4个空格  
set nowrap "不自动换行  
set hlsearch "高亮显示结果  
set incsearch "在输入要搜索的文字时，vim会实时匹配  
set backspace=indent,eol,start whichwrap+=<,>,[,] "允许退格键的使用  
  
"鼠标可用  
"防止linux终端下无法拷贝  
"au GUIEnter * simalt ~x  
  
"字体的设置  
set guifont=Bitstream_Vera_Sans_Mono:h9:cANSI "记住空格用下划线代替哦  
set gfw=幼圆:h10:cGB2312  
set history=1000 

"第一行，vim使用自动对起，也就是把当前行的对起格式应用到下一行；
"第二行，依据上面的对起格式，智能的选择对起方式，对于类似C语言编
set autoindent
set smartindent


"去除vim的GUI版本中的toolbar
set guioptions-=T

"当vim进行编辑时，如果命令错误，会发出一个响声，该设置去掉响声
set vb t_vb=

"在编辑过程中，在右下角显示光标位置的状态行
set ruler

"修改一个文件后，自动进行备份，备份的文件名为原文件名加“~“后缀
"if has("vms")
"	set nobackup
"else
"	set backup
"endif

set whichwrap=b,s,<,>,[,] "左右光标移动到头时可以自动下移

set autochdir "自动设置目录为正在编辑的文件所在目录

set laststatus=2	"总是显示状态栏status line

"==========自定义的键映射=================="
"实现CTRL-S保存操作
nmap <c-s> :w<CR>
imap <c-s> <Esc>:w<CR>a

"使用F2上翻页
map <F2> <c-e>	
"使用F3页
map <F3> <c-y>	
map <silent> <F12> :nohlsearch<CR>

"使用左右方向键来切换tab
map <left> :tabprevious<cr>
map <right> :tabnext<cr>

"Set mapleader
let g:mapleader = ","

"一些不错的映射转换语法（如果在一个文件中混合了不同语言时有用）
nnoremap <leader>html :set filetype=xhtml<CR>
nnoremap <leader>css :set filetype=css<CR>
nnoremap <leader>script :set filetype=javascript<CR>
nnoremap <leader>php :set filetype=php<CR> 

let $dir = '/var/www'

let $rel = $dir.'/release'

let $ide = $dir.'/ideliver'

highlight StatusLine cterm=bold ctermfg=yellow ctermbg=blue

function! CurDir()
	let curdir = substitute(getcwd(), $HOME, "~", "g")
	return curdir
endfunction
set statusline=[%n]\ %f%m%r%h\ \|\ \ pwd:\ %{CurDir()}/%f%m%r%h\ \ \|%=\|\ %l,%c\ %p%%\ \|\ ascii=%b,hex=%b%{((&fenc==\"\")?\"\":\"\ \|\ \".&fenc)}\ \|\ %{$USER}\ @\ %{hostname()}\
