# php-redis

[![Build Status](https://travis-ci.com/wenjy/php-redis.svg?branch=master)](https://travis-ci.com/wenjy/php-redis)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wenjy/php-redis/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/wenjy/php-redis/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/wenjy/php-redis/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/wenjy/php-redis/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/wenjy/php-redis/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)

## Installation
```php
composer require "wenjy/redis:~1.0"
```

## Usage
```php
<?php
    $config = [
        'hostname' => '127.0.0.1',
        'port' => 6379,
        'database' => 0,
    ];
    $redis = new \EasyRedis\Connection($config);
    $redis->set('string_key', 'test_value');
    echo $redis->get('string_key');
    
    // lock
    $redisLock = new \EasyRedis\Lock($this->redis);
    $lockName = 'test';
    $identifier = $redisLock->acquireLock($lockName);
    // code...
    $redisLock->releaseLock($lockName, $identifier);
    
    $semname = 'semaphore:remote';
    $identifier = $redisLock->acquireSemaphoreWithLock($semname, 5);
    // code...
    $res = $redisLock->releaseFairSemaphore($semname, $identifier);
```
