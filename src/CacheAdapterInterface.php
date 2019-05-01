<?php

namespace App;

interface CacheAdapterInterface{
    public function get($key);
    public function set($key, $value);
}