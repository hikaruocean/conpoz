<?php
namespace Conpoz\App\Middleware;
class M1
{
    public static function run ($controller)
    {
        echo __CLASS__ . '<br />';
        return true;
    }
}