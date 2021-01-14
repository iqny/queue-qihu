<?php

namespace Qihu\Queue\Drive\Redis;

use Qihu\Queue\Drive\DriveInterface;

class Redis implements DriveInterface
{
    private $client = null;

    public function __construct($cfg)
    {
        $this->connect($cfg);
    }

    public function ack()
    {
        return true;
        // TODO: Implement ack() method.
    }

    public function put($key, $val)
    {
        // TODO: Implement put() method.
    }

    public function get($key)
    {
        $ret = $this->client->lpop($key);
        if (is_null($ret)) {
            return 'HTTPSQS_GET_END';
        }
        return $ret;
        // TODO: Implement get() method.
    }

    public function len($key)
    {
        // TODO: Implement len() method.
    }

    public function append($key, $val)
    {
        $ret = $this->client->rpush($key, $val);
        // TODO: Implement append() method.
        return (bool)$ret;
    }

    public function hSet($key, $hashKey, $value)
    {
        return $this->client->hSet($key, $hashKey, $value);
    }

    public function hGet($key, $hashKey)
    {
        return $this->client->hGet($key, $hashKey);
    }
    public function hDel($key){
        return$this->client->hDel($key);
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
            $this->client = new Predis();
        } elseif (!empty($drive) && $drive === 'redis') {
            $this->client = new \Redis();

        } else {
            throw new RedisException("The config missing drive");
        }
        //连接redis-server
        $this->client->pconnect($cfg['host'], $cfg['port']);
        //设置密码
        if (isset($cfg['password'])) {
            $this->client->auth($cfg['password']);
        }
    }

    public function close()
    {
        $this->client->close();
    }
}
