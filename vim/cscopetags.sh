#!/bin/sh
# generate tag file for lookupfile plugin
find $1 -regex '.*\.\(php\|phtml\|js\|html\|html\.twig\|c\|h\)' -type f > cscope.files
cscope -bq
