<?php 
namespace Conpoz\App\Controller;

class Error extends \stdClass
{
    public function http404Action () {
        echo 'hello 404!';
    }
}