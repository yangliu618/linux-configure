<?php
apf_require_class('APF_DB_Factory');
apf_require_class('Aifang_Core_Util_Tools');
apf_require_class('Aifang_Core_Cache_Util');
apf_require_class('Aifang_Core_Cache_Redis');
apf_require_class('Aifang_Core_DaoException');
apf_require_class('Aifang_Core_Cache_SyncKeyCollection');
apf_require_class('Aifang_Core_Util_BadWord');
apf_require_class('Aifang_Core_DaoStat');
apf_require_class('Aifang_System_Log_Factory');
apf_require_class('Aifang_Core_Util_Alarm');
abstract class Aifang_Core_DAO {

    /**
     *
     * 是否启用local cache,如果运行job，就不能打开local cache ,否则占用内存太多
     * @var boolean
     */
    public $enable_local_cache = true;

    /**
     * 是否要从master 数据库上读
     * @var boolean
     */
    public $read_from_master = false;

    /**
     * memcache key 的前缀
     * @var string
     */
    const MEMCACHE_KEY_PREFIX = "af-core-dao-";

    /**
     * 为了和java缓存同步，需要删除java版本的缓存
     * @var string
     */
    const MEMCACHE_JAVA_KEY_PREFIX = "af-java-dao-";

    /**
     * 如果 table 没有 primary key 则使用 缺省的
     * @var string
     */
    const NONE_TABLE_PK = "nonepk";

    /**
     * insert 的时候是否要请除整表的cache
     * @var boolean
     */
    public $remove_tag_cache_when_insert = true;

    /**
     * 更新的时候是否要清除整表的cache
     * @var boolean
     */
    public $remove_tag_cache_when_update = true;

    /**
     * 更新表的时候是否要更新表的tag, tag 被更新后如果不更新 tag cache
     * 5 分钟之后整表的 cache 都将被清除
     * @var boolean
     */
    public $update_tag_when_update = true;

    public $cache_expire_time = 0;

    public $cache_expire_time_pk = 0;

    /**
     * 同步的data保存的时间，时间是8 Hour
     * @var int
     */
    protected $sync_data_expire_time = 28800;

    /**
     * 根据一个where条件更新表后，对应的主键缓存需要更新
     * 但是当一个where对应很多ID时，就需要整表过期主键缓存，并且要记录这个情况
     * @var int
     */
    private $threshold_for_delete_pk_by_where = 100;

    /**
     * @param int $threshold_for_delete_pk_by_where
     */
    public function set_threshold_for_delete_pk_by_where($threshold_for_delete_pk_by_where) {
        $this->threshold_for_delete_pk_by_where = $threshold_for_delete_pk_by_where;
    }

    public function set_sync_data_expire_time($sync_data_expire_time) {
        $this->sync_data_expire_time = intval($sync_data_expire_time);
    }

    public function set_sync_key_expire_time($sync_key_expire_time) {
        self::$keyCollection->set_sync_key_expire_time($sync_key_expire_time);
    }

    /**
     *
     * 缓存时是否缓存空
     * @var boolean
     */
    public $enable_cache_empty = true;

    /**
     *
     * @var Aifang_Core_Cache_SyncKeyCollection
     */
    private static $keyCollection;

    /**
     *
     * @var APF
     */
    private static $apf;

    public function get_cache_expire_time_pk() {
        return $this->cache_expire_time_pk;
    }

    public function get_cache_expire_time() {
        return $this->cache_expire_time;
    }

    public function enable_local_cache() {
        return false;
    }

    /**
     *
     * @var Aifang_System_Log_Logger
     */
    private static $logger;

    /**
     * 用于同步外键缓存
     * @var array
     */
    protected $main_columns = array();

    /**
     *
     * 用于敏感词过滤
     * @var array
     */
    protected $filter_columns = array();

    protected $filter_html_columns = array();

    private $default_limit = 500.1;

    /**
     * enable_simple_cache & enable_complex_cache 用来替换之前的 enable_cache
     * enable_complex_cache 在admin中将会是false
     * @var boolean
     */
    public static $enable_simple_cache = true;

    /**
     *
     * @var boolean
     */
    public static $enable_complex_cache = true;

    /**
     * 非标准方法，是修改了php源代码，自定义了一个取得替换过bindParams的真正的sql
     * @var string
     */
    private static $enable_final_sql = null;

    public function __construct() {

        if(self::$apf == null) {
            self::$apf = APF::get_instance();
        }

        if(self::$logger == null) {
            self::$logger = Aifang_System_Log_Factory::getLogger(__CLASS__);
        }

        if(self::$keyCollection == null) {
            self::$keyCollection = Aifang_Core_Cache_SyncKeyCollection::get_instance();
        }

        if(self::$enable_final_sql === null) {
            self::$enable_final_sql = method_exists('PDOStatement', 'getFinalSql');
        }

        self::$enable_simple_cache &= self::$apf->get_config('enable_simple_cache');
        self::$enable_complex_cache &= self::$apf->get_config('enable_complex_cache');

    }

    /**
     * 返回 当前dao对应的数据库表的名称
     * @return string - table name
     */
    abstract public function get_table_name();

    /**
     * 返回当前pdo的名称
     * @return string - pdo name
     */
    abstract public function get_read_pdo_name();

    abstract public function get_write_pdo_name();

    /**
     * return the table primary key name
     * @return string - primary key name
     */
    abstract public function get_table_pk();

    /**
     * 用于根据一个复杂的完整SQL查询出结果
     * @param string $sql eg: SELECT DISTINCT city_id,STATUS FROM zx_htmlentries_1
     * WHERE `city_id` = ? AND  `name` = ?  ORDER BY id ASC,STATUS DESC LIMIT 10 OFFSET 5
     * @param array $s_params eg:用来替换占位符号的参数 array(11,'abc')
     * @return mixed
     */
    public function find_by_sql($sql = '', $s_params = array()) {
        $sql = trim($sql);
        if(empty($sql)) {
            return false;
        }

        $params = func_get_args();
        $key = $this->get_key("function_find_by_sql_" . json_encode($params));

        if(self::$enable_simple_cache) {
            $rt = Aifang_Core_Cache_Util::get_null_and_set(array(
                    $this,
                    'execute'
            ), array(
                    $sql,
                    $s_params
            ), $this->cache_expire_time, $key, $this->enable_cache_empty);
        } else {
            //使用裸方法
            $rt = $this->execute_sql($sql, $s_params);
        }
        return $rt;
    }

    public function find_short($where = array(), $order = null, $limit = 500.1, $offset = 0, $fields = array()) {
        return $this->find($where, $order, $limit, $offset, $fields);
    }

    /**
     * 从数据库表中获取一行记录，结果是一维数组
     * 参考find
     * @param $where
     * @param $order
     * @return array
     */
    public function find_row($where = array(), $order = null) {
        $rows = (array)$this->find($where, $order, 1, 0);
        $rt = array_pop($rows);
        if($rt === null) {
            $rt = array();
        }
        return $rt;
    }

    /**
     * 查找指定条件的行数(包含去重复计数)
     * @return int - table rowsd
     */
    public function find_count($where = array(), $field = '') {
        return $this->query_cache_template('find_count0', $where, $field);
    }

    private function find_count0($where = array(), $field = '') {
        return $this->_fetch_count($where, $field);
    }

    /**
     * 得到指定字段的总合 SUM
     * @param array $field
     * @param array $where
     * @return int -feild SUM
     * */
    public function find_sum($field, $where = array()) {
        return $this->query_cache_template('find_sum0', $where, $field);
    }

    private function find_sum0($where, $feild) {
        return $this->_fetch_sum($feild, $where);
    }

    /**
     * find table data by primary key
     * @param array $id - primary key value
     * @return array
     */
    public function find_by_id($id) {
        $rs = $this->find_by_ids(array(
                $id
        ));
        if(! empty($rs)) {
            return array_shift($rs);
        } else {
            return array();
        }
    }

    /**
     * find data by multi primary key value
     * @param array $id_array - multi primary key value eg: array($id,$id)
     * @return array
     */
    public function find_by_ids($id_array) {
        if(empty($id_array) || ! is_array($id_array)) {
            return array();
        }
        $bench_key = Aifang_Core_Cache_Util::build_key(__CLASS__, $this->get_table_name() . '_find_by_ids', $id_array);
        self::$logger->trace($bench_key);
        self::$logger->apf_benchmark_begin($bench_key);

        //确保传进来的全部是数字
        $id_array = $this->format_id_array($id_array);
        $key_array = array();
        $list = array();
        foreach($id_array as $id) {
            $key = $this->build_key_pk($id);
            $key_array[] = $key;
            $list[$id]['key'] = $key;
        }

        $data = Aifang_Core_Cache_Util::get($key_array);
        $res = array();
        //需要在数据库中查询的IDS
        $_ids = array();
        foreach($list as $id => $item) {
            if(isset($data[$item['key']])) {
                $res[$id] = $data[$item['key']];
            } else {
                $_ids[] = intval($id);
            }
        }
        if(! empty($_ids)) {
            $_res = $this->_find_by_ids($_ids);

            foreach($_ids as $_id) {
                $_val = ! empty($_res[$_id]) ? $_res[$_id] : array();
                Aifang_Core_Cache_Util::set($this->build_key_pk($_id), $_val, null, $this->get_cache_expire_time_pk());
            }
            if(! empty($_res)) {
                //设置缓存
                $res += $_res;
            }
        }

        $result = array();
        //对数据按照传入ID顺序排序
        if(count($res) > 0) {
            foreach($id_array as $id) {
                //判断是否为空的理由是在上面缓存了空的数据，为了保持和之前的结果一致，这里面在去掉
                if(isset($res[$id]) && ! empty($res[$id])) {
                    $result[$id] = $res[$id];
                }
            }
        }
        self::$logger->apf_benchmark_end($bench_key);
        return $result;
    }

    private function find0($where = array(), $order = array(), $limit = 500, $offset = 0, $fields = array()) {
        $fields = $this->build_fields($fields);
        $sql = "SELECT $fields FROM " . $this->get_table_name();

        try {
            $_where = $this->build_where_new($where);
        } catch (Exception $e) {
            return array();
        }

        $sql .= $_where['where'];

        $sql .= " " . $this->build_order_and_limit($order, $limit, $offset);

        $find_data = $this->execute_sql($sql, $_where['params']);
        return $this->filter_columns($find_data);
    }

    /**
     *
     * 过滤字段，具体什么字段，见$filter_columns
     * @param array $data
     */
    public function filter_columns($data) {
        // 如果当前 dao 配置了banwords_crawler
        // 则表中内容已经经过过滤，此时不需要再过滤
        $this_class = get_class($this);
        $daos = array_keys(APF::get_instance()->get_config('enabled_daos', 'banwords_crawler'));
        if(in_array($this_class, $daos)) {
            return $data;
        }

        if(is_array($data) && count($data) > 0) {

            for($i = 0; $i < count($data); $i++) {
                if($this->filter_columns) {
                    $keys = array_values($this->filter_columns);
                    foreach($keys as $k => $key) {
                        $values[$k] = $data[$i][$key];
                    }
                    $values = Aifang_Core_Util_BadWord::batchFilterBadWords($values);
                    foreach($keys as $k => $v) {
                        if(isset($data[$i][$key])) {
                            $data[$i][$v] = $values[$k];
                        }
                    }
                }
            }
            for($i = 0; $i < count($data); $i++) {
                if($this->filter_html_columns) {
                    $keys = array_values($this->filter_html_columns);
                    foreach($keys as $k => $key) {
                        if(isset($data[$i][$key])) {
                            $data[$i][$key] = Aifang_Core_Util_BadWord::filterHtmlBadWords($data[$i][$key]);
                        }
                    }
                }
            }

        }

        return $data;
    }

    /**
     *
     * @param array $where
     * @param mix $order
     * @param int $limit 500.1 是个标识，表示应用自己没有设置limit，后面的优化有用的到
     * @param int $offset
     * @param array $fields
     * @return array
     */
    public function find($where = array(), $order = array(), $limit = 500.1, $offset = 0, $fields = array()) {
        if($offset < 0) {
            $offset = 0;
        }
        if(($rt = $this->find_to_find_by_id($where)) !== false) {
            return $rt;
        }
        return $this->query_cache_template('find0', $where, $order, $limit, $offset, $fields);
    }

    /**
     * eg
     * SELECT COUNT(*) c ,sale_status FROM loupan_basic WHERE city_id = 11 GROUP BY sale_status ORDER BY c LIMIT 10;
     * find_group_by(array('city_id'=>11),'COUNT(*)','sale_status','c asc',10,0))
     * @param array $where
     * @param string $group_operate eg:count(*) c,sum(xx) c
     * @param mixed $fields 需要分组的字段，例如city_id;city_id,column_id;array(city_id,column_id)多个字段使用逗号分开
     * @param string $order
     * @param int $limit
     * @param int $offset
     */
    public function find_group_by($where, $group_operate, $fields, $order = array(), $limit = 500, $offset = 0) {

        return $this->query_cache_template('find_group_by0', $where, $group_operate, $fields, $order, $limit, $offset);
    }

    private function find_group_by0($where, $group_operate, $fields, $order = array(), $limit = 500, $offset = 0) {
        if(empty($fields) || empty($where) || empty($group_operate)) {
            return array();
        }
        $fields = $this->build_fields($fields);
        $sql = "SELECT $group_operate,$fields FROM " . $this->get_table_name();

        try {
            $_where = $this->build_where_new($where);
        } catch (Exception $e) {
            return array();
        }

        $sql .= $_where['where'];
        $sql .= " GROUP BY $fields";
        $sql .= " " . $this->build_order_and_limit($order, $limit, $offset);

        return $this->execute_sql($sql, $_where['params']);
    }

    /**
     * 从缓存查询数据的模板方法
     * @param string $call_back eg:find0,find_count0,find_sum0
     * @param mixed  $other_params 使用 fun_get_args获取
     */
    public function query_cache_template($call_back, $other_params) {

        $params = func_get_args();
        array_shift($params);

        $key = Aifang_Core_Cache_Util::build_key(__CLASS__, $this->get_table_name() . "_$call_back", $this->get_key("function_{$call_back}_" . json_encode($params)));
        self::$logger->trace($key);
        self::$logger->apf_benchmark_begin($key);

        /**
         * 检测该dao是否支持main_column方式
         * @var string
         */
        $sync_call_back_func = "sync_cache_find_hook";
        if($call_back !== "find0") {
            $sync_call_back_func = "sync_cache_common_hook";
        }
        $sync_call_back_obj = array(
                $this,
                $sync_call_back_func
        );

        /**
         * 没有任何缓存的裸方法
         * @var string
         */

        $_sync_params = $params;
        array_unshift($_sync_params, $call_back);
        if(($rt = call_user_func_array($sync_call_back_obj, $_sync_params)) !== false) {
            //使用同步的方式拦截cache，可以使得缓存保存更长的时间
            self::$logger->apf_benchmark_end($key);
            return $rt;
        }

        $nake_call_back_obj = array(
                $this,
                $call_back
        );

        if(self::$enable_simple_cache) {

            $rt = Aifang_Core_Cache_Util::get_null_and_set($nake_call_back_obj, $params, $this->cache_expire_time, $key, $this->enable_cache_empty);
        } else {

            //使用裸方法
            $rt = call_user_func_array($nake_call_back_obj, $params);
        }
        self::$logger->apf_benchmark_end($key);
        return $rt;
    }

    /**
     * 有很多像'id != ?'这样的查询，让缓存命中率变得不高，其实这种情况很容易避免
     * 多查几个，排除就可以了
     * @param array $exclude_pkids
     * @param array $where
     * @param mixed $order
     * @param int $limit
     * @param int $offset
     * @param array $fields
     */
    public function find_exclude_pkids($exclude_pkids = array(), $where = array(), $order = array(), $limit = 500.1, $offset = 0, $fields = array()) {
        $exclude_pkids = $this->format_id_array($exclude_pkids);
        if(empty($exclude_pkids) || $this->get_table_pk() === self::NONE_TABLE_PK) {
            return $this->find($where, $order, $limit, $offset, $fields);
        }

        $exclude_count = count($exclude_pkids);

        //多查询几个数据
        $rs = $this->find($where, $order, $limit + $exclude_count, $offset, $fields);
        if(empty($rs)) {
            return array();
        }
        $limit = intval($limit);

        $rt = array();
        $pk = $this->get_table_pk();
        foreach($rs as $val) {
            //排除掉不查询的ID
            if(! in_array($val[$pk], $exclude_pkids)) {
                $rt[] = $val;
            }
            if($limit == count($rt)) {
                break;
            }
        }
        return $rt;
    }

    public function find_assoc_exclude_pkids($exclude_pkids = array(), $where = array(), $order = array(), $limit = 500.1, $offset = 0, $fields = array()) {
        return $this->build_assoc($this->find_exclude_pkids($exclude_pkids, $where, $order, $limit, $offset, $fields));
    }

    private function build_assoc($query) {
        if(empty($query)) {
            return array();
        }
        $pk = $this->get_table_pk();
        $rs = array();
        foreach($query as $val) {
            $rs[$val[$pk]] = $val;
        }
        return $rs;
    }

    /**
     * 将之前必须使用主键查询的，但是误用了find方法的
     * 强制改成find_by_id的方式
     * @param array $where
     */
    private function find_to_find_by_id($where) {
        if(is_array($where) && ! empty($where[$this->get_table_pk()]) && count($where) == 1 && ! is_array($where[$this->get_table_pk()])) {
            $tmp = $this->find_by_id($where[$this->get_table_pk()]);
            if(! empty($tmp)) {
                return array(
                        $tmp
                );
            } else {
                return array();
            }
        } else {
            return false;
        }
    }

    /**
     * 拦截默认的cache，将符合规则的，可以进行永久缓存
     * 如果返回false，表示拦截失败
     * @param string $call_back 固定是find0
     * @return boolean|mixed
     */
    private function sync_cache_find_hook($call_back, $where, $order, $limit, $offset, $fields = array()) {

        if(! self::$enable_complex_cache) {
            return false;
        }
        /**
         * eg
         * $where
         *Array
         *
         *(
         * [name] => Array
         * (
         * [0] => AH6_BOT_AD1
         * [1] => AH6_L_DQ2
         * )
         *
         * [status] => 1
         * [city_id] => 11
         *)
         *$synckeys
         *Array
         *(
         * [name] => Array
         * (
         * [AH6_BOT_AD1] => sync_key_prefix_Inform_Core_Dao_Htmlentries_name_AH6_BOT_AD1
         * [AH6_L_DQ2] => sync_key_prefix_Inform_Core_Dao_Htmlentries_name_AH6_L_DQ2
         * )
         *
         *)
         **/

        $sync_keys = $this->explain_where_to_key($where);

        if(empty($sync_keys)) {
            return false;
        }

        //检查main column方式是否使用的是IN，并且只有现在只处理第一个 main column 是IN的方式


        $main_column_in_0 = $this->get_main_column_in_0($sync_keys);
        if($main_column_in_0 && ($limit == $this->default_limit || $limit == 0) && ($offset == 0) && empty($order)) {
            //main column 含有IN，那么走一次查询，缓存多份更高效,参考 find_by_ids
            self::$logger->trace("use complex hook");
            //改写where，将单值查询改写成in的方式
            if(! is_array($where[$main_column_in_0])) {
                $where[$main_column_in_0] = array(
                        $where[$main_column_in_0]
                );
            }
            return $this->complex_hook($main_column_in_0, $sync_keys, $where);
        } else {
            self::$logger->trace("use simple hook");
            return $this->sync_cache_common_hook('find0', $where, $order, $limit, $offset, $fields);
        }
    }

    private function get_main_column_in_0($sync_keys) {
        $main_column_0 = '';
        $main_columns = $this->get_main_columns();
        foreach($sync_keys as $column => $keys) {
            if($column === $main_columns[0]) {
                $main_column_0 = $column;
                break;
            }
        }
        return $main_column_0;
    }

    /**
     * 该方法给find_count,find_sum,group_by使用
     * @param string $nake_callback eg find0...
     * @param mixed $other_params 使用 func_get_args取得
     */
    private function sync_cache_common_hook($nake_callback, $other_params) {
        if(! self::$enable_complex_cache) {
            return false;
        }

        $params = func_get_args();

        $nake_callback = array_shift($params);
        list($where) = $params;

        $sync_keys = $this->explain_where_to_key($where);
        if(empty($sync_keys)) {
            return false;
        }

        /**
         * eg where=array(loupan_id=>53241) 对应的key是
         * sync_key_prefix_Aifang_Core_DAO_Loupan_Basic_where_[{"loupan_id":53241}]
         * @var string
         */
        $where_key = $this->build_where_key($nake_callback, $params);

        //保存该where条件到redis
        $this->save_sync_keys($sync_keys, $where_key);
        return Aifang_Core_Cache_Util::get_null_and_set(array(
                $this,
                $nake_callback
        ), $params, $this->sync_data_expire_time, $where_key);
    }

    /**
     * 负责的hook，主要实现一次查询，分开缓存，类似find_by_ids
     * @param array $where
     * @param array|string $order
     * @param int $limit
     * @param int $offset
     * @param array $feilds
     */
    private function complex_hook($main_column_in_0, $sync_keys, $where) {
        $rt = array();

        /*将一次搜索有原本的一个key，变成多个key
         * eg
         *Array
         *(
         *    [AH6_BOT_AD1] => sync_key_prefix_Inform_Core_Dao_Htmlentries#100#_where_[{"status":1,"city_id":11,"name":"AH6_BOT_AD1"},"",500.1,0,[]]
         *    [AH6_L_DQ2] => sync_key_prefix_Inform_Core_Dao_Htmlentries#100#_where_[{"status":1,"city_id":11,"name":"AH6_L_DQ2"},"",500.1,0,[]]
         *)
         */
        $where_keys = $this->build_sync_where_key_complex($main_column_in_0, $where);

        //排序查询效率会高点
        ksort($where_keys);
        //find in cache


        $first_cache_rs = Aifang_Core_Cache_Util::get(array_values($where_keys));
        /*
         * 第一次找到的key
         * eg
         *Array
         *(
         *    [AH6_BOT_AD1] => sync_key_prefix_Inform_Core_Dao_Htmlentries#100#_where_[{"status":1,"city_id":11,"name":"AH6_BOT_AD1"},"",500.1,0,[]]
         *    [AH6_L_DQ2] => sync_key_prefix_Inform_Core_Dao_Htmlentries#100#_where_[{"status":1,"city_id":11,"name":"AH6_L_DQ2"},"",500.1,0,[]]
         *)
         */
        $first_cache_keys = array_keys($first_cache_rs);
        /**
         * 找到第一次没有在缓存中找到的key
         * @var array
         */
        $first_no_cache_keys = array();
        foreach($where_keys as $column_value => $where_key) {
            if(! in_array($where_key, $first_cache_keys)) {
                /**
                 * eg
                 * $column_value=>AH6_BOT_AD1
                 * $where_key=>sync_key_prefix_Inform_Core_Dao_Htmlentries#100#_where_[{......
                 * @var array
                 */
                $first_no_cache_keys[$column_value] = $where_key;
            }
        }

        //使用裸方法查找没有在cache中的值，将where重新build成where in的方式，效率高一点


        /**
         * 能够找的到的main_column_value
         * Array
         * (
         * [LY_SYGG_4] => 1
         * [LY_SYGG_5] => 1
         * )
         */
        $in_db_column_values = array();
        if(! empty($first_no_cache_keys)) {
            /**
             * 再查询一次
             * @var array
             */
            $where2 = $where;
            /**
             * main_column 方式，传递数组，使用in
             * @var unknown_type
             */
            $where2[$main_column_in_0] = array_keys($first_no_cache_keys);
            $rs = $this->find0($where2, null, 0, 0);
            if($rs) {
                //将DB的数据先保存到返回值中
                $rt = $rs;
                // 按照main_column_value 分组存放
                $rs1 = array();
                foreach($rs as $val) {
                    $in_db_column_values[$val[$main_column_in_0]] = 1;
                    $rs1[$val[$main_column_in_0]][] = $val;
                }
                //分别存放到cache中
                foreach($rs1 as $column_value => $val) {
                    Aifang_Core_Cache_Util::set($first_no_cache_keys[$column_value], $val, $this->sync_data_expire_time);
                }
            }
        }

        //合并数据，第一次是从cache中查找，第二次是在DB中查找，返回时做合并
        foreach($first_cache_rs as $key => $val) {
            foreach($val as $v) {
                $rt[] = $v;
            }
        }

        //比较$first_no_cache_keys 与 $in_db_column_values 就可以找到那些值，在DB里面查到后还是没有值
        //针对这些值，我们要缓存空值
        //处理空数据的情况，传递的ID数据库就是没有，下次也不用在查一边，所以这些数据保存成空的。
        //
        $no_rs_keys = array_diff_key($first_no_cache_keys, $in_db_column_values);
        foreach($no_rs_keys as $where_key) {
            Aifang_Core_Cache_Util::set($where_key, array());
        }
        //save sync key
        $this->save_sync_complex_keys($where_keys, $main_column_in_0);
        return $rt;
    }

    /**
     * key=>column value
     * val=>hash where key
     * @param array $where_keys
     */
    private function save_sync_complex_keys($where_keys, $main_column) {
        foreach($where_keys as $column_value => $where_key) {
            self::$keyCollection->collect($this->build_sync_key($main_column, $column_value), $where_key, Aifang_Core_Cache_SyncKeyCollection::ACTION_ADD);
        }
    }

    private function build_fields($fields) {
        $rt = "*";
        if(! empty($fields)) {
            if(is_array($fields)) {
                $rt = join(',', $fields);
            } else {
                $rt = $fields;
            }
        }
        return $rt;
    }

    private function build_order_and_limit($order, $limit, $offset) {
        $sql = "";
        if(is_array($order) && count($order)) {
            $sql .= ' ORDER BY ' . implode(',', $order);
        } else if(is_string($order) && $order) {
            $sql .= ' ORDER BY ' . $order;
        }
        if($limit > 0) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        return $sql;
    }

    private function build_where_new($where) {

        if(! is_array($where)) {
            $where = trim($where);
            return array(
                    'where' => ! empty($where) ? " WHERE $where" : "",
                    'params' => array()
            );
        }
        $in_wheres = array();
        foreach($where as $key => $val) {
            if(is_array($val)) {
                //is array mean use in,not support not in
                if(! empty($val)) {
                    $in_wheres[$key] = $val;
                } else {
                    self::$logger->error("build_where_new IN value is empty where is: ", $where);
                    throw new Aifang_Core_DaoException("build_where_new Exception IN value is empty");
                }
                unset($where[$key]);
            }
        }
        ksort($where);
        ksort($in_wheres);
        $old_where = $this->build_where($where);
        $in_sql_part = $this->build_in_sql($in_wheres);
        $rt = array();
        if(! empty($old_where) && ! empty($old_where['where'])) {
            if(! empty($in_sql_part)) {
                $old_where['where'] .= ' AND ' . $in_sql_part;
            }
            $rt = $old_where;
        } else {
            if(! empty($in_sql_part)) {
                $rt = array(
                        'where' => " WHERE $in_sql_part",
                        'params' => array()
                );
            }

        }
        return $rt;
    }

    protected function build_in_sql($in_wheres) {
        $rt = "";
        $sp = "";
        foreach($in_wheres as $key => $val) {
            if(empty($val)) {
                continue;
            }
            if($rt != "") {
                $sp = " AND ";
            }
            $val = array_unique($val);
            sort($val);
            $rt .= "$sp `$key` IN (" . Aifang_Core_Util_Tools::join_string_ids(self::escape_array($val)) . ")";
        }
        return $rt;
    }

    protected function escape_array($array) {
        foreach($array as &$val) {
            $val = self::escape_value($val);
        }
        return $array;
    }

    protected function _find_by_ids($id_array) {
        if(empty($id_array) || ! is_array($id_array)) {
            return array();
        }
        $pk = $this->get_table_pk();
        $where = array(
                $pk => $id_array
        );
        $query = $this->find0($where, array(), count($id_array));
        $rs = array();
        foreach($query as $val) {
            $rs[$val[$pk]] = $val;
        }
        return $rs;
    }

    /**
     * 将pdo返回结果集中的索引改为主键
     * @param array $array
     * @return array
     */
    public function find_assoc($where = array(), $order = null, $limit = 500.1, $offset = 0, $fields = array()) {
        $query = $this->find($where, $order, $limit, $offset, $fields);
        if(empty($query)) {
            return array();
        }
        $pk = $this->get_table_pk();
        $rs = array();
        foreach($query as $val) {
            $rs[$val[$pk]] = $val;
        }
        return $rs;
    }

    /**
     * 将pdo返回结果集中的索引改为主键
     * @param array $array
     * @return array
     */
    public function find_short_assoc($where = array(), $order = null, $limit = 500.1, $offset = 0, $fields = array()) {
        return $this->find_assoc($where, $order, $limit, $offset, $fields);
    }

    /**
     * 更新数据库表
     * @param array $data eg: array('id'=>$id)
     * @param array $where
     * @return int
     */
    public function update($data = array(), $where = array(), $status = FALSE) {

        //清除缓存一定要放到前面
        $pkid = $where[$this->get_table_pk()];
        if(! empty($pkid)) {
            $this->delete_pk_cache($pkid);
        } else {
            $this->clean_relate_pk_cache_by_where($where);
        }

        $rs = $this->_update($data, $where, $status);
        if(is_numeric($rs) && $rs > 0) {
            $this->clean_main_column_cache($where);
            $this->clean_tag_cache();
        }

        return $rs;
    }

    /**
     * update row by primary key
     * @param int $id - primary key value
     * @param arrat $data - update data eg: array('updated'=>$value)
     * @param boolean $status - update status  'field_name = field_name+1'
     * @return mixed
     */
    public function update_by_id($id, $data, $status = FALSE) {
        $rs = $this->_update($data, array(
                $this->get_table_pk() => $id
        ), $status);
        if($rs) {
            $this->clean_pk_cache($id);
            $this->clean_tag_cache();
        }

        return $rs;
    }

    /**
     * update rows by multi primary key
     * @param array $id_array
     * @param array $data
     * @return int
     */
    public function update_by_ids($id_array, $data) {
        $rs = $this->_update_by_ids($data, $id_array);
        if($id_array && $rs) {
            $this->clean_pk_cache($id_array);
            $this->clean_tag_cache();
        }
        return $rs;
    }

    /**
     * 向数据库表中插入一行数据
     *
     * @param array $data - array eg:array('column'=>$colvalue)
     * @param array $filter 需要插入DB的列名，有可能传递过来的数据会多于列名
     * @return int , if primary key not exists  return effect rows
     */
    public function insert($data, $filter = array(), $replace = false) {
        $_data = array();
        if(! empty($filter) && is_array($filter)) {
            foreach($filter as $column) {
                $_data[$column] = $data[$column];
            }
        } else {
            $_data = $data;
        }
        if(empty($_data)) {
            return false;
        }
        $rs = $this->_insert($_data, $replace);

        if(is_numeric($rs) && $rs > 0) {
            $this->clean_main_column_cache($_data);
            $this->delete_pk_cache_only($data[$this->get_table_pk()]);
            $this->clean_tag_cache();
        }
        return $rs;
    }

    /**
     * 向数据库批量插入多行数据
     * @param $data - array eg:array(array('column'=>$colvalue1),array('column'=>$colvalue2))
     * warning：每行的列结构应该相同
     */
    public function batch_insert($data, $is_ignore = false) {
        if(empty($data)) {
            return false;
        }

        $_data = $data;
        $param = array();
        $values = array();

        //过滤掉重复提交的报错
        $ignore = "";
        if($is_ignore) {
            $ignore = "ignore";
        }
        $sql = "INSERT {$ignore} INTO " . $this->get_table_name() . " (`";
        $sql .= implode('`,`', array_keys(array_pop($data))) . "`) VALUES ";
        $param = array();
        foreach($_data as $arr) {
            unset($values);
            foreach($arr as $v) {
                $values[] = "?";
                $param[] = $v;
            }
            $sql .= "(";
            $sql .= implode(",", $values) . "),";
        }
        $sql = rtrim($sql, ',');

        $rs = $this->execute_sql($sql, $param, true);

        $this->clean_tag_cache();

        $this->clean_main_column_cache($data);
        if($rs === 0) {
            //保持和之前的逻辑一样，当返回0是，替换成true
            $rs = true;
        }
        return $rs;
    }

    /**
     * 对数据库表中数据进行删除
     * @param array $where - array eg:array('column'=>$colvalue)
     * @return int $result -  effect rows
     */
    public function remove($where) {
        if(! is_array($where)) {
            return false;
        }

        try {
            $_where = $this->build_where_new($where);
        } catch (Exception $e) {
            return false;
        }

        //必需放到前面，要不然有些main column 缓存没法清除
        $pkid = $where[$this->get_table_pk()];
        if(! empty($pkid)) {
            $this->delete_pk_cache($pkid);
        } else {
            $this->clean_relate_pk_cache_by_where($where);
        }

        $sql = "delete from " . $this->get_table_name();
        $sql .= $_where['where'];
        $rs = $this->execute_sql($sql, $_where['params'], true);

        if(is_numeric($rs) && $rs > 0) {
            $this->clean_main_column_cache($where);
            $this->clean_tag_cache();
        }

        if($rs === 0) {
            //保持和之前的逻辑一样，当返回0是，替换成true
            $rs = true;
        }
        return $rs;
    }

    public function remove_by_id($id) {
        return $this->remove_by_ids($id);
    }

    public function remove_by_ids($ids) {
        if($this->get_table_pk() == self::NONE_TABLE_PK) {
            return false;
        }
        if(empty($ids)) {
            return false;
        }
        if(! is_array($ids)) {
            $ids = array(
                    $ids
            );
        }
        $ids = $this->format_id_array($ids);
        if(empty($ids)) {
            return false;
        }

        $rs = $this->remove(array(
                $this->get_table_pk() => $ids
        ));
        return $rs;
    }

    /**
     * @deprecated
     * 设置当前的 dao memcache是否开启
     * @param $boolean - true : enable memcache , false : disable memcache
     * @return null;
     */
    public function set_cache_enable($boolean) {}

    public function get_key($str) {
        return trim($this->build_key($str));
    }

    final protected function build_key_pk($id) {
        $table_name = $this->get_table_name();
        return self::MEMCACHE_KEY_PREFIX . $table_name . "-" . $this->get_tag_long_value() . "_" . $id;
    }

    final protected function build_key_pk_java($id) {
        $table_name = $this->get_table_name();
        return self::MEMCACHE_JAVA_KEY_PREFIX . $table_name . "-" . $this->get_tag_long_value() . "_" . $id;
    }

    final protected function build_key($str) {
        $table_name = $this->get_table_name();
        return self::MEMCACHE_KEY_PREFIX . $table_name . "-" . $this->get_tag_value() . "-" . $str;
    }

    protected function _fetch_by_id($id) {
        return $this->_fetch_row(array(
                $this->get_table_pk() => $id
        ));
    }

    protected function _fetch_row($where = array(), $order = null) {
        $rows = (array)$this->find0($where, $order, 1, 0);
        $rt = array_pop($rows);
        if($rt === null) {
            $rt = array();
        }
        return $rt;
    }

    protected function _fetch_count($where = array(), $field = '') {

        try {
            $_where = $this->build_where_new($where);
        } catch (Exception $e) {
            return 0;
        }

        if($field) {
            $sql = "SELECT COUNT(DISTINCT {$field}) AS total_rows FROM " . $this->get_table_name();
        } else {
            $sql = "SELECT COUNT(*) AS total_rows FROM " . $this->get_table_name();
        }
        $sql .= $_where['where'];

        $rs = $this->execute($sql, $_where['params']);

        return ! empty($rs) ? $rs[0]['total_rows'] : 0;
    }

    protected function _fetch_sum($feild, $where = array()) {
        try {
            $_where = $this->build_where_new($where);
        } catch (Exception $e) {
            return 0;
        }
        $sql = "SELECT SUM($feild) AS sum FROM " . $this->get_table_name();
        $sql .= $_where['where'];
        $rs = $this->execute_sql($sql, $_where['params']);
        return ! empty($rs) ? $rs[0]['sum'] : 0;
    }

    protected function build_where($where) {
        $_where = array(
                'where' => '',
                'params' => array(),
                'field' => array(),
        );
        if(empty($where)) {
            return $_where;
        }
        if(is_array($where) && count($where)) {
            foreach($where as $key => $value) {
                if(preg_match('/\?/', $key)) {
                    $_where['field'][] = '(' . $key . ')';
                } else {
                    $_where['field'][] = '(`' . $key . '` = ?)';
                }
                $_where['params'][] = $value;
            }
            $_where['where'] = ' WHERE ' . implode(' AND ', $_where['field']);
        } else {
            $_where['where'] = ' WHERE ' . $where;
        }
        return $_where;
    }

    protected function _update($data = array(), $where = array(), $status = FALSE) {
        $data_count = count($data);
        if($data_count < 1) {
            return false;
        }
        if(empty($where)) {
            return false;
        }
        //防止误更新
        if(is_numeric($where)) {
            return false;
        }
        $sql = "UPDATE " . $this->get_table_name() . " SET ";
        $i = 1;
        foreach($data as $key => $value) {
            $v = self::escape_value($value);
            $sql .= $status ? "`{$key}`= $v " : "`{$key}` = '{$v}'";
            $sql .= ($i < $data_count) ? ',' : '';
            $i++;
        }
        try {
            $_where = $this->build_where_new($where);
        } catch (Exception $e) {
            return false;
        }
        $sql .= $_where['where'];
        $rs = $this->execute_sql($sql, $_where['params'], true);
        if($rs === 0) {
            //保持和之前的逻辑一样，当返回0是，替换成true
            $rs = true;
        }
        return $rs;
    }

    protected function _update_by_ids($data, $id_array) {
        if(empty($id_array) || ! is_array($id_array)) {
            return 0;
        }
        $where = array(
                $this->get_table_pk() => $id_array
        );
        return $this->_update($data, $where);
    }

    protected function _insert($arr, $replace = false) {
        $param = array();
        $values = array();
        foreach($arr as $v) {
            $values[] = "?";
            $param[] = $v;
        }

        $type = $replace ? 'REPLACE' : 'INSERT';

        $sql = "$type INTO " . $this->get_table_name() . " (`";
        $sql .= implode('`,`', array_keys($arr));
        $sql .= "`) VALUES (";
        $sql .= implode(",", $values) . ")";
        $rs = $this->execute_sql($sql, $param, true);
        if($rs === 0) {
            //保持和之前的逻辑一样，当返回0是，替换成true
            $rs = true;
        }
        return $rs;
    }

    /**
     * @var Aifang_Core_Cache_Mem
     */
    private $_cache = null;

    /**
     * @return Aifang_Core_Cache_DaoMemcache
     */
    public function get_cache() {
        if($this->_cache === null) {
            apf_require_class('Aifang_Core_Cache_DaoMemcache');
            $this->_cache = new Aifang_Core_Cache_DaoMemcache();
        }
        return $this->_cache;
    }

    private $_local_cache = null;

    /**
     * @return Aifang_Core_Cache_Xcache
     */
    public function get_local_cache() {
        if($this->_local_cache == null) {
            apf_require_class('Aifang_Core_Cache_Xcache');
            $this->_local_cache = new Aifang_Core_Cache_Xcache();
        }
        return $this->_local_cache;
    }

    /**
     * @var Aifang_Core_Cache_Tag
     */
    private $_tag = null;

    /**
     * @return Aifang_Core_Cache_Tag
     */
    protected function get_tag() {
        if(null === $this->_tag) {
            apf_require_class('Aifang_Core_Cache_Tag');
            $this->_tag = Aifang_Core_Cache_Tag::get_instance();
        }
        return $this->_tag;
    }

    /**
     * @var APF_DB_Factory
     */
    private $_dbfactory = null;

    /**
     * 获取一个 pdo 实例
     * @param $write boolean - true :master db connection false: slave db connection
     * @return APF_DB_PDO
     */
    protected function get_pdo($write = false) {
        if(null === $this->_dbfactory) {
            $this->_dbfactory = APF_DB_Factory::get_instance();
        }
        if($write) {
            return $this->_dbfactory->get_pdo($this->get_write_pdo_name());
        } else {
            if($this->read_from_master) {
                return $this->_dbfactory->get_pdo($this->get_write_pdo_name());
            }
        }
        return $this->_dbfactory->get_pdo($this->get_read_pdo_name());
    }

    public static function format_to_string($data) {
        if(is_array($data)) {
            $str = "";
            foreach($data as $key => $value) {
                $str .= $key . "," . $value;
            }
            return $str;
        }
        return $data;
    }

    /**
     * 执行SQL语句
     *
     * @param string $sql SQL语句
     * @param array $params SQL绑定参数
     * @param boolean $write 数据库主机选择
     * @return mixed
     */
    public function execute($sql, $params = array(), $write = false) {
        $rs = $this->execute_sql($sql, $params, $write);
        if(! is_array($rs) && $rs > 0) {
            $this->clean_tag_cache();
        }
        return $rs;
    }

    private function build_traced_sql($sql) {
        $request_session_id = '';
        if(defined('REQUEST_SESSION_ID')) {
            $request_session_id = REQUEST_SESSION_ID;
        }
        return $sql . " # php " . APP_NAME . ' : ' . $request_session_id . ' : ' . date('Y-m-d H-i-s') . ' end;';
    }

    protected function execute_sql($sql, $params = array(), $write = false) {
        $sql = ltrim($sql);
        $sql = $this->build_traced_sql($sql);
        $pdo = $this->get_pdo($write);

        if(! self::$enable_final_sql) {
            self::$logger->debug("[execute sql] $sql");
        }

        $stmt = $pdo->prepare($sql);
        if(! $stmt->execute((array)$params)) {
            self::$logger->debug($stmt->errorInfo());
            return false;
        }

        $type = $this->get_type($sql);
        Aifang_Core_DaoStat::inc_table_stats($this->get_table_name(), $type);

        $result = null;
        switch($type) {
            case 'INSERT' :
            case 'REPLACE' :
                $result = $pdo->lastInsertId();
                if(! $result) {
                    $result = $stmt->rowCount();
                }
                break;

            case 'UPDATE' :
            case 'DELETE' :
                $result = $stmt->rowCount();
                break;

            case 'SELECT' :
                $result = $stmt->fetchAll();
                break;

            default :
                break;
        }

        if(self::$enable_final_sql) {
            self::$logger->info("[execute sql] " . $stmt->getFinalSql());
        }

        Aifang_Core_Util_Alarm::getInstance()->alarm_job_message();
        return $result;
    }

    private function get_type($sql) {
        $type = substr($sql, 0, strpos($sql, " "));
        return trim(strtoupper($type));
    }

    /**
     * 格式化ID数组，确保数组中全部是数字
     * @param $id_array
     */
    public function format_id_array($id_array) {
        foreach($id_array as $key => $val) {
            $val = intval($val);
            if($val <= 0) {
                unset($id_array[$key]);
            } else {
                $id_array[$key] = $val;
            }
        }
        return array_unique($id_array);
    }

    public static function escape_like($value) {
        if(is_numeric($value) || is_string($value)) {
            $value = self::escape_value($value);
            return str_replace(array(
                    '_',
                    '%'
            ), array(
                    '\_',
                    '\%'
            ), $value);
        } else {
            return $value;
        }
    }

    public static function escape_value($value) {
        if(is_numeric($value) || is_string($value)) {
            return mysql_escape_string($value);
        } else {
            return $value;
        }
    }

    private function delete_pk_cache_only($ids) {
        if(empty($ids)) {
            return;
        }
        //删除cache
        if(! $this->get_table_pk() || $this->get_table_pk() == self::NONE_TABLE_PK) {
            return;
        }
        if(! is_array($ids)) {
            $ids = array(
                    $ids
            );
        }

        foreach($ids as $id) {
            if(! $id) {
                continue;
            }
            Aifang_Core_Cache_Util::delete($this->build_key_pk($id));
            Aifang_Core_Cache_Util::delete($this->build_key_pk_java($id));
        }
    }

    /**
     * 适用于先按照某个主键查找，如果存在就更新，否则进行插入
     * @param array $data
     * @param array $filter
     * @throws Exception
     * @return boolean
     */
    public function find_by_id_update_insert($data = array(), $filter = array()) {
        if($this->get_table_pk() == self::NONE_TABLE_PK) {
            throw new Exception("find_update_insert in no pk");
        }

        if(empty($data) || ! is_array($data)) {
            throw new Exception("find_update_insert data is empty or is not a array");
        }

        $id = $data[$this->get_table_pk()];
        $rs = $this->find_by_id($id);
        if(empty($rs)) {
            //insert
            $rt = $this->insert($data, $filter);
        } else {
            //update
            $rt = $this->update_by_id($id, $data);
        }
        return $rt;
    }

    /**
     * 解析where条件，用于同步缓存
     * @param array $where
     * @return array
     */
    private function explain_where_to_key($where) {
        $main_columns = $this->get_main_columns();
        if(empty($main_columns)) {
            return array();
        }

        $rt = array();
        foreach($main_columns as $column) {
            if(! empty($where[$column]) && (is_string($where[$column]) || is_numeric($where[$column]) || is_array($where[$column]))) {
                if(is_array($where[$column])) {
                    foreach($where[$column] as $val) {
                        $rt[$column][$val] = $this->build_sync_key($column, $val);
                    }
                } else {
                    $rt[$column][$where[$column]] = $this->build_sync_key($column, $where[$column]);
                }
            }
        }
        return $rt;
    }

    private function build_sync_key($column_name, $value) {
        $key_prefix = $this->build_sync_key_prefix();
        $key_version = $this->get_tag_long_value();
        return "{$key_prefix}_{$column_name}_{$value}_{$key_version}";
    }

    /**
     * 将find方法所使用到的key，保存到redis或类似的存储上面
     */
    private function save_sync_keys($sync_keys, $where_key) {
        foreach($sync_keys as $keys) {
            foreach($keys as $key) {
                self::$keyCollection->collect($key, $where_key, Aifang_Core_Cache_SyncKeyCollection::ACTION_ADD);
            }
        }
    }

    private function build_sync_key_prefix() {
        return "sync_key_prefix_" . get_class($this);
    }

    /**
     *同步方法的key是区别之前的find_by_id 和 find_by_where,因为可以做到
     *永久缓存
     *@param $call_func eg: find,find_sum,find_count ...
     */
    private function build_where_key($call_func = 'find', $other_params = '') {
        $args = func_get_args();
        if(empty($args)) {
            return '';
        }

        $key_prefix = $this->build_sync_key_prefix() . "#$call_func#" . $this->get_tag_long_value() . '#' . "_where_";
        $key = json_encode($args);

        return $this->sub_key($key_prefix . $key);
    }

    private function sub_key($key) {
        if(strlen($key) > 250) {
            $key = substr($key, 0, 210) . md5(substr($key, 210));
        }
        return $key;
    }

    /**
     * 将一个查询，根据IN的字段，分割成多个where，一次查询，多分cache
     */
    private function build_sync_where_key_complex($main_column_in_0, $where) {
        $in_where_column_value = $where[$main_column_in_0];
        unset($where[$main_column_in_0]);

        $rt = array();
        foreach($in_where_column_value as $val) {
            $where[$main_column_in_0] = $val;
            $rt[$val] = $this->build_where_key('find0', $where, '', $this->default_limit, 0, array());
        }
        return $rt;
    }

    /**
     * 该表的的主要列,用于同步缓存
     * eg array(
     * 'loupan_id',
     * 'comm_id'
     * )
     */
    public function get_main_columns() {
        return $this->main_columns;
    }

    public function set_main_columns($main_columns) {
        $this->main_columns = $main_columns;
    }

    /**
     * 使用$aifang_debugger替换
     * @deprecated
     */
    public function debug() {

    }

    /**
     * 在dao中自动处理
     * @deprecated
     */
    public function log_error($stmt, $sql, $data = array()) {

    }

    /**
     * @deprecated
     */
    protected function _fetch_all_short($where = array(), $order = null, $limit = 500.1, $offset = 0, $field = array()) {
        return $this->find0($where, $order, $limit, $offset, $field);
    }

    /**
     * @deprecated
     */
    protected function _fetch_all($where = array(), $order = null, $limit = 500.1, $offset = 0) {
        return $this->find0($where, $order, $limit, $offset);
    }

    /**
     * 删除cache_tag 相关的缓存
     */
    protected function clean_tag_cache() {
        $this->get_tag()->update_tag_local($this->get_table_name());
    }

    /**
     * 删除cache_tag 相关的缓存
     */
    protected function clean_tag_long_cache() {
        $this->get_tag()->update_long_tag_local($this->get_table_name());
    }

    /**
     * 删除主键相关的缓存
     */
    public function clean_pk_cache($pkids) {
        return $this->delete_pk_cache($pkids);
    }

    public function delete_pk_cache($ids) {
        $this->delete_relative_pk_cache_by_pkids($ids);
        $this->delete_pk_cache_only($ids);
    }

    /**
     * 删除main column 相关的缓存
     * @param array $where
     */
    public function clean_main_column_cache($where) {

        if(! is_array($where)) {
            return false;
        }
        /**
         * eg
         * Array
         *(
         * [name] => Array
         * (
         * [AH6_L_DQ2] => sync_key_prefix_Inform_Core_Dao_Htmlentries_name_AH6_L_DQ2
         * )
         *
         *)
         * @var array
         */
        $sync_keys = $this->explain_where_to_key($where);

        if(empty($sync_keys)) {
            return false;
        }
        //根据这些key，去redis搜索找出对应的where key并且删除对应的memcache值


        /**
         * Array
         *(
         * [0] => Array
         * (
         * [AH6_L_DQ2] => sync_key_prefix_Inform_Core_Dao_Htmlentries_name_AH6_L_DQ2
         * )
         *
         *)
         **/
        $redis_sync_keys = array_values($sync_keys);

        foreach($redis_sync_keys as $keys) {

            /**
             * eg
             * 这里返回的结果是
             *Array
             *(
             * [AH6_L_DQ2] => sync_key_prefix_Inform_Core_Dao_Htmlentries_name_AH6_L_DQ2
             *)
             * @var array
             */
            foreach($keys as $key) {
                /**
                 * eg
                 * $key=>sync_key_prefix_Inform_Core_Dao_Htmlentries_name_AH6_L_DQ2
                 */
                self::$keyCollection->collect($key, "", Aifang_Core_Cache_SyncKeyCollection::ACTION_DEL);
            }
        }
    }

    /**
     * 根据主键更新或删除的时候，需要删除其相关的maincolumn缓存
     */
    private function delete_relative_pk_cache_by_pkids($ids) {
        if(empty($ids)) {
            return;
        }
        if(empty($this->main_columns)) {
            return;
        }
        self::$logger->debug("------------------ delete_relative_cache_with_pk");
        if(! is_array($ids)) {
            $ids = array(
                    $ids
            );
        }
        $rs = $this->find_by_ids($ids);

        if(empty($rs)) {
            return;
        }
        $main_values = array();

        foreach($rs as $val) {
            foreach($this->main_columns as $col) {
                if(isset($main_values[$col])) {
                    if(! in_array($val[$col], $main_values[$col])) {
                        $main_values[$col][] = $val[$col];
                    }
                } else {
                    $main_values[$col][] = $val[$col];
                }
            }
        }

        //first support in
        $first_col = $this->main_columns[0];
        if(! empty($main_values[$first_col])) {
            $this->clean_main_column_cache(array(
                    $first_col => $main_values[$first_col]
            ));
            unset($main_values[$first_col]);
        }

        //other not support in
        if(! empty($main_values)) {
            foreach($main_values as $col => $values) {
                foreach($values as $val) {
                    $this->clean_main_column_cache(array(
                            $col => $val
                    ));
                }
            }
        }
    }

    /**
     * 根据where条件删除对应的主键缓存
     * @param array $where
     */
    private function clean_relate_pk_cache_by_where($where) {
        if(! is_array($where) || empty($where) || self::NONE_TABLE_PK == $this->get_table_pk()) {
            return;
        }

        //根据条件查询出主键iD
        $rs = $this->find_assoc($where, null, $this->threshold_for_delete_pk_by_where, 0, array(
                $this->get_table_pk()
        ));

        if(count($rs) == $this->threshold_for_delete_pk_by_where) {
            //这个条件关联的主键太多了，这个时候放弃，直接整表过期pk和main_column缓存
            $this->clean_tag_long_cache();
            self::$logger->error("UPDATE too many rows, update tag_long table is ", $this->get_table_name(), "where: ", $where);
        } else {
            $this->delete_pk_cache_only(array_keys($rs));
        }
    }

    private function get_tag_value() {
        return $this->get_tag()->get($this->get_table_name(), Aifang_Core_Cache_Tag::UPDATE_INDEX);
    }

    private function get_tag_long_value() {
        return $this->get_tag()->get($this->get_table_name(), Aifang_Core_Cache_Tag::UPDATE_LONG_INDEX);
    }
}
