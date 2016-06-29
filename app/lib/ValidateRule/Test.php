<?php 
namespace Conpoz\App\Lib\ValidateRule;

class Test extends \Conpoz\Core\Lib\Util\Validator\ValidateRule
{
    public $id = array(
        'required' => 'id is required!',
        'min-length:4' => 'id\'s min lenght is 4',
        'max-length:24' => 'id\'s max lenght is 24',
        'email' => 'id need valid email format',
        'regex-rule:/hikaru.com$/' => 'email must use hikaru.com'
        );
    public $password = array(
        'required' => 'password required',
        'alpha-numeric' => 'password format is alpha-numeric',
        'min-length:6' => 'password\'s min lenght is 6',
        'max-length:20' => 'password\'s max lenght is 20',
        );

    public $retype_password = array(
        'required' => 'retype password plz',
        'compare-with:password' => 'password different with retype password'
        );
    public $birthday = array(
        'date' => 'yyyy-mm-dd birthday format error'
        );
    public $tel = array(
        'tel' => 'tel format error'
        );
    public $ip = array(
        'ip' => 'ip format error'
        );
    public $number = array(
        'number' => 'must number'
        );
    public $numeric = array(
        'numeric' => 'must numeric'
        ) ;
    public $datetime = array(
        'date-time' => 'must datetime'
        );
}