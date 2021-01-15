<?php
if(!function_exists('hihuPush')){
    function hihuPush($queueName,$msg){
        \Qihu\Queue\QueueHelper::getQueueClient($queueName)->append($queueName, $msg);
    }
}
if(!function_exists('hihuPop')){
    function hihuPop($queueName,$msg){
        \Qihu\Queue\QueueHelper::getQueueClient($queueName)->get($queueName, $msg);
    }
}
