<?php
namespace Conpoz\App\Middleware;
class M2
{
    public static function run ($controller)
    {
        echo __CLASS__ . '<br />';
    }
}