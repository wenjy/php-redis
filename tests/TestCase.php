<?php

/**
 * @author: jiangyi
 * @date: 下午5:17 2018/12/14
 *
 * Redis server v=5.0.3
 */

namespace EasyRedis\Tests;

use EasyRedis\Connection;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * @var Connection
     */
    protected $redis;

    /**
     * @var array all keys
     */
    protected $testRedisKeyList = [];

    public function setUp()
    {
        if ($this->redis === null) {
            $this->redis = new Connection();
        }
    }

    public function tearDown()
    {
        while ($this->testRedisKeyList) {
            $key = array_pop($this->testRedisKeyList);
            $this->redis->del($key);
        }
        $this->redis->close();
    }

    protected function generateKey()
    {
        $key = 'test_redis_key_' . uniqid();
        $this->testRedisKeyList[] = $key;
        return $key;
    }
}
