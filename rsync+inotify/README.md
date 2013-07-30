###添加实时同步脚本

> 1. 需要安装插件 `sudo apt-get install rsync` + `sudo apt-get install inotify-tools`

> 接受端模块配置
    uid=root
    gid=root
    # 这个test就是上面脚本中用到的rsync_module名
    # path指定同步过来的文件存放的路径
    # 如果只允许部分ip的机器进行同步的话，设置allow为 192.168.1.1/100 类似的格式
    [test]
    path=/path/to/your/dir
    allow *

> 重启rsync 服务 `rsync --daemon`
