<?php
/**
 * @author: jiangyi
 * @date: 下午3:49 2019/2/28
 */

namespace EasyRedis\tests\Unit;

use EasyRedis\Lock;
use EasyRedis\Tests\TestCase;

class LockTest extends TestCase
{
    public function testLock()
    {
        $redisLock = new Lock($this->redis);
        $lockName = 'test';
        $identifier = $redisLock->acquireLock($lockName);
        $this->assertNotNull($identifier);
        $res = $redisLock->releaseLock($lockName, $identifier);
        $this->assertTrue($res);

        $semname = 'semaphore:remote';
        $identifier = $redisLock->acquireSemaphoreWithLock($semname, 5);
        $this->assertNotNull($identifier);
        $res = $redisLock->releaseFairSemaphore($semname, $identifier);
        $this->assertTrue($res);

        foreach (['lock:test', 'semaphore:remote:owner', 'semaphore:remote:counter'] as $key) {
            $this->redis->del($key);
        }
    }
}
