<?php

namespace App\Queueqihu;

use Qihu\Queue\Queue\BaseQueue;

class TestQueue extends BaseQueue
{

    function parse()
    {
        // TODO: Implement parse() method.
        // echo "方法".__CLASS__.__METHOD__.PHP_EOL;
        //echo "test$i\n";
        $this->info(json_encode($this->data));
        //sleep(1);
        return true;
    }
}
