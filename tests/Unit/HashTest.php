<?php
/**
 * @author: jiangyi
 * @date: 上午11:56 2018/12/18
 */

namespace EasyRedis\tests\Unit;

use EasyRedis\Tests\TestCase;

class HashTest extends TestCase
{
    /**
     * 将哈希表 key 中的域 field 的值设为 value
     * 如果 field 是哈希表中的一个新建域，并且值设置成功，返回 1 。
     * 如果哈希表中域field已经存在且旧值已被新值覆盖，返回 0
     */
    public function testSet()
    {
        $key = $this->generateKey();

        $res = $this->redis->hset($key, 'key1', 'val1');
        $this->assertEquals(1, $res);

        $res = $this->redis->hset($key, 'key1', 'val2');
        $this->assertEquals(0, $res);
    }

    /**
     * 同时将多个 field-value (域-值)对设置到哈希表 key 中
     */
    public function testMSet()
    {
        $key = $this->generateKey();
        $res = $this->redis->hmset($key, 'key1', 'val1', 'key2', 'val2');
        $this->assertEquals(2, $res);
    }

    /**
     * 将哈希表 key 中的域 field 的值设置为 value ，当且仅当域 field 不存在
     */
    public function testSetNX()
    {
        $key = $this->generateKey();
        $res = $this->redis->hsetnx($key, 'key1', 'val1');
        $this->assertEquals(1, $res);

        $res = $this->redis->hsetnx($key, 'key1', 'val1');
        $this->assertEquals(0, $res);
    }

    /**
     * 返回哈希表 key 中给定域 field 的值
     * 给定域的值。当给定域不存在或是给定 key 不存在时，返回 nil
     */
    public function testGet()
    {
        $key = $this->generateKey();
        $this->redis->hset($key, 'key1', 'val1');
        $member = $this->redis->hget($key, 'key1');
        $this->assertEquals('val1', $member);

        $member = $this->redis->hget($key, 'key2');
        $this->assertNull($member);

        $member = $this->redis->hget($this->generateKey(), 'key1');
        $this->assertNull($member);
    }

    /**
     * 一个包含多个给定域的关联值的表，表值的排列顺序和给定域参数的请求顺序一样
     */
    public function testMGet()
    {
        $key = $this->generateKey();
        $this->redis->hset($key, 'key1', 'val1');
        $this->redis->hset($key, 'key2', 'val2');

        $members = $this->redis->hmget($key, 'key1', 'key2');
        $this->assertArraySubset(['val1', 'val2'], $members);

        $members = $this->redis->hmget($this->generateKey(), 'key1');
        $this->assertArraySubset([], $members);
    }

    /**
     * 以列表形式返回哈希表的域和域的值。若 key 不存在，返回空列表
     */
    public function testGetAll()
    {
        $key = $this->generateKey();
        $this->redis->hset($key, 'key1', 'val1');

        $members = $this->redis->hgetall($key);
        $this->assertArraySubset(['key1', 'val1'], $members);

        $members = $this->redis->hgetall($this->generateKey());
        $this->assertArraySubset([], $members);
    }

    /**
     * 一个包含哈希表中所有值的表。当 key 不存在时，返回一个空表
     */
    public function testVals()
    {
        $key = $this->generateKey();
        $this->redis->hmset($key, 'key1', 'val1', 'key2', 'val2');

        $members = $this->redis->hvals($key);
        $this->assertArraySubset(['val1', 'val2'], $members);

        $members = $this->redis->hvals($this->generateKey());
        $this->assertArraySubset([], $members);
    }

    /**
     * 删除哈希表 key 中的一个或多个指定域，不存在的域将被忽略
     * 返回被成功移除的域的数量
     */
    public function testDel()
    {
        $key = $this->generateKey();
        $this->redis->hset($key, 'key1', 'val1');

        $number = $this->redis->del($key, $this->generateKey());
        $this->assertEquals(1, $number);
    }

    /**
     * 查看哈希表 key 中，给定域 field 是否存在
     */
    public function testExists()
    {
        $key = $this->generateKey();
        $this->redis->hset($key, 'key1', 'val1');

        $res = $this->redis->hexists($key, 'key1');
        $this->assertEquals(1, $res);

        $res = $this->redis->hexists($key, 'key22');
        $this->assertEquals(0, $res);
    }

    /**
     * 为哈希表 key 中的域 field 的值加上增量 increment
     * 返回执行 HINCRBY 命令之后，哈希表 key 中域 field 的值
     */
    public function testIncrBy()
    {
        $key = $this->generateKey();
        $this->redis->hset($key, 'key1', 1);

        $member = $this->redis->hincrby($key, 'key1', 3);
        $this->assertEquals(4, $member);

        $key1 = $this->generateKey();
        $member = $this->redis->hincrby($key1, 'key1', 3);
        $this->assertEquals(3, $member);
    }

    /**
     * 为哈希表 key 中的域 field 的值加上浮点数增量 increment
     * 返回执行 HINCRBYFLOAT 命令之后，哈希表 key 中域 field 的值
     */
    public function testIncrByFloat()
    {
        $key = $this->generateKey();
        $this->redis->hset($key, 'key1', 1.0);

        $member = $this->redis->hincrbyfloat($key, 'key1', 3.1);
        $this->assertEquals(4.1, $member);

        $key1 = $this->generateKey();
        $member = $this->redis->hincrbyfloat($key1, 'key1', 3.1);
        $this->assertEquals(3.1, $member);
    }

    /**
     * 返回哈希表 key 中的所有域
     */
    public function testKeys()
    {
        $key = $this->generateKey();
        $this->redis->hset($key, 'key1', 'val1');

        $members = $this->redis->hkeys($key);
        $this->assertArraySubset(['key1'], $members);

        $members = $this->redis->hkeys($this->generateKey());
        $this->assertArraySubset([], $members);
    }

    /**
     * 哈希表中域的数量。当 key 不存在时，返回 0
     */
    public function testLen()
    {
        $key = $this->generateKey();
        $this->redis->hset($key, 'key1', 'val1');

        $number = $this->redis->hlen($key);
        $this->assertEquals(1, $number);

        $number = $this->redis->hlen($this->generateKey());
        $this->assertEquals(0, $number);
    }

    /**
     * 返回哈希表 key 中， 与给定域 field 相关联的值的字符串长度（string length）
     */
    public function testStrLen()
    {
        $key = $this->generateKey();
        $this->redis->hset($key, 'key1', 'val1');

        $length = $this->redis->hstrlen($key, 'key1');
        $this->assertEquals(4, $length);

        $this->redis->hset($key, 'key2', 'val2中文');
        $length = $this->redis->hstrlen($key, 'key2');
        $this->assertEquals(10, $length);
    }
}
