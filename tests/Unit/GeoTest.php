<?php
/**
 * @author: jiangyi
 * @date: 上午10:50 2018/12/20
 */

namespace EasyRedis\tests\Unit;

use EasyRedis\Tests\TestCase;

class GeoTest extends TestCase
{
    /**
     * 将给定的空间元素（纬度、经度、名字）添加到指定的键里面。 这些数据会以有序集合的形式被储存在键里面
     * 有效的经度介于 -180 度至 180 度之间。
     * 有效的纬度介于 -85.05112878 度至 85.05112878 度之间。
     */
    public function testAdd()
    {
        $key = $this->generateKey();
        $number = $this->redis->geoadd($key, 13.361389, 38.115556, 'Palermo', 15.087269, 37.502669, 'Catania');
        $this->assertEquals(2, $number);
    }

    public function testAddA()
    {
        $key = $this->generateKey();
        $number = $this->redis->geoadd_a($key, [13.361389, 38.115556, 'Palermo', 15.087269, 37.502669, 'Catania']);
        $this->assertEquals(2, $number);
    }

    /**
     * 从键里面返回所有给定位置元素的位置（经度和纬度）
     */
    public function testPos()
    {
        $key = $this->generateKey();
        $this->redis->geoadd($key, 13.361389, 38.115556, 'Palermo', 15.087269, 37.502669, 'Catania');

        $members = $this->redis->geopos($key, 'Palermo', 'Catania', 'NonExisting');
        $this->assertEquals(3, count($members));
    }

    /**
     * 返回两个给定位置之间的距离，如果两个位置之间的其中一个不存在， 那么命令返回空值
     * m 表示单位为米
     * km 表示单位为千米
     * mi 表示单位为英里
     * ft 表示单位为英尺
     */
    public function testDist()
    {
        $key = $this->generateKey();
        $this->redis->geoadd($key, 13.361389, 38.115556, 'Palermo', 15.087269, 37.502669, 'Catania');

        $distance = $this->redis->geodist($key, 'Palermo', 'Catania', 'm');
        $this->assertEquals('166274.1516', $distance);

        $distance = $this->redis->geodist($key, 'Palermo', 'Catania', 'km');
        $this->assertEquals('166.2742', $distance);

        $distance = $this->redis->geodist($key, 'Palermo', 'NonExisting', 'km');
        $this->assertNull($distance);
    }

    /**
     * 以给定的经纬度为中心， 返回键包含的位置元素当中， 与中心的距离不超过给定最大距离的所有位置元素
     * WITHDIST ： 在返回位置元素的同时， 将位置元素与中心之间的距离也一并返回。 距离的单位和用户给定的范围单位保持一致
     * WITHCOORD ： 将位置元素的经度和维度也一并返回
     * WITHHASH ： 以 52 位有符号整数的形式， 返回位置元素经过原始 geohash 编码的有序集合分值
     */
    public function testRadius()
    {
        $key = $this->generateKey();
        $this->redis->geoadd($key, 13.361389, 38.115556, 'Palermo', 15.087269, 37.502669, 'Catania');

        $res = $this->redis->georadius($key, 15, 37, 200, 'km', 'WITHDIST');
        $this->assertEquals(2, count($res));

        $res = $this->redis->georadius($key, 15, 37, 200, 'km', 'WITHCOORD');
        $this->assertEquals(2, count($res));

        $res = $this->redis->georadius($key, 15, 37, 200, 'km', 'WITHDIST', 'WITHCOORD', 'DESC', 'COUNT', 10);
        $this->assertEquals(2, count($res));
    }

    /**
     * 这个命令和 GEORADIUS 命令一样， 都可以找出位于指定范围内的元素， 但是 GEORADIUSBYMEMBER 的中心点是由给定的位置元素决定的，
     * 而不是像 GEORADIUS 那样， 使用输入的经度和纬度来决定中心点
     */
    public function testRadiusByMember()
    {
        $key = $this->generateKey();
        $this->redis->geoadd($key, 13.361389, 38.115556, 'Palermo', 15.087269, 37.502669, 'Catania');
        $this->redis->geoadd($key, 13.583333, 37.316667, 'Agrigento');

        // 返回包括自己在内。。
        $res = $this->redis->georadiusbymember($key, 'Agrigento', 200, 'km', 'WITHDIST');
        $this->assertEquals(3, count($res));

        $res = $this->redis->georadiusbymember($key, 'Agrigento', 200, 'km', 'WITHCOORD');
        $this->assertEquals(3, count($res));

        $res = $this->redis->georadiusbymember($key, 'Agrigento', 200, 'km', 'WITHDIST', 'WITHCOORD', 'DESC', 'COUNT',
            10);
        $this->assertEquals(3, count($res));
    }

    /**
     * 返回一个或多个位置元素的 Geohash 表示
     */
    public function testHash()
    {
        $key = $this->generateKey();
        $this->redis->geoadd($key, 13.361389, 38.115556, 'Palermo', 15.087269, 37.502669, 'Catania');

        $res = $this->redis->geohash($key, 'Palermo');
        $this->assertArraySubset(['sqc8b49rny0'], $res);
    }
}
