#!/bin/bash
/bin/bash $HOME/.vim/lookupfiletags.sh $1
/bin/bash $HOME/.vim/ctags.sh $1
/bin/bash $HOME/.vim/cscopetags.sh $1

