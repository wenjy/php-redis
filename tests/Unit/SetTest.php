<?php

/**
 * @author: jiangyi
 * @date: 下午5:23 2018/12/14
 */

namespace EasyRedis\Tests\Unit;

use EasyRedis\Tests\TestCase;

class SetTest extends TestCase
{
    /**
     * 将一个或多个 member 元素加入到集合 key 当中，已经存在于集合的 member 元素将被忽略。
     * 返回被添加到集合中的新元素的数量，不包括被忽略的元素。key已存在时返回0
     */
    public function testAdd()
    {
        $key = $this->generateKey();
        $number = $this->redis->sadd($key, 'val1', 'val2');
        $this->assertEquals(2, $number);

        $number = $this->redis->sadd($key, 'val1', 'val2');
        $this->assertEquals(0, $number);
    }

    public function testAddA()
    {
        $key = $this->generateKey();
        $number = $this->redis->sadd_a($key, ['val1', 'val2']);
        $this->assertEquals(2, $number);

        $number = $this->redis->sadd_a($key, ['val1', 'val2']);
        $this->assertEquals(0, $number);
    }

    /**
     * 返回集合 key 中元素的数量，当 key 不存在时，返回 0
     */
    public function testCard()
    {
        $key = $this->generateKey();
        $this->redis->sadd($key, 'val1', 'val2');
        $number = $this->redis->scard($key);
        $this->assertEquals(2, $number);

        $key1 = $this->generateKey();
        $number = $this->redis->scard($key1);
        $this->assertEquals(0, $number);
    }

    /**
     * 返回一个集合的全部成员，该集合是所有给定集合之间的差集。不存在的 key 被视为空集
     * 存在 第一个key 中，但是不存在其他的key中的元素
     */
    public function testDiff()
    {
        $key1 = $this->generateKey();
        $key2 = $this->generateKey();
        $this->redis->sadd($key1, 'val1', 'val2');
        $this->redis->sadd($key2, 'val1', 'val3');
        $diffArray = $this->redis->sdiff($key1, $key2);
        $this->assertArraySubset(['val2'], $diffArray);
    }

    public function testDiffStore()
    {
        $key1 = $this->generateKey();
        $key2 = $this->generateKey();
        $key3 = $this->generateKey();
        $this->redis->sadd($key1, 'val1', 'val2');
        $this->redis->sadd($key2, 'val1', 'val3');
        $number = $this->redis->sdiffstore($key3, $key1, $key2);

        $this->assertEquals(1, $number);
    }

    /**
     * 返回一个集合的全部成员，该集合是所有给定集合的交集。
     */
    public function testInter()
    {
        $key1 = $this->generateKey();
        $key2 = $this->generateKey();
        $this->redis->sadd($key1, 'val1', 'val2');
        $this->redis->sadd($key2, 'val1', 'val3');
        $interArray = $this->redis->sinter($key1, $key2);
        $this->assertArraySubset($interArray, ['val1']);
    }

    public function testInterStore()
    {
        $key1 = $this->generateKey();
        $key2 = $this->generateKey();
        $key3 = $this->generateKey();
        $this->redis->sadd($key1, 'val1', 'val2');
        $this->redis->sadd($key2, 'val1', 'val3');
        $number = $this->redis->sinterstore($key3, $key1, $key2);
        $this->assertEquals(1, $number);
    }

    /**
     * 判断 member 元素是否集合 key 的成员
     * 返回 是/1 不是/0
     */
    public function testIsMember()
    {
        $key = $this->generateKey();
        $this->redis->sadd($key, 'val1', 'val2');
        $exists = $this->redis->sismember($key, 'val1');
        $this->assertEquals(1, $exists);
        $notExists = $this->redis->sismember($key, 'val3');
        $this->assertEquals(0, $notExists);
    }

    /**
     * 返回集合 key 中的所有成员
     */
    public function testMembers()
    {
        $key = $this->generateKey();
        $this->redis->sadd($key, 'val1', 'val2');
        $array = $this->redis->smembers($key);
        sort($array);
        $this->assertArraySubset(['val1', 'val2'], $array);
    }

    /**
     * 将 member 元素从 source 集合移动到 destination 集合
     * 如果 source 集合不存在或不包含指定的 member 元素，命令不执行任何操作，仅返回 0
     */
    public function testMove()
    {
        $sourceKey = $this->generateKey();
        $destinationKey = $this->generateKey();
        $this->redis->sadd($sourceKey, 'val1', 'val2');
        $this->redis->sadd($destinationKey, 'val3', 'val4');
        $res = $this->redis->smove($sourceKey, $destinationKey, 'val1');
        $this->assertEquals(1, $res);

        $res = $this->redis->smove($sourceKey, $destinationKey, 'val3');
        $this->assertEquals(0, $res);

        $notExistsKey = $this->generateKey();
        $res = $this->redis->smove($notExistsKey, $destinationKey, 'val1');
        $this->assertEquals(0, $res);
    }

    /**
     * 移除并返回集合中的一个随机元素，3.2之后指定多个
     * 当 key 不存在或 key 是空集时，返回 nil
     */
    public function testPop()
    {
        $key = $this->generateKey();
        $this->redis->sadd($key, 'val1', 'val2', 'val3');
        $members = $this->redis->spop($key);
        $this->assertTrue(in_array($members, ['val1', 'val2', 'val3']));

        $members = $this->redis->spop($key, 2);
        $this->assertEquals(2, count($members));

        $this->assertNull($this->redis->spop($this->generateKey()));
    }

    /**
     * 只提供 key 参数时，返回一个元素；如果集合为空，返回 nil 。
     * 如果提供了 count 参数，那么返回一个数组；如果集合为空，返回空数组。
     */
    public function testRandMember()
    {
        $key = $this->generateKey();
        $this->redis->sadd($key, 'val1', 'val2', 'val3');
        $members = $this->redis->srandmember($key);
        $this->assertTrue(in_array($members, ['val1', 'val2', 'val3']));

        $members = $this->redis->srandmember($key, 2);
        $this->assertEquals(2, count($members));

        $this->assertNull($this->redis->srandmember($this->generateKey()));
    }

    /**
     * 移除集合 key 中的一个或多个 member 元素，不存在的 member 元素会被忽略
     * 返回被成功移除的元素的数量，不包括被忽略的元素
     */
    public function testRem()
    {
        $key = $this->generateKey();
        $this->redis->sadd($key, 'val1', 'val2', 'val3');
        $number = $this->redis->srem($key, 'val1', 'val2');
        $this->assertEquals(2, $number);

        $number = $this->redis->srem($key, 'val3', 'val4');
        $this->assertEquals(1, $number);
    }

    public function testRemA()
    {
        $key = $this->generateKey();
        $this->redis->sadd($key, 'val1', 'val2', 'val3');
        $number = $this->redis->srem_a($key, ['val1', 'val2']);
        $this->assertEquals(2, $number);

        $number = $this->redis->srem_a($key, ['val3', 'val4']);
        $this->assertEquals(1, $number);
    }

    /**
     * 返回一个集合的全部成员，该集合是所有给定集合的并集。
     */
    public function testUnion()
    {
        $key1 = $this->generateKey();
        $key2 = $this->generateKey();
        $this->redis->sadd($key1, 'val1', 'val2');
        $this->redis->sadd($key2, 'val1', 'val3');
        $unionArray = $this->redis->sunion($key1, $key2);
        sort($unionArray);
        $this->assertArraySubset(['val1', 'val2', 'val3'], $unionArray);
    }

    public function testUnionStore()
    {
        $key1 = $this->generateKey();
        $key2 = $this->generateKey();
        $key3 = $this->generateKey();
        $this->redis->sadd($key1, 'val1', 'val2');
        $this->redis->sadd($key2, 'val1', 'val3');
        $number = $this->redis->sunionstore($key3, $key1, $key2);
        $this->assertEquals(3, $number);
    }
}
