<?php
apf_require_class("APF_Controller");

/**
 * Resource 新加载器
 * @todo 新老版本兼容、http 200 from cache || http 304
 * @version 1.0
 * @packaged default
 * @author rockywu 吴佳雷(2014-08-01 Fri 17:06:21)
 **/
class Kfs_Resource_ResourcesMyController extends APF_Controller{

    const CONFIG_F_RESOURCE                   = 'resource';

    const CONFIG_N_VERSION                    = 'version';

    const CONFIG_N_PREFIX_URI                 = 'prefix_uri';

    const CONFIG_N_RESOURCE_TYPE_SINGLE       = 'resource_type_single';

    const CONFIG_N_RESOURCE_TYPE_BOUNDABLE    = 'resource_type_boundable';

    const CONFIG_N_RESOURCE_TYPE_HASH         = 'resource_type_hash';

    const CONFIG_N_RESOURCE_TYPE_DECORATOR    = 'resource_type_decorator';

    const CONFIG_N_RESOURCE_REGULAR_SWITCH    = 'resource_regular_switch';

    const DEFAULT_N_RESOURCE_REGULAR_SWITCH   = false;

    const CONFIG_N_RESOURCE_DECORATOR_SWITCH  = 'resource_decorator_switch';

    const DEFAULT_N_RESOURCE_DECORATOR_SWITCH = false;

    const DEFAULT_F_D_NAME                    = "get_decorator_name";

    const DEFAULT_F_B_COMPONENTS              = "use_component";

    const DEFAULT_F_B_JAVASCRIPTS             = "use_boundable_javascripts";

    const DEFAULT_F_B_STYLES                  = "use_boundable_styles";

    const DEFAULT_PREFIX_URI                  = 'res';

    const DEFAULT_RESOURCE_TYPE_SINGLE        = 's';

    const DEFAULT_RESOURCE_TYPE_BOUNDABLE     = 'b';

    const DEFAULT_RESOURCE_TYPE_HASH          = 'h';

    const DEFAULT_RESOURCE_TYPE_DECORATOR     = 'd';

    const DEFAILT_MATCH_OLD_TYPE              = 1;

    const DEFAILT_MATCH_OLD_FILE              = 2;

    const DEFAILT_MATCH_OLD_EXT               = 3;

    const DEFAILT_MATCH_NEW_VERSION           = 1;

    const DEFAILT_MATCH_NEW_TYPE              = 2;

    const DEFAILT_MATCH_NEW_FILE              = 3;

    const DEFAILT_MATCH_NEW_EXT               = 4;

    private $vaild_resources                  = array();

    private $boundable_resources              = array();

    private $decorator_resources              = array();

    /**
     * Returns the uri of single resource
     *
     * @param string $resource full name of the resource
     * @return string
     */
    public static function build_uri($resource) {
        if (preg_match('/:\/\//', $resource)) {
            return $resource;
        }
        $parameters = self::init_parameters();
        $apf = APF::get_instance();
        $version = @$apf->get_config(self::CONFIG_N_VERSION, self::CONFIG_F_RESOURCE);
        $prefix = $parameters['prefix'];
        $type = $parameters['single'];
        if (isset($version)) {
            return "$prefix/$version/$type/$resource";
        } else {
            return "$prefix/$type/$resource";
        }
    }


    /**
     * Returns the uri of boundable resource
     *
     * @param string $resource Page name
     * @param string $ext File extention without prefix dot
     * @return string
     */
    public static function build_boundable_uri($resource, $ext) {
        $apf = APF::get_instance();
        $version = @$apf->get_config(self::CONFIG_N_VERSION, self::CONFIG_F_RESOURCE);
        $prefix = $parameters['prefix'];
        $type = $parameters['boundable'];
        if (isset($version)) {
            return "$prefix/$version/$type/$resource.$ext";
        } else {
            return "$prefix/$type/$resource.$ext";
        }
    }

    /**
     * Returns the uri of decorator resource
     *
     * @param string $resource Page name
     * @param string $ext File extention without prefix dot
     * @return string
     */
    public static function build_decorator_uri($resource, $ext) {
        $apf = APF::get_instance();
        $version = @$apf->get_config(self::CONFIG_N_VERSION, self::CONFIG_F_RESOURCE);
        $prefix = $parameters['prefix'];
        $type = $parameters['decorator'];
        if (isset($version)) {
            return "$prefix/$version/$type/$resource.$ext";
        } else {
            return "$prefix/$type/$resource.$ext";
        }
    }

    /**
     * resource资源新加载器
     * 老script_uri route 规则:
     *  array(
     *      '^/res/[^\/]+/(s|b|h|g)/(.*)\.(css|js)$',
     *      '^/res/(s|b|h|g)/(.*)\.(css|js)$'
     *  )
     * 新script_uri route 规则:
     *  array(
     *      '^/res/([^\/]+/)?(s|b|h|g)/(.*)\.(css|js)$'
     *  )
     * 新老差别 新增version标识位:
     *      老：match_type = 1, match_file = 2, match_ext = 3
     *      新：match_version = 1, match_type = 2, match_file = 3, match_ext = 4
     * 解析类型扩充: 原类型(s|b|h) 扩充后(s|b|h|g)
     * @return string
     * @author rockywu 吴佳雷
     */
    public function handle_request ()
    {
        $apf=APF::get_instance();
        $request=$apf->get_request();
        $response=$apf->get_response();
        $matches = $request->get_router_matches();
        //判断是否使用新route规则
        if(self::is_use_new_regular()) {
            $version = rtrim($matches[self::DEFAILT_MATCH_NEW_VERSION], "/");
            $type    = $matches[self::DEFAILT_MATCH_NEW_TYPE];
            $file    = $matches[self::DEFAILT_MATCH_NEW_FILE];
            $ext     = $matches[self::DEFAILT_MATCH_NEW_EXT];
        } else {
            $version = '';
            $type    = $matches[self::DEFAILT_MATCH_OLD_TYPE];
            $file    = $matches[self::DEFAILT_MATCH_OLD_FILE];
            $ext     = $matches[self::DEFAILT_MATCH_OLD_EXT];
        }
        if($ext == 'css'){
            $content_type="text/css";
        }elseif($ext == 'js'){
            $content_type="application/x-javascript";
        }else{
            trigger_error("Unknown extention \"$ext\"",E_USER_ERROR);
            return;
        }
        $this->access_control_header();
        // 发送内容类型头指令
        $response->set_content_type($content_type);
        $this->try304();
        $contents = "";
        ob_start();
        $this->get_contents($version, $type, $file, $ext);
        $contents = ob_get_contents();
        ob_end_clean();
        return $this->output_contents($contents);
    }

    /**
     * 获取加载资源
     *
     * @return void
     * @author rockywu 吴佳雷
     */
    protected function get_contents($version, $type, $file, $ext)
    {
        $apf = APF::get_instance();
        $parameters = self::init_parameters();
        switch($type) {
            case $parameters['single']:
                // 单个独立资源文件
                if (!$this->include_resource_file("$file.$ext")) {
                    trigger_error("Unable to include resource \"$file.$ext\"",
                        E_USER_WARNING);
                }
                break;
            case $parameters['boundable']:
                //检查是否开启独立装饰器资源加载
                $this->fetch_boundable_resources(self::get_independent_decorator_name($file), $ext, true, $this->decorator_resources);
                if(!$this->fetch_boundable_resources($file, $ext, true, $this->boundable_resources)) {
                    //无法合并时执行
                    $this->get_file_failed($file, $ext);
                    return;
                }
                $this->do_duplicate_filter();
                $this->passthru_boundable_resources();
                break;
            case $parameters['hash']:

                break;
            case $parameters['decorator']:
                $this->fetch_boundable_resources(self::get_independent_decorator_name($file), $ext, true, $this->decorator_resources);
                $this->boundable_resources = $this->decorator_resources;
                $this->passthru_boundable_resources();
                break;
        }

    }

    /**
     * 重复过滤器
     * @return boolean
     * @author rockywu 吴佳雷(2014-08-01 Fri 17:06:21)
     */
    protected function do_duplicate_filter()
    {
        if(!self::is_use_independent_decorator() || empty($this->decorator_resources)) {
            return false;
        }
        foreach($this->boundable_resources as $key=>$value) {
            if(array_key_exists($key, $this->decorator_resources)) {
                unset($this->boundable_resources[$key]);
            }
        }
        return true;
    }


    /**
     * 按顺序输出资源文件内容，多个资源文件合并为一个。
     */
    protected function passthru_boundable_resources() {
        if ($this->boundable_resources) {
            usort($this->boundable_resources,
                "APF::resource_order_comparator");
        }
        if (!is_array($this->boundable_resources)) {
            return ;
        }
        foreach ($this->boundable_resources as $item) {
            if (!$this->include_resource_file($item[0])) {
                trigger_error("Unable to include resource \""
                    . $item[0]."\"",
                    E_USER_WARNING
                );
            }
        }
    }

    /**
     * 获取文件失败后执行
     *
     * @return void
     * @author rockywu 吴佳雷
     */
    private function get_file_failed($file, $ext)
    {
        $new_file = $this->try_new_file($file);
        if ($new_file) { // 301 永久跳转
            $url = "$new_file.$ext";
            $response->redirect($url,true);
        } else { //404
            $response->set_header("HTTP/1.1", "404 Not Found", "404");
            return;
        }
    }



    /**
     * 载入（执行）资源文件
     * @param string $file 文件名
     * @param string $path 文件路径
     */
    protected function include_resource_file($file, $path=NULL) {
        if (isset($path)) {
            if (file_exists($path.$file)) {
                include_once($path.$file);
                return true;
            }
        } else {
            global $G_LOAD_PATH;
            foreach ($G_LOAD_PATH as $path) {
                if (file_exists($path.$file)) {
                    include_once($path.$file);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 实际结果就是更新APF->boundable_resources变量，将用到的资源文件（整合）路径信息
     * 记录到resource_list中。
     * @todo 非递归实现
     * @param string $class 文件名<->资源文件名与页面类名是一一对应的
     * @param string $ext 文件扩展名
     * @param boolean $is_page 是否为页面类
     * @param boolean $is_decorator 是否是装饰器资源
     */
    protected function fetch_boundable_resources($class, $ext, $is_page = false, &$attribute)
    {
        if ($is_page === true) { // 载入页面类
            apf_require_page($class);
            $path =  "page/";
            $class = $class."Page";
        } else { // 载入组件类
            apf_require_component($class);
            $path =  "component/";
            $class = $class."Component";
        }
        $fn_c = self::DEFAULT_F_B_COMPONENTS;
        if (!class_exists($class)) {
            return false;
        }
        if(!method_exists($class, $fn_c)) {
            //过滤不可执行资源
            return true;
        }
        eval("\$list = $class"."::$fn_c();");
        foreach($list as $item) {
           $this->fetch_boundable_resources($item, $ext, false, $attribute);
        }
        $rows = $this->get_resource_list_by_ext($class, $ext);
        foreach($rows as $row) {
            APF::get_instance()->prcess_resource_url($path,$row, $attribute);
        }
        return true;
    }

    /**
     * 获取资源列表
     * @param string $class
     * @param string $ext
     * @param boolean $is_cache_fun
     * @return array 
     * @author rockywu 吴佳雷
     */
    protected function get_resource_list_by_ext($class, $ext)
    {
        $rows = array();
        $fn_j = self::DEFAULT_F_B_JAVASCRIPTS;
        $fn_s = self::DEFAULT_F_B_STYLES;
        //加载不同类型资源
        if($ext == 'js'){
            if(method_exists($class, $fn_j)) {
                eval("\$rows = $class::$fn_j();");
            }
        } elseif ($ext == 'css') {
            if(method_exists($class, $fn_s)) {
                eval("\$rows = $class::$fn_s();");
            }
        } else {
            trigger_error("Unknown extention \"$ext\"", E_USER_WARNING);
        }
        return $rows;
    }

    /**
     * 输出页面内容内容
     * @param string $content
     * @return void
     * @author rockywu 吴佳雷
     */
    protected function output_contents($contents)
    {
        echo $contents;
    }

    /**
     * 是否启用新route 规则
     *
     * @return boolean
     * @author rockywu 吴佳雷
     */
    public static function is_use_new_regular()
    {
        $bool   = APF::get_instance()->get_config(self::CONFIG_N_RESOURCE_REGULAR_SWITCH, self::CONFIG_F_RESOURCE);
        return $bool === true ?  $bool : self::DEFAULT_N_RESOURCE_REGULAR_SWITCH;
    }

    /**
     * 是否启用装饰器资源文件分离机制
     *
     * @return boolean
     * @author rockywu 吴佳雷
     */
    public static function is_use_independent_decorator()
    {
        $bool   = APF::get_instance()->get_config(self::CONFIG_N_RESOURCE_DECORATOR_SWITCH, self::CONFIG_F_RESOURCE);
        return $bool === true ?  $bool : self::DEFAULT_N_RESOURCE_DECORATOR_SWITCH;
    }

    /**
     * 获取分装饰器资源分离制装饰器名
     * @param string $page
     * @return mixed
     * @author rockywu 吴佳雷
     */
    public static function get_independent_decorator_name($class)
    {
        if(!self::is_use_independent_decorator()) {
            return null;
        }
        apf_require_page($class);
        //装饰器资源获取
        $fn_d = self::DEFAULT_F_D_NAME;
        $class = $class."Page";
        if(!method_exists($class, $fn_d)) {
            //无装饰器配置
            return null;
        }
        eval("\$class = $class"."::$fn_d();");
        return $class;
    }


    /**
     * 初始化可用参数
     *
     * @return array
     * @author rockywu 吴佳雷
     */
    public static function init_parameters()
    {
        $request   = APF::get_instance()->get_request();
        $single    = self::get_resource_real_config(self::CONFIG_N_RESOURCE_TYPE_SINGLE, self::DEFAULT_RESOURCE_TYPE_SINGLE);
        $boundable = self::get_resource_real_config(self::CONFIG_N_RESOURCE_TYPE_BOUNDABLE, self::DEFAULT_RESOURCE_TYPE_BOUNDABLE);
        $hash      = self::get_resource_real_config(self::CONFIG_N_RESOURCE_TYPE_HASH, self::DEFAULT_RESOURCE_TYPE_HASH);
        $decorator = self::get_resource_real_config(self::CONFIG_N_RESOURCE_TYPE_DECORATOR, self::DEFAULT_RESOURCE_TYPE_DECORATOR);
        $prefix    = self::get_resource_real_config(self::CONFIG_N_PREFIX_URI, self::DEFAULT_PREFIX_URI);
        $parameters  = array(
            'single'    => $single,
            'boundable' => $boundable,
            'hash'      => $hash,
            'decorator' => $decorator,
            'prefix'    => $prefix
        );
        return $parameters;
    }

    public static function get_resource_real_config($name, $def) {
        $val   = APF::get_instance()->get_config($name, self::CONFIG_F_RESOURCE);
        return empty($val) ? $def : $val;
    }

    /**
     * 获取文件
     * @param string $class
     * @return mixed
     * @author rockywu 吴佳雷
     */
    public function try_new_file($class){
        global $G_LOAD_PATH;
        $file = apf_classname_to_filename($class);
        $position = strrpos($file,"/");
        $pathmid = "page/".substr($file,0,$position);
        $fileName = substr($file,$position+1);
        foreach ($G_LOAD_PATH as $pathpre) {
            $files = glob("$pathpre$pathmid/*.php");
            foreach($files as $tmpfile){
                $pos1 = strrpos($tmpfile,"/")+1;
                $pos2 = strrpos($tmpfile,".");
                $trueName = substr($tmpfile,$pos1,$pos2-$pos1);
                if(strtolower($fileName) == strtolower($trueName)){
                    $newClass = substr_replace($class,
                        $trueName,
                        strrpos($class,"_")+1);
                    return $newClass;
                }
            }
        }
        return null;
    }

    /**
     * 是否发送304未改动指令
     * @return boolean
     */
    protected function try304(){
        // 返回304需要定义发布版本
        if(!defined("RELEASE_VERSION")){
            return false;
        }

        $apf=APF::get_instance();
        $response=$apf->get_response();

        $last_modified=strtotime("2009-06-04 00:00:00");

        if(isset($last_modified)){
            $etag='"'.dechex($last_modified).'"';
        }

        if(isset($etag)){
            $none_match=@$_SERVER['HTTP_IF_NONE_MATCH'];
            if($none_match&&$none_match==$etag){
                $response->set_header("HTTP/1.1","304 ETag Matched","304");
                return true;
            }
        }

        if(isset($last_modified)&&isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
            $tmp=explode(";",$_SERVER['HTTP_IF_MODIFIED_SINCE']);
            $modified_since=@strtotime($tmp[0]);
            if($modified_since&&$modified_since>=$last_modified){
                $response->set_header("HTTP/1.1","304 Not Modified","304");
                return true;
            }
        }

        if(isset($last_modified)){
            $response->set_header("ETag",$etag);
            $response->set_header("Last-Modified",
                gmdate("D, d M Y H:i:s", $last_modified) . " GMT");
        }

        return false;
    }

    /**
     * 添加跨域访问控制
     *
     * @return void
     * @author rockywu 吴佳雷
     */
    protected function access_control_header () {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $pattern = ';^(https?://)?(([a-zA-Z][a-zA-Z0-9\-]*\.)+[a-zA-Z][a-zA-Z0-9\-]*);';
            $hostname = $_SERVER['HTTP_REFERER'];
            $flag = preg_match($pattern, $hostname, $matches);
            if ($flag) {
                if ($matches[1]) {
                    $hostname = $matches[0];
                } else {
                    $hostname = 'http://' + $matches[0];
                }
                if (preg_match(';.+\.(anjuke|aifang|haozu|jinpu)\.(com|test);i', $hostname, $m)) {
                    $response = APF::get_instance()->get_response();
                    $response->set_header('Access-Control-Allow-Origin', $hostname);
                    $response->set_header('Access-Control-Allow-Methods', 'GET');
                }
            }
        }
    }

}
