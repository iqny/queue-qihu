<?php

namespace App\Queueqihu;

use Qihu\Queue\Queue\BaseQueue;

class OrderQueue extends BaseQueue
{

    function parse()
    {
        // TODO: Implement parse() method.
        // echo "方法".__CLASS__.__METHOD__.PHP_EOL;
        $this->info($this->data);
        //sleep(2);
        return true;
    }
}
