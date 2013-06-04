#!/bin/sh
ctags -R --langmap=php:.php,javascript:.js,c:+.h,c:.c $1
