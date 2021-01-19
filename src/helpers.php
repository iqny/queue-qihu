<?php
if(!function_exists('qihuPush')){
    function qihuPush($queueName,$msg){
        \Qihu\Queue\ConnPool::getQueueClient($queueName)->append($queueName, $msg);
    }
}
