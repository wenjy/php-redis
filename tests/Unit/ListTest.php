<?php
/**
 * @author: jiangyi
 * @date: 上午10:39 2018/12/18
 */

namespace EasyRedis\Tests\Unit;

use EasyRedis\Tests\TestCase;

class ListTest extends TestCase
{
    /**
     * 将一个或多个值 value 插入到列表 key 的表头
     * 返回执行 LPUSH 命令后，列表的长度
     */
    public function testLPush()
    {
        $key = $this->generateKey();
        $length = $this->redis->lpush($key, 'val1', 'val2', 'val3');
        $this->assertEquals(3, $length);
    }

    /**
     * 将值 value 插入到列表 key 的表头，当且仅当 key 存在并且是一个列表。
     * 和 LPUSH 命令相反，当 key 不存在时， LPUSHX 命令什么也不做
     */
    public function testLPushX()
    {
        $key = $this->generateKey();
        $this->redis->lpush($key, 'val1', 'val2', 'val3');
        $length = $this->redis->lpushx($key, 'val4');
        $this->assertEquals(4, $length);

        $key = $this->generateKey();
        $length = $this->redis->lpushx($key, 'val4');
        $this->assertEquals(0, $length);
    }

    /**
     * 返回列表 key 中指定区间内的元素，区间以偏移量 start 和 stop 指定
     */
    public function testRange()
    {
        $key = $this->generateKey();
        $this->redis->lpush($key, 'val1', 'val2', 'val3');

        $array = $this->redis->lrange($key, 0, 1);
        $this->assertArraySubset(['val3', 'val2'], $array);
    }

    /**
     * 根据参数 count 的值，移除列表中与参数 value 相等的元素
     * count > 0 : 从表头开始向表尾搜索，移除与 value 相等的元素，数量为 count
     * count < 0 : 从表尾开始向表头搜索，移除与 value 相等的元素，数量为 count 的绝对值
     * count = 0 : 移除表中所有与 value 相等的值
     */
    public function testRem()
    {
        $key = $this->generateKey();
        $this->redis->lpush($key, 'val1', 'val2', 'val3', 'val2', 'val3');

        $number = $this->redis->lrem($key, 1, 'val3');
        $this->assertEquals(1, $number);

        $number = $this->redis->lrem($key, -1, 'val3');
        $this->assertEquals(1, $number);

        $number = $this->redis->lrem($key, 0, 'val2');
        $this->assertEquals(2, $number);

        $length = $this->redis->llen($key);
        $this->assertEquals(1, $length);
    }

    /**
     * 将列表 key 下标为 index 的元素的值设置为 value
     */
    public function testSet()
    {
        $key = $this->generateKey();
        $this->redis->lpush($key, 'val1', 'val2', 'val3');

        $res = $this->redis->lset($key, 0, 'val4');
        $this->assertTrue($res);
    }

    /**
     * 对一个列表进行修剪(trim)，就是说，让列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除
     */
    public function testTrim()
    {
        $key = $this->generateKey();
        $this->redis->lpush($key, 'val1', 'val2', 'val3');

        $res = $this->redis->ltrim($key, 0, 1);
        $this->assertTrue($res);
    }

    /**
     * 将列表 source 中的最后一个元素(尾元素)弹出，并返回给客户端。
     * 将 source 弹出的元素插入到列表 destination ，作为 destination 列表的的头元素
     */
    public function testRPopLPush()
    {
        $key = $this->generateKey();
        $this->redis->lpush($key, 'val1', 'val2', 'val3');

        $key1 = $this->generateKey();
        $number = $this->redis->rpoplpush($key, $key1);
        $this->assertEquals('val1', $number);

        $number = $this->redis->lindex($key1, 0);
        $this->assertEquals('val1', $number);
    }

    /**
     * 移除并返回列表 key 的头元素
     */
    public function testLPop()
    {
        $key = $this->generateKey();
        $this->redis->lpush($key, 'val2', 'val3');

        $number = $this->redis->lpop($key);
        $this->assertEquals('val3', $number);

        $number = $this->redis->lpop($key);
        $this->assertEquals('val2', $number);

        $number = $this->redis->lpop($key);
        $this->assertNull($number);

        $key1 = $this->generateKey();
        $number = $this->redis->lpop($key1);
        $this->assertNull($number);
    }

    /**
     * 将一个或多个值 value 插入到列表 key 的表尾(最右边)
     * 返回执行 RPUSH 命令后，列表的长度
     */
    public function testRPush()
    {
        $key = $this->generateKey();
        $length = $this->redis->rpush($key, 'val1', 'val2', 'val3');
        $this->assertEquals(3, $length);
    }

    /**
     * 将值 value 插入到列表 key 的表头，当且仅当 key 存在并且是一个列表。
     * 和 LPUSH 命令相反，当 key 不存在时， LPUSHX 命令什么也不做
     */
    public function testRPushX()
    {
        $key = $this->generateKey();
        $this->redis->rpush($key, 'val1', 'val2', 'val3');
        $length = $this->redis->rpushx($key, 'val4');
        $this->assertEquals(4, $length);

        $key = $this->generateKey();
        $length = $this->redis->rpushx($key, 'val4');
        $this->assertEquals(0, $length);
    }

    /**
     * 移除并返回列表 key 的尾元素
     */
    public function testRPop()
    {
        $key = $this->generateKey();
        $this->redis->rpush($key, 'val2', 'val3');

        $number = $this->redis->rpop($key);
        $this->assertEquals('val3', $number);

        $number = $this->redis->rpop($key);
        $this->assertEquals('val2', $number);

        $number = $this->redis->rpop($key);
        $this->assertNull($number);

        $key1 = $this->generateKey();
        $number = $this->redis->rpop($key1);
        $this->assertNull($number);
    }

    /**
     * 返回列表 key 中，下标为 index 的元素
     */
    public function testIndex()
    {
        $key = $this->generateKey();
        $this->redis->lpush($key, 'val1', 'val2', 'val3');
        $number = $this->redis->lindex($key, 0);
        $this->assertEquals('val3', $number);

        $number = $this->redis->lindex($key, -1);
        $this->assertEquals('val1', $number);

        $number = $this->redis->lindex($key, 3);
        $this->assertNull($number);
    }

    /**
     * 将值 value 插入到列表 key 当中，位于值 pivot 之前或之后
     */
    public function testInsert()
    {
        $key = $this->generateKey();
        $this->redis->lpush($key, 'val1', 'val2', 'val4');

        $length = $this->redis->linsert($key, 'BEFORE', 'val4', 'val3');
        $this->assertEquals(4, $length);

        $length = $this->redis->linsert($key, 'AFTER', 'val4', 'val5');
        $this->assertEquals(5, $length);

        $length = $this->redis->linsert($key, 'AFTER', 'val6', 'val7');
        $this->assertEquals(-1, $length);

        $key1 = $this->generateKey();
        $length = $this->redis->linsert($key1, 'AFTER', 'val6', 'val7');
        $this->assertEquals(0, $length);
    }

    /**
     * 返回列表 key 的长度
     */
    public function testLen()
    {
        $key = $this->generateKey();
        $this->redis->lpush($key, 'val1', 'val2', 'val4');

        $length = $this->redis->llen($key);
        $this->assertEquals(3, $length);
    }
}
