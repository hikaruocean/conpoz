<?php
namespace Conpoz\Core\Lib\Util;

class Validator
{
    public static function valid($ruleObj, &$dataAry) 
    {
        $msgAry = array();
        $rulesAry = array();
        $ruleErrBreak;
        $columnErrBreak;
        $choiceField = array();
        $errorCount = 0;
        
        $ruleErrBreak = $ruleObj->getRuleErrBreak();
        $columnErrBreak = $ruleObj->getColumnErrBreak();
        $choiceField = $ruleObj->getChoice();
        if (count($choiceField) == 0) {
            $rulesAry = get_object_vars($ruleObj);
        } else {
            foreach ($choiceField as $v) {
                if (isset($ruleObj->{$v})) {
                    $rulesAry[$v] = $ruleObj->{$v};
                }
            }
        }

        if (count($rulesAry) == 0) {
            return false;
        }
        foreach ($rulesAry as $k => $v) {
            $errorCount = 0;
            if ($columnErrBreak && count($msgAry) > 0) {
                return $msgAry;
            }
            if (!isset($v["required"]) && ( !isset($dataAry[$k]) || $dataAry[$k] === "" ) ) { // 不是 "required" 且未填值的欄位，就不需檢查了直接跳過
                continue;
            } else { //驗證 "required" 的欄位 或 不是 "required" 但有填值的欄位
                if (!isset($dataAry[$k])) {
                    $dataAry[$k] = null;
                }
                foreach ($v as $rstr => $rmsg) {
                    $r = explode(":", $rstr, 2);
                    
                    switch ($r[0]) {
                        /**
                        * no user assign rule value
                        * */
                        case "required":
                            if (is_null($dataAry[$k]) || $dataAry[$k] === "" ){
                                $msgAry[] = $rmsg;
                                $errorCount++;
                            }
                            break;
                        case "boolean":
                            if ($dataAry[$k] !== "true" && $dataAry[$k] !== "false" ) {
                                $msgAry[] = $rmsg;
                                $errorCount++;
                            }
                            break;
                        case "number":
                            //if (preg_match("/^(-?[0-9]+(\.[0-9]+)?)?$/",dataAry[k]) === 0) {
                            if (preg_match("/^-?[0-9]+(\.[0-9]+)?$/", $dataAry[$k]) === 0) {
                                $msgAry[] = $rmsg;
                                $errorCount++;
                            }
                            break;
                        case "numeric":
                            if (preg_match("/^[0-9]+$/", $dataAry[$k]) === 0) {
                                $msgAry[] = $rmsg;
                                $errorCount++;
                            }
                            break;
                        case "alpha-numeric":
                            //if (preg_match("/^([A-Za-z0-9]*)$/",dataAry[k]) === 0) {
                            if (preg_match("/^[A-Za-z0-9]+$/", $dataAry[$k]) === 0) {
                                $msgAry[] = $rmsg;
                                $errorCount++;
                            }
                            break;
                        case "date":
                            $tmpData = explode("-", $dataAry[$k]);
                            if (count($tmpData) < 2 || count($tmpData) > 3) {
                                $msgAry[] = $rmsg;
                                $errorCount++;
                                break;
                            }
                            if (count($tmpData) === 2) {
                                if(!checkdate($tmpData[1], 1, $tmpData[0])) {
                                    $msgAry[] = $rmsg;
                                    $errorCount++;
                                }
                            } else {
                                if (!checkdate($tmpData[1], $tmpData[2], $tmpData[0]) ) {
                                    $msgAry[] = $rmsg;
                                    $errorCount++;
                                }
                            }
                            break;
                        case "date-time":
                            $tmpData = preg_split("/[-\s:]+/", $dataAry[$k]);
                            if (count($tmpData) !== 6) {
                                $msgAry[] = $rmsg;
                                $errorCount++;
                                break;
                            }
                            if (is_bool(strtotime($dataAry[$k]))){
                                $msgAry[] = $rmsg;
                                $errorCount++;
                            }
                            break;
                        case "ip":
                            if (!filter_var($dataAry[$k], FILTER_VALIDATE_IP)) {
                                $msgAry[] = $rmsg;
                                $errorCount++;
                            }
                            break;
                        case "email":
                            if (!filter_var($dataAry[$k], FILTER_VALIDATE_EMAIL)) {
                                $msgAry[] = $rmsg;
                                $errorCount++;
                            }
                            break;
                        case "tel":
                            if (preg_match("/^(\([0-9]+\))?[0-9]+(-[0-9]+)*(#[0-9]+)?$/", $dataAry[$k]) === 0) {
                                $msgAry[] = $rmsg;
                                $errorCount++;
                            }
                            break;
                        /**
                        * user assign rule value
                        * */
                        case "max-length":
                            if (!isset($r[1])) {
                                break;
                            }
                            if (mb_strlen($dataAry[$k],"UTF-8") > $r[1]) {
                                $msgAry[] = $rmsg;
                                $errorCount++;
                            }
                            break;
                        case "min-length":
                            if (!isset($r[1])) {
                                break;
                            }
                            if (mb_strlen($dataAry[$k],"UTF-8") < $r[1]) {
                                $msgAry[] = $rmsg;
                                $errorCount++;
                            }
                            break;
                        case "compare-with":
                            if (!isset($r[1])) {
                                break;
                            }
                            if (!isset($dataAry[$r[1]])) {
                                $dataAry[$r[1]] = null;
                            }
                            if ($dataAry[$k] !== $dataAry[$r[1]]) {
                                $msgAry[] = $rmsg;
                                $errorCount++;
                            }
                            break;
                        case "regex-rule":
                            if (!isset($r[1])){
                                $r[1] = "/.*/";
                            }
                            if (preg_match($r[1], $dataAry[$k]) === 0) {
                                $msgAry[] = $rmsg;
                                $errorCount++;
                            }
                            break;
                    }
                    if ($ruleErrBreak && $errorCount > 0) {
                        break;
                    }
                }
            }
        }
        return $msgAry;
    }
}   