<?php
/**
 * @author: jiangyi
 * @date: 上午9:50 2018/12/20
 */

namespace EasyRedis\tests\Unit;

use EasyRedis\Tests\TestCase;

class HyperLogLogTest extends TestCase
{
    /**
     * 在输入元素的数量或者体积非常非常大的时候，计算基数所需的空间总是固定的，并且是很小的。
     * 在Redis里面，每个Hyperloglog键只需要12Kb的大小就能计算接近2^64个不同元素的基数，
     * 但是hyperloglog只会根据输入元素来计算基数，而不会存储元素本身，所以不能像集合那样返回各个元素本身
     *
     * 通过 HyperLogLog 数据结构， 用户可以使用少量固定大小的内存， 来储存集合中的唯一元素
     * （每个 HyperLogLog 只需使用 12k 字节内存，以及几个字节的内存来储存键本身）
     *
     * 这个结构可以非常省内存的去统计各种计数，比如注册ip数、每日访问IP数、页面实时UV（PV肯定字符串就搞定了）、在线用户数等
     */
    public function testPFAdd()
    {
        $key1 = $this->generateKey('ip:20181219');
        $res = $this->redis->pfadd($key1, '1.1.1.1', '2.2.2.2', '3.3.3.3');
        $this->assertEquals(1, $res);
        $res = $this->redis->pfadd($key1, '1.1.1.1');
        $this->assertEquals(0, $res);

        $this->redis->pfadd($key1, '3.3.3.3', '4.4.4.4');
        $count = $this->redis->pfcount($key1);
        $this->assertEquals(4, $count);

        $key2 = $this->generateKey('ip:20181220');
        $this->redis->pfadd($key2, '3.3.3.3', '4.4.4.4', '5.5.5.5');
        $count = $this->redis->pfcount($key1, $key2);
        $this->assertEquals(5, $count);

        $key3 = $this->generateKey('ip:201812');
        $res = $this->redis->pfmerge($key3, $key1, $key2);
        $this->assertTrue($res);

        $count = $this->redis->pfcount($key3);
        $this->assertEquals(5, $count);
    }
}
