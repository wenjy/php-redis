<?php
/**
 * @author: jiangyi
 * @date: 下午2:37 2018/12/17
 */

namespace EasyRedis\Tests\Unit;

use EasyRedis\Tests\TestCase;

class StoredSetTest extends TestCase
{
    /**
     * 将一个或多个 member 元素及其 score 值加入到有序集 key 当中。
     * 返回被成功添加的新成员的数量，不包括那些被更新的、已经存在的成员
     */
    public function testAdd()
    {
        $key = $this->generateKey();
        $number = $this->redis->zadd($key, 1, 'val1', 2, 'val2');
        $this->assertEquals(2, $number);

        $number = $this->redis->zadd($key, 3, 'val1', 4, 'val2');
        $this->assertEquals(0, $number);
    }

    /**
     * 返回有序集 key 的基数，key不存在时返回0
     */
    public function testCard()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 2, 'val2');

        $number = $this->redis->zcard($key);
        $this->assertEquals(2, $number);

        $this->assertEquals(0, $this->redis->zcard($this->generateKey()));
    }

    /**
     * 返回有序集 key 中， score 值在 min 和 max 之间(默认包括 score 值等于 min 或 max )的成员的数量
     */
    public function testCount()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 2, 'val2', 2, 'val3', 3, 'val4');

        $number = $this->redis->zcount($key, 2, 3);
        $this->assertEquals(3, $number);
    }

    /**
     * 为有序集 key 的成员 member 的 score 值加上增量 increment
     * 返回member 成员的新 score 值，以字符串形式表示
     */
    public function testIncrBy()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 2, 'val2');

        $score = $this->redis->zincrby($key, 2, 'val1');
        $this->assertEquals(3, $score);
    }

    /**
     * 返回有序集 key 中，指定区间内的成员，带有 score 值(可选)
     *
     */
    public function testRange()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 2, 'val2', 2, 'val3', 3, 'val4');

        $array = $this->redis->zrange($key, 1, 2);
        $this->assertArraySubset(['val2', 'val3'], $array);

        $array = $this->redis->zrange($key, 1, 2, 'WITHSCORES');
        $this->assertArraySubset(['val2', 2, 'val3', 2], $array);
    }

    /**
     * 返回有序集 key 中，所有 score 值介于 min 和 max 之间(包括等于 min 或 max )的成员。
     * 有序集成员按 score 值递增(从小到大)次序排列
     */
    public function testRangeByScore()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 2, 'val2', 2, 'val3', 3, 'val4');

        $array = $this->redis->zrangebyscore($key, 1, 2);
        $this->assertArraySubset(['val1', 'val2', 'val3'], $array);

        $array = $this->redis->zrangebyscore($key, 1, 2, 'WITHSCORES');
        $this->assertArraySubset(['val1', 1, 'val2', 2, 'val3', 2], $array);

        $array = $this->redis->zrangebyscore($key, 1, 2, 'WITHSCORES', 'LIMIT', 0, 1);
        $this->assertArraySubset(['val1', 1], $array);
    }

    /**
     * 返回有序集 key 中成员 member 的排名。其中有序集成员按 score 值递增(从小到大)顺序排列
     * 排名以 0 为底，也就是说， score 值最小的成员排名为 0
     */
    public function testRank()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 2, 'val2', 2, 'val3', 3, 'val4', 4, 'val5');

        $rank = $this->redis->zrank($key, 'val5');
        $this->assertEquals(4, $rank);
    }

    /**
     * 移除有序集 key 中的一个或多个成员，不存在的成员将被忽略
     */
    public function testRem()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 2, 'val2');

        $removeNumber = $this->redis->zrem($key, 'val1');
        $this->assertEquals(1, $removeNumber);

        $removeNumber = $this->redis->zrem($key, 'val2', 'val3');
        $this->assertEquals(1, $removeNumber);
    }

    /**
     * 移除有序集 key 中，指定排名(rank)区间内的所有成员
     * 返回被移除成员的数量
     */
    public function testRemRangeByRank()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 2, 'val2', 2, 'val3', 3, 'val4');

        $removeNumber = $this->redis->zremrangebyrank($key, 1, 2);
        $this->assertEquals(2, $removeNumber);

        $rank = $this->redis->zrank($key, 'val4');
        $this->assertEquals(1, $rank);
    }

    /**
     * 移除有序集 key 中，所有 score 值介于 min 和 max 之间(包括等于 min 或 max )的成员
     * 返回被移除成员的数量
     */
    public function testRemRangeByStore()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 2, 'val2', 2, 'val3', 3, 'val4');

        $removeNumber = $this->redis->zremrangebyscore($key, 1, 2);
        $this->assertEquals(3, $removeNumber);

        $rank = $this->redis->zrank($key, 'val4');
        $this->assertEquals(0, $rank);
    }

    /**
     * 返回指定区间内，带有 score 值(可选)的有序集成员的列表
     * 其中成员的位置按 score 值递减(从大到小)来排列。
     */
    public function testRevRange()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 2, 'val2', 2, 'val3', 3, 'val4');

        $array = $this->redis->zrevrange($key, 0, 1);
        $this->assertArraySubset(['val4', 'val3'], $array);

        $array = $this->redis->zrevrange($key, 0, 1, 'WITHSCORES');
        $this->assertArraySubset(['val4', 3, 'val3', 2], $array);
    }

    /**
     * 返回有序集 key 中， score 值介于 max 和 min 之间(默认包括等于 max 或 min )的所有的成员。
     * 有序集成员按 score 值递减(从大到小)次序排列
     */
    public function testRevRangeByScore()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 2, 'val2', 2, 'val3', 3, 'val4');

        $array = $this->redis->zrevrangebyscore($key, 2, 1);
        $this->assertArraySubset(['val3', 'val2', 'val1'], $array);

        $array = $this->redis->zrevrangebyscore($key, 2, 1, 'WITHSCORES');
        $this->assertArraySubset(['val3', 2, 'val2', 2, 'val1', 1], $array);

        $array = $this->redis->zrevrangebyscore($key, 2, 1, 'WITHSCORES', 'LIMIT', 0, 1);
        $this->assertArraySubset(['val3', 2], $array);
    }

    /**
     * 返回有序集 key 中成员 member 的排名。其中有序集成员按 score 值递减(从大到小)排序。
     */
    public function testRevRank()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 2, 'val2', 2, 'val3', 3, 'val4', 4, 'val5');

        $rank = $this->redis->zrevrank($key, 'val5');
        $this->assertEquals(0, $rank);
    }

    /**
     * 返回有序集 key 中，成员 member 的 score 值
     */
    public function testScore()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 2, 'val2');

        $score = $this->redis->zscore($key, 'val2');
        $this->assertEquals(2, $score);
    }

    /**
     * 计算给定的一个或多个有序集的并集，其中给定 key 的数量必须以 numkeys 参数指定，并将该并集(结果集)储存到 destination
     */
    public function testUnionStore()
    {
        $key1 = $this->generateKey();
        $this->redis->zadd($key1, 1, 'val1', 2, 'val2');

        $key2 = $this->generateKey();
        $this->redis->zadd($key2, 3, 'val2', 4, 'val4');

        $key3 = $this->generateKey();
        $this->redis->zunionstore($key3, 2, $key1, $key2);
        $array = $this->redis->zrange($key3, 0, -1, 'WITHSCORES');
        $this->assertArraySubset(['val1', 1, 'val4', 4, 'val2', 5], $array);

        $key4 = $this->generateKey();
        $this->redis->zunionstore($key4, 2, $key1, $key2, 'WEIGHTS', 1, 2);
        $array = $this->redis->zrange($key4, 0, -1, 'WITHSCORES');
        $this->assertArraySubset(['val1', 1, 'val2', 8, 'val4', 8], $array);

        $key5 = $this->generateKey();
        $this->redis->zunionstore($key5, 2, $key1, $key2, 'WEIGHTS', 1, 2, 'AGGREGATE', 'MAX');
        $array = $this->redis->zrange($key5, 0, -1, 'WITHSCORES');
        $this->assertArraySubset(['val1', 1, 'val2', 6, 'val4', 8], $array);
    }

    /**
     * 计算给定的一个或多个有序集的交集，其中给定 key 的数量必须以 numkeys 参数指定，并将该交集(结果集)储存到 destination 。
     */
    public function testInterStore()
    {
        $key1 = $this->generateKey();
        $this->redis->zadd($key1, 1, 'val1', 2, 'val2');

        $key2 = $this->generateKey();
        $this->redis->zadd($key2, 3, 'val2', 4, 'val4');

        $key3 = $this->generateKey();
        $this->redis->zinterstore($key3, 2, $key1, $key2);
        $array = $this->redis->zrange($key3, 0, -1, 'WITHSCORES');
        $this->assertArraySubset(['val2', 5], $array);

        $key4 = $this->generateKey();
        $this->redis->zinterstore($key4, 2, $key1, $key2, 'WEIGHTS', 1, 2);
        $array = $this->redis->zrange($key4, 0, -1, 'WITHSCORES');
        $this->assertArraySubset(['val2', 8], $array);

        $key5 = $this->generateKey();
        $this->redis->zinterstore($key5, 2, $key1, $key2, 'WEIGHTS', 1, 2, 'AGGREGATE', 'MAX');
        $array = $this->redis->zrange($key5, 0, -1, 'WITHSCORES');
        $this->assertArraySubset(['val2', 6], $array);
    }

    /**
     * 当有序集合的所有成员都具有相同的分值时， 有序集合的元素会根据成员的字典序（lexicographical ordering）来进行排序，
     * 而这个命令则可以返回给定的有序集合键 key 中， 值介于 min 和 max 之间的成员。
     */
    public function testRangeByLex()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 1, 'val2', 1, 'val3');

        $array = $this->redis->zrangebylex($key, '-', '[val2');
        $this->assertArraySubset(['val1', 'val2'], $array);
    }

    /**
     * 对于一个所有成员的分值都相同的有序集合键 key 来说， 这个命令会返回该集合中， 成员介于 min 和 max 范围内的元素数量。
     * 返回指定范围内的元素数量
     */
    public function testLexCount()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 1, 'val2', 1, 'val3');

        $number = $this->redis->zlexcount($key, '-', '[val2');
        $this->assertEquals(2, $number);
    }

    /**
     * 对于一个所有成员的分值都相同的有序集合键 key 来说， 这个命令会移除该集合中， 成员介于 min 和 max 范围内的所有元素。
     * 返回被移除的元素数量
     */
    public function testRemRangeByLex()
    {
        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'val1', 1, 'val2', 1, 'val3');

        $number = $this->redis->zremrangebylex($key, '-', '[val2');
        $this->assertEquals(2, $number);

        $array = $this->redis->zrange($key, 0, -1);
        $this->assertArraySubset(['val3'], $array);
    }
}
