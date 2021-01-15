<?php
if(!function_exists('qihuPush')){
    function qihuPush($queueName,$msg){
        \Qihu\Queue\QueueHelper::getQueueClient($queueName)->append($queueName, $msg);
    }
}
if(!function_exists('qihuPop')){
    function qihuPop($queueName,$msg){
        \Qihu\Queue\QueueHelper::getQueueClient($queueName)->get($queueName, $msg);
    }
}
