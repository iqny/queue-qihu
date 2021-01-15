<?php

namespace Qihu\Queue\Drive\Rabbitmq;

use Qihu\Queue\Drive\DriveInterface;

class Rabbitmq implements DriveInterface
{

    private $channel = null;
    private $conn = null;
    private $QMAPQueue = null;
    private $getDeliveryTag = '';
    private $ex = null;

    public function __construct($cfg)
    {
        $this->connect($cfg);
    }

    public function connect($cfg)
    {
        $this->conn = new \AMQPConnection($cfg);
        $this->conn->connect();
        $this->channel = new \AMQPChannel($this->conn);//创建交换机
        $this->ex = new \AMQPExchange($this->channel);
        $this->ex->setName($cfg['exchange']);
        $this->ex->setType(AMQP_EX_TYPE_DIRECT);
        $this->ex->setFlags(AMQP_DURABLE);//持久化
        $this->ex->declareExchange();
    }

    /**
     * @return bool|mixed
     */
    public function ack()
    {
        if ($this->getDeliveryTag) {
            return $this->QMAPQueue->ack($this->getDeliveryTag);
        }
        return true;
        //$this->close();
    }

    public function put($key, $val)
    {
    }

    public function get($key, callable $callable)
    {
        //声明路由键
        $routingKey = $key . '_router';
        //echo "Exchange Status:" . $ex->declareExchange() . "\n";
        $this->QMAPQueue = new \AMQPQueue($this->channel);
        $this->QMAPQueue->setName($key);
        $this->QMAPQueue->setFlags(AMQP_DURABLE);
        $this->QMAPQueue->declareQueue();
        $this->QMAPQueue->bind($this->ex->getName(), $routingKey);
        $this->QMAPQueue->get(AMQP_NOPARAM);
        $this->getDeliveryTag = '';
        /*$body = '';
        if ($messages) {
            $body = $messages->getBody();
            $this->getDeliveryTag = $messages->getDeliveryTag();
        }
        return $body;*/
        //$msg = $arr->getBody();
        //var_dump($msg);
        //$res = $q->ack($arr->getDeliveryTag());
        $this->QMAPQueue->consume($callable);
        /*$this->QMAPQueue->consume(function ($envelope, $queue) {
            $body = $envelope->getBody();
            //var_dump($body);
            $this->body = $body;
            //$ret = yield ($body);
            //if ($ret=='success'){
            $queue->ack($envelope->getDeliveryTag());
            //     yield $body;
            //   }
        });*/
    }

    public function len($key)
    {
        // TODO: Implement len() method.
    }

    public function append($key, $val)
    {
        $routingKey = $key . '_router';
        //发送消息到交换机，并返回发送结果
        //delivery_mode:2声明消息持久，持久的队列+持久的消息在RabbitMQ重启后才不会丢失
        if (!is_string($val)) {
            $val = json_encode($val);
        }
        $this->ex->publish($val, $routingKey, AMQP_NOPARAM, array('delivery_mode' => 2));
        //代码执行完毕后进程会自动退出
        // TODO: Implement append() method.
    }

    public function close()
    {
        $this->channel->close();
        $this->conn->disconnect();
    }
}
