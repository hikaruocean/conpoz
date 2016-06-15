<?php 
namespace Conpoz\Lib\Util;

class ImageManager
{
    public function make($path)
    {
        return new \abeautifulsite\SimpleImage($path);
    }
}