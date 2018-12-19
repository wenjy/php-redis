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

    const TEST_REDIS_KEY_PRE = 'test_redis_key_';

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

    protected function generateKey($suffix = null)
    {
        $key = self::TEST_REDIS_KEY_PRE . ($suffix ? $suffix : uniqid());
        $this->testRedisKeyList[] = $key;
        return $key;
    }
}
