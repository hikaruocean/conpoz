<?php 
namespace Conpoz\Core\Lib\Util\Validator;
abstract class ValidateRule
{

    private $ruleErrBreak = true;
    private $columnErrBreak = false;
    private $choice = array();
    public function rule2string($key) {
        return $this->rts($key);
    }
    public function rts ($key) {
        if (isset($this->{$key})) {
            $str = "";
            foreach ($this->{$key} as $k => $v) {
                if (!empty($str)) {
                    $str .= " ";
                }
                $str .= $k;
            }
            return $str;
        }
        return false;
    } 
    public function rta ($key) 
    {
        if (isset($this->{$key})) {
            $ruleStr = "";
            $valStr = "";
            $msgStr = "";
            $regCount = 1;
            foreach ($this->{$key} as $k => $v)  {
                $r = explode(":", $k, 2);
                switch ($r[0]) {
                    case "regex-rule":
                        $r[0] = $r[0] . "-" . $regCount;
                        $regCount++;
                        break;
                    case "function":
                        continue;
                        break;
                    default:
                        break;
                }
                if (!empty($ruleStr)) {
                    $ruleStr .= " ";
                }
                $ruleStr .= $r[0];
                if (isset($r[1])) {
                    $valStr .= "data-" . $r[0] . "=\"" . htmlspecialchars($r[1], ENT_QUOTES) . "\" ";
                }
                $msgStr .= "data-err-msg-" . $r[0] . "=\"" . htmlspecialchars($v, ENT_QUOTES) . "\" ";
            }
            return "data-validation-rules=\"" . $ruleStr . "\" " . $valStr . $msgStr;
        }
        return false;
    }

    public function setRuleErrBreak($val) 
    {
        $this->ruleErrBreak =  $val;
        return $this;
    }

    public function getRuleErrBreak() 
    {
        return $this->ruleErrBreak;
    }

    public function setColumnErrBreak($val)
    {
        $this->columnErrBreak = $val;
        return $this;
    }

    public function getColumnErrBreak() 
    {
        return $this->columnErrBreak;
    }

    public function setChoice(array $cAry = array()) 
    {
        $this->choice = $cAry;
        return $this;
    }

    public function getChoice() 
    {
        return $this->choice;
    }

    public function autoChoice(array $dataAry = array()) 
    {
        if (!empty($dataAry)) {
            foreach($dataAry as $k => $v) {
                if (isset($this->{$k})) {
                    $choiceAry[] = $k;
                }
            }
            $this->setChoice($choiceAry);
        }
        return $this;
    }
}   