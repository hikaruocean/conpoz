<?php
set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
    throw new \Exception('[Conpoz Catch Error] ' . $errno . ' ' . $errstr . ' ' . $errfile . ' ' . $errline);
}, E_ALL);