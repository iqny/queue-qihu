<?php
namespace Qihu\Queue\Signal;

class Signal implements SignalInterface{
    public static function SetSigHandler($signal)
    {
        // TODO: Implement sigHandler() method.
        // 安装信号处理函数
        pcntl_signal_dispatch();
        pcntl_signal(SIGTERM, $signal);
        pcntl_signal(SIGHUP, $signal);
        pcntl_signal(SIGINT, $signal);
    }
}