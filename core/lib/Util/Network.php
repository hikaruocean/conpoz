<?php
namespace Conpoz\Core\Lib\Util;

class Network
{
    public static $userAgent = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.220 Safari/535.1";
    public static function httpPost($url, $params = null, $headerAry = null, $cookie = null, $cookieFilePath = null, $referer = null, $proxy = null, $setOptArray = array()) {
        $pstring = '';
        if (is_array($params)) {
            $z = 0;
            foreach ($params as $k => $v) {
                if (++$z > 1)
                $pstring .= "&";
                $pstring .= $k . "=" . $v;
            }
        }else {
            $pstring = $params;
        }
        $cstring = '';
        if (!$cookieFilePath) {
            if (is_array($cookie)) {
                $z = 0;
                foreach ($cookie as $k => $v) {
                    if (++$z > 1)
                    $cstring .= "&";
                    $cstring .= $k . "=" . $v;
                }
            }else {
                $cstring = $cookie;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
        if ($pstring) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $pstring);
        }
        if (is_array($headerAry)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerAry);
        }
        if ($cstring) {
            curl_setopt($ch, CURLOPT_COOKIE, $cstring);
        }
        if ($cookieFilePath) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFilePath);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFilePath);
        }
        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        if (!empty($setOptArray)) {
            curl_setopt_array($ch, $setOptArray);
        }
        $content = curl_exec($ch);
        $returnAry = array('httpCode' => curl_getinfo($ch, CURLINFO_HTTP_CODE));
        if ($content === false) {
            $returnAry['errorNo'] = curl_errno($ch);
            $returnAry['errorMsg'] = curl_error($ch);
        } else {
            $returnAry['errorNo'] = null;
            $returnAry['errorMsg'] = null;
        }
        $returnAry['result'] = $content;
        curl_close($ch);
        return $returnAry;
    }

    public static function httpGet($url, $headerAry = null, $cookie = null, $cookieFilePath = null, $referer = null, $proxy = null, $setOptArray = array()) {
        $cstring = '';
        if (!$cookieFilePath) {
            if (is_array($cookie)) {
                $z = 0;
                foreach ($cookie as $k => $v) {
                    if (++$z > 1)
                    $cstring .= "&";
                    $cstring .= $k . "=" . $v;
                }
            }else {
                $cstring = $cookie;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
        if (is_array($headerAry)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerAry);
        }
        if ($cstring) {
            curl_setopt($ch, CURLOPT_COOKIE, $cstring);
        }
        if ($cookieFilePath) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFilePath);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFilePath);
        }
        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        if (!empty($setOptArray)) {
            curl_setopt_array($ch, $setOptArray);
        }
        $content = curl_exec($ch);
        $returnAry = array('httpCode' => curl_getinfo($ch, CURLINFO_HTTP_CODE));
        if ($content === false) {
            $returnAry['errorNo'] = curl_errno($ch);
            $returnAry['errorMsg'] = curl_error($ch);
        } else {
            $returnAry['errorNo'] = null;
            $returnAry['errorMsg'] = null;
        }
        $returnAry['result'] = $content;
        curl_close($ch);
        return $returnAry;
    }

    public static function httpRequest($method, $url, $params = null, $headerAry = null, $cookie = null, $cookieFilePath = null, $referer = null, $proxy = null, $setOptArray = array()) {
        $pstring = '';
        if (is_array($params)) {
            $z = 0;
            foreach ($params as $k => $v) {
                if (++$z > 1)
                $pstring .= "&";
                $pstring .= $k . "=" . $v;
            }
        }else {
            $pstring = $params;
        }
        $cstring = '';
        if (!$cookieFilePath) {
            if (is_array($cookie)) {
                $z = 0;
                foreach ($cookie as $k => $v) {
                    if (++$z > 1)
                    $cstring .= "&";
                    $cstring .= $k . "=" . $v;
                }
            }else {
                $cstring = $cookie;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
        if ($method === 'HEAD') {
            curl_setopt($ch, CURLOPT_NOBODY, true);
        }
        if ($pstring) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $pstring);
        }
        if (is_array($headerAry)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerAry);
        }
        if ($cstring) {
            curl_setopt($ch, CURLOPT_COOKIE, $cstring);
        }
        if ($cookieFilePath) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFilePath);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFilePath);
        }
        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        if (!empty($setOptArray)) {
            curl_setopt_array($ch, $setOptArray);
        }
        $content = curl_exec($ch);
        $returnAry = array('httpCode' => curl_getinfo($ch, CURLINFO_HTTP_CODE));
        if ($content === false) {
            $returnAry['errorNo'] = curl_errno($ch);
            $returnAry['errorMsg'] = curl_error($ch);
        } else {
            $returnAry['errorNo'] = null;
            $returnAry['errorMsg'] = null;
        }
        $returnAry['result'] = $content;
        curl_close($ch);
        return $returnAry;
    }


}
