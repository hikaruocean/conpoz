<?php
// set_error_handler(function($errno, $errstr, $errfile, $errline) {
//     if (strpos($errstr, 'MySQL server has gone away') !== false || strpos($errstr, "Error while sending QUERY packet") !== false || strpos($errstr, 'Packets out of order') !== false) {
//         throw new \Conpoz\Core\Lib\Db\DBQuery\Exception("Mysql server has gone away", 2006, $errfile, $errline);
//     }
//     throw new \Exception('[Conpoz Catch Error] ' . $errno . ' ' . $errstr . ' ' . $errfile . ' ' . $errline);
// });
