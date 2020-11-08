<?php
/**
 *  Redis锁操作类
 *  Date:   2016-06-30
 *  Author: fdipzone
 *  Ver:    1.0
 *
 *  Func:
 *  public  lock    获取锁
 *  public  unlock  释放锁
 *  private connect 连接
 */
class redisLock {

    private $_config =array(
             'host' => '127.0.0.1',
             'port' => 6379,
             'index' => 0,
             'auth' => '',
             'timeout' => 1,
             'reserved' => NULL,
             'retry_interval' => 100,
        );
    private $_redis;

    /**
     * 初始化
     * @param Array $config redis连接设定
     */
    public function __construct(){
        $this->_redis = $this->connect();
    }

    /**
     * 获取锁
     * @param  String  $key    锁标识
     * @param  Int     $expire 锁过期时间
     * @return Boolean  返回true说明当前程序获得锁成功,可以继续执行后面的程序
     * 备注: 
     *  1.SETNX key value  
     *  2. 将 key 的值设为 value ，当且仅当 key 不存在。
     *  3. 若给定的 key 已经存在，则 SETNX 不做任何动作。
     *  4. 设置成功，返回 1 。设置失败，返回 0 。
     * 
     */
    public function lock($key, $expire=10){
        $is_lock = $this->_redis->setnx($key, time()+$expire);
        // 不能获取锁,说明锁已存在
        if(!$is_lock){
            // 获取当前key存储的值，判断锁是否过期
            $lock_time = $this->_redis->get($key);
            // 锁已过期，删除锁，重新设置并获取
            if(time()>$lock_time){
                $this->unlock($key);
                $is_lock = $this->_redis->setnx($key, time()+$expire);
            }
        }
        return $is_lock ? true : false;
    }

    /**
     * 释放锁
     * @param  String  $key 锁标识
     * @return Boolean
     */
    public function unlock($key){
        return $this->_redis->del($key);
    }

    /**
     * 创建redis连接
     * @return Link
     */
    private function connect(){
        try{
            $redis = new Redis();

            $redis->connect($this->_config['host'],$this->_config['port'],$this->_config['timeout'],$this->_config['reserved'],$this->_config['retry_interval']);
            if(empty($this->_config['auth'])){
                $redis->auth($this->_config['auth']);
            }
            $redis->select($this->_config['index']);
        }catch(RedisException $e){
            throw new Exception($e->getMessage());
            return false;
        }
        return $redis;
    }

}