<?php 
spl_autoload_register(function ($class) use ($config) {
    $fallbackDirsPsr4 = array();
    foreach ($config->autoloadNamespace as $prefix => $paths) {
        if (!$prefix) {
            $fallbackDirsPsr4 = (array) $paths;
        } else {
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            $prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            $prefixDirsPsr4[$prefix] = (array) $paths;
        }
    }

    if ('\\' == $class[0]) {
        $class = substr($class, 1);
    }
    $ext = '.php';
    $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . $ext;
    $first = $class[0];
    if (isset($prefixLengthsPsr4[$first])) {
        foreach ($prefixLengthsPsr4[$first] as $prefix => $length) {
            if (0 === strpos($class, $prefix)) {
                foreach ($prefixDirsPsr4[$prefix] as $dir) {
                    if (file_exists($file = $dir . DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $length))) {
                        require $file;
                        return;
                    }
                }
            }
        }
    }

    // PSR-4 fallback dirs
    foreach ($fallbackDirsPsr4 as $dir) {
        if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr4)) {
            require $file;
            return;
        }
    }
}, true, true);
