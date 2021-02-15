<?php
$memcache_host = '192.168.1.24';
/* OO API */
$memcache_obj = new Memcache;
$memcache_obj->connect($memcache_host, 11211);
$status = $memcache_obj->flush();
var_dump($status);
