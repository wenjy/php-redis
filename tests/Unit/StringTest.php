<?php
/**
 * @author: jiangyi
 * @date: 下午9:45 2018/12/18
 */

namespace EasyRedis\tests\Unit;

use EasyRedis\Tests\TestCase;

class StringTest extends TestCase
{
    /**
     * 如果 key 已经存在并且是一个字符串， APPEND 命令将 value 追加到 key 原来的值的末尾。
     * 如果 key 不存在， APPEND 就简单地将给定 key 设为 value ，就像执行 SET key value 一样
     * 返回追加 value 之后， key 中字符串的长度
     */
    public function testAppend()
    {
        $key = $this->generateKey();

        $length = $this->redis->append($key, 'abc');
        $this->assertEquals(3, $length);

        $length = $this->redis->append($key, 'def');
        $this->assertEquals(6, $length);
    }

    /**
     * 对 key 所储存的字符串值，设置或清除指定偏移量上的位(bit)
     */
    public function testSetBit()
    {
        $key = $this->generateKey();
        $res = $this->redis->setbit($key, 8, 1);
        $this->assertEquals(0, $res);
    }

    /**
     * 对 key 所储存的字符串值，获取指定偏移量上的位(bit)
     */
    public function testGetBit()
    {
        $key = $this->generateKey();
        $bit = $this->redis->getbit($key, 8);
        $this->assertEquals(0, $bit);

        $this->redis->setbit($key, 8, 1);
        $bit = $this->redis->getbit($key, 8);
        $this->assertEquals(1, $bit);
    }

    /**
     * 计算给定字符串中，被设置为 1 的比特位的数量
     */
    public function testBitCount()
    {
        $key = $this->generateKey();
        $this->redis->setbit($key, 8, 1);

        $number = $this->redis->bitcount($key, 0, -1);
        $this->assertEquals(1, $number);

        $this->redis->setbit($key, 7, 1);
        $number = $this->redis->bitcount($key, 0, -1);
        $this->assertEquals(2, $number);
    }

    /**
     * 对一个或多个保存二进制位的字符串 key 进行位元操作，并将结果保存到 destkey 上
     * operation 可以是 AND 、 OR 、 NOT 、 XOR 这四种操作中的任意一种
     */
    public function testBitOP()
    {
        $key = $this->generateKey();
        // 1001
        $this->redis->setbit($key, 0, 1);
        $this->redis->setbit($key, 3, 1);

        $key2 = $this->generateKey();
        // 1101
        $this->redis->setbit($key2, 0, 1);
        $this->redis->setbit($key2, 1, 1);
        $this->redis->setbit($key2, 3, 1);

        $key3 = $this->generateKey();
        // 1001
        $res = $this->redis->bitop('AND', $key3, $key, $key2);
        $this->assertEquals(1, $res);
        $res = $this->redis->getbit($key3, 0);
        $this->assertEquals(1, $res);
        $res = $this->redis->getbit($key3, 1);
        $this->assertEquals(0, $res);
        $res = $this->redis->getbit($key3, 2);
        $this->assertEquals(0, $res);
        $res = $this->redis->getbit($key3, 3);
        $this->assertEquals(1, $res);
    }

    public function testBitOPA()
    {
        $key = $this->generateKey();
        // 1001
        $this->redis->setbit($key, 0, 1);
        $this->redis->setbit($key, 3, 1);

        $key2 = $this->generateKey();
        // 1101
        $this->redis->setbit($key2, 0, 1);
        $this->redis->setbit($key2, 1, 1);
        $this->redis->setbit($key2, 3, 1);

        $key3 = $this->generateKey();
        // 1001
        $res = $this->redis->bitop_a('AND', $key3, [$key, $key2]);
        $this->assertEquals(1, $res);
        $res = $this->redis->getbit($key3, 0);
        $this->assertEquals(1, $res);
        $res = $this->redis->getbit($key3, 1);
        $this->assertEquals(0, $res);
        $res = $this->redis->getbit($key3, 2);
        $this->assertEquals(0, $res);
        $res = $this->redis->getbit($key3, 3);
        $this->assertEquals(1, $res);
    }

    /**
     * BITFIELD 命令可以将一个 Redis 字符串看作是一个由二进制位组成的数组，
     * 并对这个数组中储存的长度不同的整数进行访问 （被储存的整数无需进行对齐）
     * GET <type> <offset> —— 返回指定的二进制位范围
     * SET <type> <offset> <value> —— 对指定的二进制位范围进行设置，并返回它的旧值
     * INCRBY <type> <offset> <increment> —— 对指定的二进制位范围执行加法操作，并返回它的旧值
     * OVERFLOW [WRAP|SAT|FAIL] 它可以改变之后执行的 INCRBY 子命令在发生溢出情况时的行为
     * 用户可以在类型参数的前面添加 i 来表示有符号整数， 或者使用 u 来表示无符号整数
     */
    public function testBitField()
    {
        $key = $this->generateKey();
        // 对位于偏移量 100 的 8 位长有符号整数执行加法操作， 并获取位于偏移量 0 上的 4 位长无符号整数
        $res = $this->redis->bitfield($key, 'INCRBY', 'i8', 100, 1, 'GET', 'u4', 0);
        $this->assertArraySubset([1, 0], $res);

        $key2 = $this->generateKey();

        $res = $this->redis->bitfield($key2, 'incrby', 'u2', 100, 1, 'OVERFLOW', 'SAT', 'incrby', 'u2', 102, 1);
        $this->assertArraySubset([1, 1], $res);
        $res = $this->redis->bitfield($key2, 'incrby', 'u2', 100, 1, 'OVERFLOW', 'SAT', 'incrby', 'u2', 102, 1);
        $this->assertArraySubset([2, 2], $res);
        $res = $this->redis->bitfield($key2, 'incrby', 'u2', 100, 1, 'OVERFLOW', 'SAT', 'incrby', 'u2', 102, 1);
        $this->assertArraySubset([3, 3], $res);
        $res = $this->redis->bitfield($key2, 'incrby', 'u2', 100, 1, 'OVERFLOW', 'SAT', 'incrby', 'u2', 102, 1);
        $this->assertArraySubset([0, 3], $res);
    }

    /**
     * 将 key 中储存的数字值减一
     * 返回执行 DECR 命令之后 key 的值
     */
    public function testDecr()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 10);

        $number = $this->redis->decr($key);
        $this->assertEquals(9, $number);

        $number = $this->redis->decr($this->generateKey());
        $this->assertEquals(-1, $number);
    }

    /**
     * 将 key 所储存的值减去减量 decrement
     * 返回减去 decrement 之后， key 的值
     */
    public function tetDecrBy()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 10);

        $number = $this->redis->decrby($key, 2);
        $this->assertEquals(8, $number);

        $number = $this->redis->decrby($this->generateKey(), 2);
        $this->assertEquals(-2, $number);
    }

    /**
     * 返回 key 所关联的字符串值
     */
    public function testGet()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 10);

        $number = $this->redis->get($key);
        $this->assertEquals(10, $number);

        $number = $this->redis->get($this->generateKey());
        $this->assertNull($number);
    }

    /**
     * 返回 key 中字符串值的子字符串，字符串的截取范围由 start 和 end 两个偏移量决定(包括 start 和 end 在内)
     */
    public function testGetRange()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello world');

        $string = $this->redis->getrange($key, 0, 4);
        $this->assertEquals('hello', $string);

        $string = $this->redis->getrange($key, -1, -5);
        $this->assertEquals('', $string);

        $string = $this->redis->getrange($key, -5, -1);
        $this->assertEquals('world', $string);

        $string = $this->redis->getrange($key, 0, -1);
        $this->assertEquals('hello world', $string);

        $string = $this->redis->getrange($key, 0, 20);
        $this->assertEquals('hello world', $string);
    }

    /**
     * 将给定 key 的值设为 value ，并返回 key 的旧值(old value)
     */
    public function testGetSet()
    {
        $key = $this->generateKey();
        $string = $this->redis->getset($key, 'val');
        $this->assertNull($string);

        $string = $this->redis->getset($key, 'val2');
        $this->assertEquals('val', $string);
    }

    /**
     * 将 key 中储存的数字值增一
     */
    public function testIncr()
    {
        $key = $this->generateKey();
        $number = $this->redis->incr($key);
        $this->assertEquals(1, $number);

        $number = $this->redis->incr($key);
        $this->assertEquals(2, $number);
    }

    /**
     * 将 key 所储存的值加上增量 increment
     */
    public function testIncrBy()
    {
        $key = $this->generateKey();
        $number = $this->redis->incrby($key, 1);
        $this->assertEquals(1, $number);

        $number = $this->redis->incrby($key, 2);
        $this->assertEquals(3, $number);
    }

    /**
     * 为 key 中所储存的值加上浮点数增量 increment
     */
    public function testIncrByFloat()
    {
        $key = $this->generateKey();
        $number = $this->redis->incrbyfloat($key, 1.1);
        $this->assertEquals(1.1, $number);

        $number = $this->redis->incrbyfloat($key, 1.1);
        $this->assertEquals(2.2, $number);
    }

    /**
     * 返回所有(一个或多个)给定 key 的值
     */
    public function testMGet()
    {
        $key1 = $this->generateKey();
        $key2 = $this->generateKey();
        $this->redis->mset($key1, 'hello', $key2, 'world');

        $res = $this->redis->mget($key1, $key2);
        $this->assertArraySubset(['hello', 'world'], $res);
    }

    public function testMGetA()
    {
        $key1 = $this->generateKey();
        $key2 = $this->generateKey();
        $this->redis->mset($key1, 'hello', $key2, 'world');

        $res = $this->redis->mget_a([$key1, $key2]);
        $this->assertArraySubset(['hello', 'world'], $res);
    }

    /**
     * 同时设置一个或多个 key-value 对
     */
    public function testMSet()
    {
        $key1 = $this->generateKey();
        $key2 = $this->generateKey();
        $res = $this->redis->mset($key1, 'hello', $key2, 'world');
        $this->assertTrue($res);
    }

    public function testMSetA()
    {
        $key1 = $this->generateKey();
        $key2 = $this->generateKey();
        $res = $this->redis->mset_a([$key1, 'hello', $key2, 'world']);
        $this->assertTrue($res);
    }

    /**
     * 同时设置一个或多个 key-value 对，当且仅当所有给定 key 都不存在
     * 即使只有一个给定 key 已存在， MSETNX 也会拒绝执行所有给定 key 的设置操作
     */
    public function testMSetNX()
    {
        $key1 = $this->generateKey();
        $key2 = $this->generateKey();
        $key3 = $this->generateKey();

        $res = $this->redis->msetnx($key1, 'hello', $key2, 'world');
        $this->assertEquals(1, $res);

        $res = $this->redis->msetnx($key1, 'hello', $key3, 'world');
        $this->assertEquals(0, $res);
    }

    public function testMSetNXA()
    {
        $key1 = $this->generateKey();
        $key2 = $this->generateKey();
        $key3 = $this->generateKey();

        $res = $this->redis->msetnx_a([$key1, 'hello', $key2, 'world']);
        $this->assertEquals(1, $res);

        $res = $this->redis->msetnx_a([$key1, 'hello', $key3, 'world']);
        $this->assertEquals(0, $res);
    }

    /**
     * 这个命令和 SETEX 命令相似，但它以毫秒为单位设置 key 的生存时间，而不是像 SETEX 命令那样，以秒为单位
     */
    public function testPSetEX()
    {
        $key = $this->generateKey();
        $res = $this->redis->psetex($key, 1000, 'hello');
        $this->assertTrue($res);
    }

    /**
     * 将字符串值 value 关联到 key
     */
    public function testSet()
    {
        $key = $this->generateKey();
        $res = $this->redis->set($key, 'hello', 'EX', 100, 'NX');
        $this->assertTrue($res);

        $key = $this->generateKey();
        $res = $this->redis->set($key, 'hello', 'PX', 100, 'XX');
        $this->assertNull($res);
    }

    /**
     * 将值 value 关联到 key ，并将 key 的生存时间设为 seconds (以秒为单位)
     */
    public function testSetEx()
    {
        $key = $this->generateKey();
        $res = $this->redis->setex($key, 10, 'hello');
        $this->assertTrue($res);

        $res = $this->redis->setex($key, 10, 'hello2');
        $this->assertTrue($res);
    }

    /**
     * 将 key 的值设为 value ，当且仅当 key 不存在
     */
    public function testSetNx()
    {
        $key = $this->generateKey();
        $res = $this->redis->setnx($key, 'hello');
        $this->assertEquals(1, $res);

        $res = $this->redis->setnx($key, 'hello2');
        $this->assertEquals(0, $res);
    }

    /**
     * 用 value 参数覆写(overwrite)给定 key 所储存的字符串值，从偏移量 offset 开始
     * 返回被 SETRANGE 修改之后，字符串的长度
     */
    public function testSetRange()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello world');

        $length = $this->redis->setrange($key, 6, 'redis');
        $this->assertEquals(11, $length);
        $string = $this->redis->get($key);
        $this->assertEquals('hello redis', $string);
    }

    /**
     * 返回 key 所储存的字符串值的长度
     */
    public function testStrLen()
    {
        $key = $this->generateKey();
        $length = $this->redis->strlen($key);
        $this->assertEquals(0, $length);

        $this->redis->set($key, 'hello world');
        $length = $this->redis->strlen($key);
        $this->assertEquals(11, $length);
    }
}
