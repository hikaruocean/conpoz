<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit89c43a155045390546fb5e3f5bf1244e
{
    public static $prefixesPsr0 = array (
        'a' => 
        array (
            'abeautifulsite' => 
            array (
                0 => __DIR__ . '/..' . '/abeautifulsite/simpleimage/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit89c43a155045390546fb5e3f5bf1244e::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}