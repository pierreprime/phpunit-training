<?php

namespace App;


class FragmentCaching{

    /**
     * @var CacheAdapterInterface
     */
    private $cache;

    public function __construct(CacheAdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    private function hashKeys($keys){
        if(is_array($keys)){
            $return = [];
            foreach ($keys as $key){
                array_push($return, $this->hashKey($key));
            }
            return implode('-', $return);
        }
        else {
            return $keys;
        }
    }

    private function hashKey($key){
        if(is_bool($key)){
            return $key ? "1" : "0";
        }
        else if(is_object($key)){
            return $key->cache_key();
        }
        else{
            return $key;
        }
    }

    public function cache($key, Callable $callback){
        $key = $this->hashKeys($key);
        $value = $this->cache->get($key);
        if($value){
            echo $value;
        }
        else {
            ob_start();
            $callback();
            $value = ob_get_clean();
            $this->cache->set($key, $value);
            echo $value;
        }
    }

    public function cacheIf($condition, $key, Callable $callback){
        if($condition == false){
            $callback();
        }
        else {
            $this->cache($key, $callback);
        }
    }
}