<?php 
namespace Conpoz\Core\Lib\Util;

class ImageManager
{
    public function make($path)
    {
        return new \abeautifulsite\SimpleImage($path);
    }
}