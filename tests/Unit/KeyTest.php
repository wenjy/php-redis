<?php
/**
 * @author: jiangyi
 * @date: 上午11:06 2018/12/19
 */

namespace EasyRedis\tests\Unit;

use EasyRedis\Tests\TestCase;

class KeyTest extends TestCase
{
    /**
     * 删除给定的一个或多个key，不存在的key会被忽略
     */
    public function testDel()
    {
        $key1 = $this->generateKey();
        $key2 = $this->generateKey();

        $this->redis->set($key1, 'hello');
        $res = $this->redis->del($key1, $key2);
        $this->assertEquals(1, $res);
    }

    public function testDelA()
    {
        $key1 = $this->generateKey();
        $key2 = $this->generateKey();

        $this->redis->set($key1, 'hello');
        $res = $this->redis->del_a([$key1, $key2]);
        $this->assertEquals(1, $res);
    }

    /**
     * 序列化给定 key ，并返回被序列化的值，使用 RESTORE 命令可以将这个值反序列化为 Redis 键
     */
    public function testDump()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello');
        $res = $this->redis->dump($key);

        $key1 = $this->generateKey();
        $this->redis->restore($key1, 0, $res);
        $res = $this->redis->get($key1);
        $this->assertEquals('hello', $res);
    }

    /**
     * 检查给定 key 是否存在
     */
    public function testExists()
    {
        $key1 = $this->generateKey();
        $this->redis->set($key1, 'hello');

        $key2 = $this->generateKey();
        $this->redis->set($key2, 'world');

        $res = $this->redis->exists($key1, $key2);
        $this->assertEquals(2, $res);

        $key = $this->generateKey();
        $res = $this->redis->exists($key);
        $this->assertEquals(0, $res);
    }

    public function testExistsA()
    {
        $key1 = $this->generateKey();
        $this->redis->set($key1, 'hello');

        $key2 = $this->generateKey();
        $this->redis->set($key2, 'world');

        $res = $this->redis->exists_a([$key1, $key2]);
        $this->assertEquals(2, $res);
    }

    /**
     * 为给定 key 设置生存时间，当 key 过期时(生存时间为 0 )，它会被自动删除
     */
    public function testExpire()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello');

        $res = $this->redis->expire($key, 10);
        $this->assertEquals(1, $res);
    }

    /**
     * EXPIREAT 的作用和 EXPIRE 类似，都用于为 key 设置生存时间，不同在于 EXPIREAT 命令接受的时间参数是 UNIX 时间戳(unix timestamp)
     */
    public function testExpireAt()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello');
        $res = $this->redis->expireat($key, time() + 100);
        $this->assertEquals(1, $res);
    }

    /**
     * 查找所有符合给定模式 pattern 的 key 。*?[]
     * KEYS * 匹配数据库中所有 key
     * KEYS h?llo 匹配 hello ， hallo 和 hxllo 等
     * KEYS h*llo 匹配 hllo 和 heeeeello 等
     * KEYS h[ae]llo 匹配 hello 和 hallo ，但不匹配 hillo
     */
    public function testKeys()
    {
        $key = $this->generateKey();
        $key1 = $this->generateKey();
        $this->redis->mset($key, 'hello', $key1, 'world');
        $keys = $this->redis->keys('test_redis_key_*');
        $this->assertInternalType('array', $keys);
    }

    /**
     * 命令允许从内部察看给定 key 的 Redis 对象
     * OBJECT REFCOUNT <key> 返回给定 key 引用所储存的值的次数。此命令主要用于除错
     * OBJECT ENCODING <key> 返回给定 key 锁储存的值所使用的内部表示(representation)
     * OBJECT IDLETIME <key> 返回给定 key 自储存以来的空闲时间(idle， 没有被读取也没有被写入)，以秒为单位
     */
    public function testObject()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello');

        $res = $this->redis->object('REFCOUNT', $key);
        $this->assertEquals(1, $res);

        $res = $this->redis->object('IDLETIME', $key);
        $this->assertEquals(0, $res);

        $res = $this->redis->object('ENCODING', $key);
        $this->assertEquals('embstr', $res);
    }

    public function testObjectA()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello');

        $res = $this->redis->object_a('REFCOUNT', [$key]);
        $this->assertEquals(1, $res);

        $res = $this->redis->object_a('IDLETIME', [$key]);
        $this->assertEquals(0, $res);

        $res = $this->redis->object_a('ENCODING', [$key]);
        $this->assertEquals('embstr', $res);
    }

    /**
     * 移除给定 key 的生存时间，将这个 key 从『易失的』(带生存时间 key )转换成『持久的』(一个不带生存时间、永不过期的 key )
     */
    public function testPersist()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello');

        $res = $this->redis->persist($key);
        $this->assertEquals(0, $res);

        $this->redis->expire($key, 10);
        $res = $this->redis->persist($key);
        $this->assertEquals(1, $res);
    }

    /**
     * 这个命令和 EXPIRE 命令的作用类似，但是它以毫秒为单位设置 key 的生存时间，而不像 EXPIRE 命令那样，以秒为单位
     */
    public function testPExpire()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello');

        $res = $this->redis->pexpire($key, 100);
        $this->assertEquals(1, $res);
    }

    /**
     * 这个命令和 EXPIREAT 命令类似，但它以毫秒为单位设置 key 的过期 unix 时间戳，而不是像 EXPIREAT 那样，以秒为单位
     */
    public function testPExpireAt()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello');

        $res = $this->redis->pexpire($key, (time() + 100) * 100);
        $this->assertEquals(1, $res);
    }

    /**
     * 以秒为单位，返回给定 key 的剩余生存时间
     */
    public function testTtl()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello');

        $this->redis->expire($key, 10);
        $res = $this->redis->ttl($key);
        $this->assertLessThanOrEqual(10, $res);

        $key = $this->generateKey();
        $this->redis->set($key, 'hello');
        $res = $this->redis->ttl($key);
        $this->assertEquals(-1, $res);

        $res = $this->redis->ttl($this->generateKey());
        $this->assertEquals(-2, $res);
    }

    /**
     * 这个命令类似于 TTL 命令，但它以毫秒为单位返回 key 的剩余生存时间，而不是像 TTL 命令那样，以秒为单位
     */
    public function testPTtl()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello');

        $this->redis->expire($key, 10);
        $res = $this->redis->pttl($key);
        $this->assertLessThanOrEqual(10000, $res);
    }

    /**
     * 从当前数据库中随机返回(不删除)一个 key
     */
    public function testRandomKey()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello');
        $key = $this->redis->randomkey();
        $this->assertNotNull($key);
    }

    /**
     * 当 key 和 newkey 相同，或者 key 不存在时，返回一个错误
     */
    public function testRename()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello');

        $newKey = $this->generateKey();
        $res = $this->redis->rename($key, $newKey);
        $this->assertTrue($res);
    }

    /**
     * 当且仅当 newkey 不存在时，将 key 改名为 newkey
     */
    public function testRenameNX()
    {
        $key = $this->generateKey();
        $this->redis->set($key, 'hello');

        $newKey = $this->generateKey();
        $res = $this->redis->renamenx($key, $newKey);
        $this->assertEquals(1, $res);
    }

    public function testSort()
    {
        $key = $this->generateKey();
        $this->redis->lpush($key, 30, 1.5, 5, 8, 2, 10);
        $res = $this->redis->sort($key);
        $this->assertArraySubset([1.5, 2, 5, 8, 10, 30], $res);

        $res = $this->redis->sort($key, 'DESC');
        $this->assertArraySubset([30, 10, 8, 5, 2, 1.5], $res);

        $res = $this->redis->sort($key, 'LIMIT', 0, 3, 'ASC');
        $this->assertArraySubset([1.5, 2, 5], $res);

        $data = [
            [1, 'admin', 9999],
            [2, 'jack', 5],
            [3, 'mary', 99],
        ];
        $uidKey = $this->generateKey('uid');
        foreach ($data as $v) {
            $this->redis->lpush($uidKey, $v[0]);
            $userNameKey = $this->generateKey('user_name_' . $v[0]);
            $this->redis->set($userNameKey, $v[1]);
            $userLevelKey = $this->generateKey('user_level_' . $v[0]);
            $this->redis->set($userLevelKey, $v[2]);
        }

        $userNamePattern = $this->generateKey('user_name_*');
        $userLevelPattern = $this->generateKey('user_level_*');
        $res = $this->redis->sort($uidKey, 'BY', $userLevelPattern);
        $this->assertArraySubset([2, 3, 1], $res);

        $res = $this->redis->sort($uidKey, 'GET', $userNamePattern);
        $this->assertArraySubset(['admin', 'jack', 'mary'], $res);

        $res = $this->redis->sort($uidKey, 'BY', $userLevelPattern, 'GET', $userNamePattern);
        $this->assertArraySubset(['jack', 'mary', 'admin'], $res);

        $res = $this->redis->sort($uidKey, 'GET', $userLevelPattern, 'GET', $userNamePattern);
        $this->assertArraySubset([9999, 'admin', 5, 'jack', 99, 'mary'], $res);

        $res = $this->redis->sort($uidKey, 'GET', '#', 'GET', $userLevelPattern, 'GET', $userNamePattern);
        $this->assertArraySubset([1, 9999, 'admin', 2, 5, 'jack', 3, 99, 'mary'], $res);

        $res = $this->redis->sort($uidKey, 'BY', $this->generateKey());
        $this->assertArraySubset([3, 2, 1], $res);

        $res = $this->redis->sort($uidKey, 'BY', $this->generateKey(), 'GET', '#', 'GET', $userLevelPattern, 'GET',
            $userNamePattern);
        $this->assertArraySubset([3, 99, 'mary', 2, 5, 'jack', 1, 9999, 'admin'], $res);
    }

    /**
     * 返回 key 所储存的值的类型
     */
    public function testType()
    {
        $type = $this->redis->type($this->generateKey());
        $this->assertEquals('none', $type);

        $key = $this->generateKey();
        $this->redis->set($key, 'hello');
        $type = $this->redis->type($key);
        $this->assertEquals('string', $type);

        $key = $this->generateKey();
        $this->redis->lpush($key, 'hello');
        $type = $this->redis->type($key);
        $this->assertEquals('list', $type);

        $key = $this->generateKey();
        $this->redis->sadd($key, 'hello');
        $type = $this->redis->type($key);
        $this->assertEquals('set', $type);

        $key = $this->generateKey();
        $this->redis->zadd($key, 1, 'hello');
        $type = $this->redis->type($key);
        $this->assertEquals('zset', $type);

        $key = $this->generateKey();
        $this->redis->hset($key, 'key1', 'hello');
        $type = $this->redis->type($key);
        $this->assertEquals('hash', $type);
    }

    /*
     * SCAN 命令用于迭代当前数据库中的数据库键
     * SSCAN 命令用于迭代集合键中的元素
     * HSCAN 命令用于迭代哈希键中的键值对
     * ZSCAN 命令用于迭代有序集合中的元素（包括元素成员和元素分值）
     */
    public function testScan()
    {
        $key1 = $this->generateKey();
        $this->redis->set($key1, 'hello');

        $key2 = $this->generateKey();
        $this->redis->lpush($key2, 'hello', 'world');

        $key3 = $this->generateKey();
        $this->redis->sadd($key3, 'hello', 'world');

        $key4 = $this->generateKey();
        $this->redis->zadd($key4, 1, 'hello', 2, 'world');

        $key5 = $this->generateKey();
        $this->redis->hmset($key5, 'key1', 'hello', 'key2', 'world');

        $res = $this->redis->scan(0, 'MATCH', self::TEST_REDIS_KEY_PRE . '*', 'COUNT', 5);
        $this->assertEquals(5, count($res[1]));

        $res = $this->redis->sscan($key3, 0);
        $this->assertEquals(2, count($res[1]));

        $res = $this->redis->hscan($key5, 0);
        $this->assertArraySubset(['0', ['key1', 'hello', 'key2', 'world']], $res);

        $res = $this->redis->zscan($key4, 0);
        $this->assertArraySubset(['0', ['hello', 1, 'world', 2]], $res);
    }
}
