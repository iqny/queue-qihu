<?php

namespace App\Queueqihu;

use Qihu\Queue\Queue\BaseQueue;

class LogQueue extends BaseQueue
{

    function parse():bool
    {
        // TODO: Implement parse() method.
        // echo "方法".__CLASS__.__METHOD__.PHP_EOL;
        //echo "test$i\n";
        $this->info(json_encode($this->getData()));
        //sleep(1);
        return true;
    }
}
