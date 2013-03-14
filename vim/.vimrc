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


"--------------------------------基本设置--------------------------------
"不要vim模仿vi模式，建议设置，否则会有很多不兼容的问题 
set nocompatible

"Set mapleader
let mapleader = ","
let g:mapleader = ","

"高亮显示结果
set hlsearch

"show matching bracets
set showmatch
 
"在输入要搜索的文字时，vim会实时匹配 
set incsearch

"取消使用SWP文件缓冲
set noswapfile

"当vim进行编辑时，如果命令错误，会发出一个响声，该设置去掉响声
set noerrorbells
set vb t_vb=

"允许退格键的使用 
set backspace=indent,eol,start

"左右光标移动到头时可以自动下移
set whichwrap=b,s,<,>,[,]

"将一个tab设置为>-格式,用$结尾
set listchars=tab:>-,eol:$

"vim使用自动对起，也就是把当前行的对起格式应用到下一行；
set autoindent

"依据上面的对起格式，智能的选择对起方式，对于类似C语言编
set smartindent

"显示不可见字符
set list

"显示行号
set number 

"设置自动换行字符长度
set textwidth=180

"设置折行
"set nowrap "不自动折行
set nowrap "自动折行 

"*自动设置目录为正在编辑的文件所在目录*
"set autochdir

"Set to auto read when a file is changed from the outside
set autoread



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

"暂不确定
"au FileType html,python,vim,javascript,xml,php setl shiftwidth=4
"au FileType html,python,vim,javascript,xml,php setl tabstop=4
"au FileType html,python,vim,javascript,xml,php setl expandtab
"au FileType html,python,vim,javascript,xml,php setl softtabstop=4

"--------------------------------------------------------------------------


"--------------------------------字体设置--------------------------------
if has("multi_byte")
  set fileencodings=ucs-bom,utf-8,cp936,gb18030,big5,euc-jp,sjis,euc-kr,ucs-2le,latin1 
  if v:lang =~ "^zh_CN" 
    set encoding=chinese 
    set termencoding=chinese 
    set fileencoding=chinese 
  elseif v:lang =~ "^zh_TW" 
    set encoding=taiwan 
    set termencoding=taiwan 
    set fileencoding=taiwan
  endif 
  if v:lang =~ "utf8$" || v:lang =~ "UTF-8$" 
    set encoding=utf-8 
    set termencoding=utf-8 
    set fileencoding=utf-8 
  endif 
endif 
if has("gui_gtk2")
    set gfn=Courier\ New\ 10,Courier\ 10,Luxi\ Mono\ 10,
          \DejaVu\ Sans\ Mono\ 10,Bitstream\ Vera\ Sans\ Mono\ 10,
          \SimSun\ 10,WenQuanYi\ Micro\ Hei\ Mono\ 10
elseif has("x11")
    set gfn=*-*-medium-r-normal--10-*-*-*-*-m-*-*
endif
set guifont=Bitstream_Vera_Sans_Mono:h9:cANSI "记住空格用下划线代替哦  
set gfw=幼圆:h10:cGB2312 

" Avoid clearing hilight definition in plugins
if !exists("g:vimrc_loaded")
    "Enable syntax hl
    syntax enable

    "color scheme
    if has("gui_running")
        set guioptions-=T
        set guioptions-=m
        set guioptions-=L
        set guioptions-=r
        colorscheme darkblue_my
        set cursorline
    else
        colorscheme desert_my
    endif " has
endif

"--------------------------------------------------------------------------


"--------------------------------自定义方法设置--------------------------------
" Switch to buffer according to file name
function! SwitchToBuf(filename)
    "let fullfn = substitute(a:filename, "^\\~/", $HOME . "/", "")
    " find in current tab
    let bufwinnr = bufwinnr(a:filename)
    if bufwinnr != -1
        exec bufwinnr . "wincmd w"
        return
    else
        " find in each tab
        tabfirst
        let tab = 1
        while tab <= tabpagenr("$")
            let bufwinnr = bufwinnr(a:filename)
            if bufwinnr != -1
                exec "normal " . tab . "gt"
                exec bufwinnr . "wincmd w"
                return
            endif
            tabnext
            let tab = tab + 1
        endwhile
        " not exist, new tab
        exec "tabnew " . a:filename
    endif
endfunction

"--------------------------------------------------------------------------


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


"--------------------------------快速键设置--------------------------------
"Fast reloading of the .vimrc
map <silent> <leader>ss :source ~/.vim/.vimrc<cr>

"Fast editing of .vimrc
map <silent> <leader>ee :call SwitchToBuf("~/.vim/.vimrc")<cr>

"实现CTRL-S保存操作
nmap <c-s> :w<CR>
imap <c-s> <Esc>:w<CR>a

"快速保存快捷键
nmap <silent> <leader>ww :w<cr>
nmap <silent> <leader>wf :w!<cr>

"快速退出快捷键
nmap <silent> <leader>qw :wq<cr>
nmap <silent> <leader>qf :q!<cr>
nmap <silent> <leader>qq :q<cr>
nmap <silent> <leader>qa :qa<cr>

"Tab configuration
map <leader>tn :tabnew
map <leader>te :tabedit
map <leader>tc :tabclose<cr>
map <leader>tm :tabmove
nmap <silent> <leader>bp :tabprevious<cr>
nmap <silent> <leader>bn :tabnext<cr>
"创建草稿文件
map <leader>es :tabnew<cr>:setl buftype=nofile<cr>
"创建临时文件
map <leader>ec :tabnew ~/tmp/scratch.txt<cr>

"--------------------------------------------------------------------------


"--------------------------------插件设置-----------------------------------
" winmanager setting
let g:winManagerWindowLayout = "BufExplorer,FileExplorer|TagList"
let g:winManagerWidth = 30
let g:defaultExplorer = 0
nmap <silent> <leader>wf :FirstExplorerWindow<cr>
nmap <silent> <leader>wb :BottomExplorerWindow<cr>
nmap <silent> <leader>wm :WMToggle<cr>
autocmd BufWinEnter \[Buf\ List\] setl nonumber


" NERDTree setting
nmap <silent> <leader>tt :NERDTreeToggle<cr>


" super tab
"let g:SuperTabPluginLoaded=1 " Avoid load SuperTab Plugin
"let g:SuperTabDefaultCompletionType='context'
"let g:SuperTabContextDefaultCompletionType='<c-p>'
"let g:SuperTabCompletionContexts = ['s:ContextText', 's:ContextDiscover']
"let g:SuperTabContextTextOmniPrecedence = ['&omnifunc', '&completefunc']
"let g:SuperTabContextDiscoverDiscovery = ["&completefunc:<c-x><c-u>", "&omnifunc:<c-x><c-o>"]
"--------------------------------------------------------------------------


"--------------------------------其他设置-----------------------------------
"一些不错的映射转换语法（如果在一个文件中混合了不同语言时有用）
nnoremap <leader>html :set filetype=xhtml<CR>
nnoremap <leader>css :set filetype=css<CR>
nnoremap <leader>script :set filetype=javascript<CR>
nnoremap <leader>python :set syntax=python<CR> 

"--------------------------------------------------------------------------







