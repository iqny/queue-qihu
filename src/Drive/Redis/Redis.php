<?php

namespace Qihu\Queue\Drive\Redis;

use Qihu\Queue\Drive\DriveInterface;
use Predis\Client;

class Redis implements DriveInterface
{
    private $client = null;
    private $cfg = [];
    private $key = '';

    public function __construct($cfg)
    {
        $this->cfg = $cfg;
        $this->connect($cfg);
    }

    public function ack($ok = true)
    {
        return true;
        // TODO: Implement ack() method.
    }

    public function put($key, $val)
    {
        // TODO: Implement put() method.
    }

    public function get($key, callable $callable)
    {
        $this->key = $key;
        $callable($this, $this);


        // TODO: Implement get() method.
    }

    public function getBody()
    {
        return $this->client->lpop($this->key);
    }

    public function getDeliveryTag()
    {
        return true;
    }

    public function len($key)
    {
        // TODO: Implement len() method.
    }

    public function append($key, $val)
    {
        if (!is_string($val)) {
            $val = json_encode($val);
        }
        $ret = $this->client->rpush($key, $val);
        // TODO: Implement append() method.
        return (bool)$ret;
    }

    public function hSet($key, $hashKey, $value)
    {
        return $this->client->hset($key, $hashKey, $value);
    }

    public function hGet($key, $hashKey)
    {
        return $this->client->hget($key, $hashKey);
    }

    public function hDel($key, $hashKey)
    {
        return $this->client->hdel($key, $hashKey);
    }

    public function del($key)
    {
        return $this->client->del($key);
    }

    public function connect($cfg)
    {
        //实例redis
        $drive = isset($cfg['drive']) ? $cfg['drive'] : '';
        if (!empty($drive) && $drive === 'predis') {
            $this->client = new Client($cfg);
        } elseif (!empty($drive) && $drive === 'redis') {
            $this->client = new \Redis();
            //连接redis-server
            $this->client->pconnect($cfg['host'], $cfg['port']);
        } else {
            throw new RedisException("The config missing drive");
        }

        //设置密码
        if (isset($cfg['password']) && !empty($cfg['password'])) {
            $this->client->auth($cfg['password']);
        }
    }

    public function close()
    {
        if ($this->cfg['drive'] === 'redis') {
            $this->client->close();
        }
    }
}
