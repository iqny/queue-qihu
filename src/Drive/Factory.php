<?php
namespace Qihu\Queue\Drive;
interface Factory{
    public static function createClient($cfg);
}