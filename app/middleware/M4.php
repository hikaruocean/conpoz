<?php
namespace Conpoz\App\Middleware;
class M4
{
    public static function run ($controller)
    {
        echo __CLASS__ . '<br />';
        return true;
    }
}