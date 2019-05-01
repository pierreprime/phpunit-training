<?php

use PHPUnit\Framework\TestCase;

class FakeCacheAdapter implements \App\CacheAdapterInterface{
    public function get($key)
    {
    }

    public function set($key, $value)
    {
    }
}

class FakeModel{

    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function cache_key(){
        return $this->key;
    }
}

class FragmentCachingTest extends TestCase
{
    public function testConstructorInterface(){
        $this->expectException(TypeError::class);
        new App\FragmentCaching(new stdClass());
    }

    public function fakeCacheAdapterInterface(){
        new App\FragmentCaching(new FakeCacheAdapter());
    }

    public function testCacheWithCache(){

        $cacheAdapter = $this->getMockBuilder(FakeCacheAdapter::class)
            ->setMethods(['get'])
            ->getMock();

        $cacheAdapter->method('get')->willReturn('en cache');

        $cache = new \App\FragmentCaching($cacheAdapter);
        $this->expectOutputString('en cache');

        $cache->cache('test', function (){
            echo "hello";
        });
    }

    public function testCacheWithoutCache(){
        $cacheAdapter = $this->getMockBuilder(FakeCacheAdapter::class)
            ->setMethods(['get'])
            ->getMock();

        $cacheAdapter->method('get')->willReturn(false);

        $cache = new \App\FragmentCaching($cacheAdapter);
        $this->expectOutputString('salut');
        $cache->cache('test', function (){ echo "salut"; });
    }

//    public function testCacheWithoutCacheSetCache(){
//        $cacheAdapter = $this->getMockBuilder(FakeCacheAdapter::class)
//            ->setMethods(['get', 'set'])
//            ->getMock();
//
//        $cacheAdapter->method('get')->willReturn(false);
//        $cacheAdapter->expects($this->never())->method('set')->with('test', 'salut');
//
//        $cache = new \App\FragmentCaching($cacheAdapter);
//        $cache->cache('test', function (){ echo "salut"; });
//    }

    public function testKeyWithArray(){
        $cache = $this->getInstanceWithExpectedGet('test-je-suis');
        $cache->cache(['test', 'je', 'suis'], function (){ return false; });
    }

    public function testKeyWithString(){
        $cache = $this->getInstanceWithExpectedGet('test');
        $cache->cache('test', function (){
            return false;
        });
    }

    public function testKeyWithArrayWithBoolean(){
        $cache = $this->getInstanceWithExpectedGet('test-0-suis');
        $cache->cache(['test', false, 'suis'], function (){
            return false;
        });
    }

    public function testKeyWithArrayWithObject(){
        $fake = new FakeModel('model');
        $cache = $this->getInstanceWithExpectedGet('test-model-suis');
        $cache->cache(['test', $fake, 'suis'], function (){
            return false;
        });
    }

    public function getInstanceWithExpectedGet($value){
        $cacheAdapter = $this->getMockBuilder(FakeCacheAdapter::class)
            ->setMethods(['get'])
            ->getMock();

        $cacheAdapter->expects($this->once())->method('get')->with($value);
        $cache = new App\FragmentCaching($cacheAdapter);
        return $cache;
    }

    public function testCacheIfWithFalseCondition(){

        $cache = $this->getMockBuilder(\App\FragmentCaching::class)
            ->setConstructorArgs([new FakeCacheAdapter()])
            ->setMethods(['cache'])
            ->getMock();

        $cache->expects($this->never())->method('cache');
        $this->expectOutputString('salut');

        $cache->cacheIf(false, 'key', function (){
            echo 'salut';
        });
    }

    public function testCacheIfWithTrueCondition(){

        $cache = $this->getMockBuilder(\App\FragmentCaching::class)
            ->setConstructorArgs([new FakeCacheAdapter()])
            ->setMethods(['cache'])
            ->getMock();

        $cache->expects($this->once())->method('cache');
        $cache->cacheIf(true, 'key', function (){
            echo 'salut';
        });
    }
}