<?php
/**
 * @author: jiangyi
 * @date: 下午2:55 2019/2/28
 */

namespace EasyRedis;

class Lock
{
    protected $redis;

    public function __construct(Connection $connection)
    {
        $this->redis = $connection;
    }

    /**
     * 获取锁
     * @param string $lockName
     * @param int|float $acquireTimeout
     * @param int|float $lockTimeout
     * @return bool|string
     */
    public function acquireLock(string $lockName, $acquireTimeout = 10, $lockTimeout = 10)
    {
        $lockName = 'lock:' . $lockName;
        $identifier = $this->getIdentifier();
        $end = time() + $acquireTimeout;
        while (time() < $end) {
            if ($this->redis->setnx($lockName, $identifier)) {
                $this->redis->expire($lockName, $lockTimeout);
                return $identifier;
            } elseif (!$this->redis->ttl($lockName)) {
                $this->redis->expire($lockName, $lockTimeout);
            }
            usleep(1000);
        }
        return false;
    }

    /**
     * 释放锁
     * @param string $lockName
     * @param string $identifier
     * @return bool
     */
    public function releaseLock(string $lockName, string $identifier)
    {
        $lockName = 'lock:' . $lockName;
        while (true) {
            $this->redis->watch($lockName);
            if ($this->redis->get($lockName) == $identifier) {
                $this->redis->multi();
                $this->redis->del($lockName);
                if (!$this->redis->exec()) {
                    continue;
                }
                return true;
            }
            $this->redis->unwatch();
            break;
        }
        return false;
    }


    /**
     * 获取公平信号量锁
     * @param $semname
     * @param $limit
     * @param int $timeout
     * @return bool|string
     */
    public function acquireFairSemaphore($semname, $limit, $timeout = 10)
    {
        $identifier = $this->getIdentifier();
        $microTime = $this->microTimeFloat();
        $ownerZset = $semname . ':owner';
        $counterStr = $semname . ':counter';

        // 清理过期的信号量持有者
        $this->redis->zremrangebyscore($semname, '-inf', $microTime + $timeout);
        // 计数器自增
        $counter = $this->redis->incr($counterStr);
        // 加入超时有序集合
        $this->redis->zadd($semname, $microTime, $identifier);
        // 加入信号量拥有者
        $this->redis->zadd($ownerZset, $counter, $identifier);
        // 交集
        $this->redis->zinterstore($ownerZset, 2, $ownerZset, $semname, 'WEIGHTS', 1, 0);

        $rank = $this->redis->zrank($ownerZset, $identifier);
        if ($rank < $limit) {
            return $identifier;
        }
        // 获取失败，删除添加的信号量
        $this->redis->zrem($semname, $identifier);
        $this->redis->zrem($ownerZset, $identifier);
        return false;
    }

    /**
     * 释放公平信号量
     * @param $semname
     * @param $identifier
     * @return bool
     */
    public function releaseFairSemaphore($semname, $identifier)
    {
        $this->redis->zrem($semname, $identifier);
        $ownerZset = $semname . ':owner';
        return (bool)$this->redis->zrem($ownerZset, $identifier);
    }

    /**
     * 刷新信号量
     * @param $semname
     * @param $identifier
     * @return bool
     */
    public function refreshFairSemaphore($semname, $identifier)
    {
        $microTime = $this->microTimeFloat();
        // 更新客户端持有的信号量
        if ($this->redis->zadd($semname, $microTime, $identifier)) {
            // 告知调用者，客户端已经失去信号量
            $this->releaseFairSemaphore($semname, $identifier);
            return false;
        }
        return true;
    }

    /**
     * 公平信号量加锁，消除竞争条件
     * @param $semname
     * @param $limit
     * @param int $timeout
     * @return bool|string
     */
    public function acquireSemaphoreWithLock($semname, $limit, $timeout = 10)
    {
        if ($identifier = $this->acquireLock($semname, 0.01)) {
            try {
                return $this->acquireFairSemaphore($semname, $limit, $timeout);
            } catch (\Exception $e) {

            } finally {
                $this->releaseLock($semname, $identifier);
            }
        }
        return false;
    }

    protected function microTimeFloat()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }

    protected function getIdentifier()
    {
        return self::uuidV4();
    }

    public static function uuidV4()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
