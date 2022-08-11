<?php

use Hyperf\Utils\ApplicationContext;
use Psr\SimpleCache\CacheInterface;

if (!function_exists('cache_has_set')) {
    function cache_has_set(string $key, $callback, $tll = 3600)
    {
        $data = cache()->get($key);
        if ($data || $data === false) {
            return $data;
        }
        $data = call_user_func($callback);
        if ($data === null) {
            p('设置空缓存防止穿透');
            cache()->set($key, false, 10);
        } else {
            cache()->set($key, $data, $tll);
        }
        return $data;
    }
}

if (!function_exists('cache')) {
    function cache(): CacheInterface
    {
        return ApplicationContext::getContainer()->get(CacheInterface::class);
    }
}
//输出控制台日志
if (!function_exists('p')) {
    function p($val, $title = null, $starttime = '')
    {
        print_r('[ ' . date("Y-m-d H:i:s") . ']:');
        if ($title != null) {
            print_r("[" . $title . "]:");
        }
        print_r($val);
        print_r("\r\n");
    }
}


if (!function_exists('get_millisecond')) {

    //毫秒级时间戳
    function get_millisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
}