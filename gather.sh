#!/bin/bash
file="$1"
tap="$2"
awk -v file="$file" '$8=="m.anjuke.com"{if((substr($11, 1,4) =="HTTP"&&(pre=$11)&&(code=$12)) || (substr($12, 1,4) =="HTTP"&&(pre=$12)&&(code=$13)) || (substr($13, 1,4) =="HTTP" &&(pre=$13)&&(code=$14)) || (substr($14, 1,4) =="HTTP" &&(pre=$14)&&(code=$15))){arr[pre" "code]++;print file" "$8" "$10" "pre >> "'"$tap"'.out";}}END{for(i in arr)print file" "i" "arr[i] >> "'"$tap"'.data"}' "$file"



