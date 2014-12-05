<?php
/**
 * Created by PhpStorm.
 * User: Howard Wu
 * Desc: 批次驗證
 *       經由繼承擴充增加驗證方法
 *       遵守命名規則( checker_規則名稱 )
 * Version: 1.3
 * Date: 2014/10/30
 * Time: 上午 10:28
 */

class Validation {

    protected $rule_queue;        // 要驗證的任務排列
    protected $error_code;        // 驗證失敗的錯誤碼
    protected $error_rule;        // 驗證失敗的驗證名稱    ex.'minLen' , 表示未通過最小值檢驗
    protected $valid_flag;        // 驗證狀態, true 正常
    protected $stepMode;          // 逐項檢查模式: true遇到錯誤就中止檢查, false全部rule檢查

    const WARN_MISSING_PARAMETER = 'Missing parameter';
    const WARN_MISSING_ERR_CODE = 'Can not get any error code';
    const WARN_INVALID_RULE = 'Can not find validation method: ';
    const WARN_UNDEFINED = 'Something wrong!';

    // constructor
    function Validation() {
        $this->initialize();
    }

    // Default value for member variable
    public function initialize() {

        $this->rule_queue = array();
        $this->error_code = array();
        $this->error_rule = array();
        $this->valid_flag = true;
        $this->stepMode = true;
    }

    // Setter
    // 改變模式
    public function setStepMode( $bol ) {
        $this->stepMode = (( $bol )?true:false);
    }

    // Getter
    // @param $index int: 選擇特定error_code
    public function getErrorCode( $index = '' ) {
        if ( $index AND isset($this->error_code[$index]) ) {
            return $this->error_code[$index];
        }
        return $this->error_code;
    }

    // Getter
    // @param $index int: 選擇特定error_rule
    public function getErrorRule( $index = '' ) {
        if ( $index AND isset($this->error_rule[$index]) ) {
            return $this->error_rule[$index];
        }
        return $this->error_rule;
    }

    // Get validation pass or fail
    public function isAllValid() {
        return $this->valid_flag;
    }

    // Get error_code and error_rule in format
    public function getReadableError() {
        if ( !$this->valid_flag ) {
            if ( sizeof($this->error_code) === sizeof($this->error_rule) ) {

                $result = array();
                foreach( $this->error_code AS $index => $err_code ) {
                    array_push($result,  'error_code: ' . $err_code . ' error_rule: ' . $this->error_rule[$index] . PHP_EOL);
                }
                return $result;

            } else {
                throw new Exception(self::WARN_UNDEFINED);
            }
        } else {
            // no error, do nothing!!
        }
    }

    // 加入檢查規則項目到 rule_queue
    public function check( $rules = array(), $value, $error ) {

        array_push($this->rule_queue, array('rules'=>$rules, 'value'=>$value, 'error'=>$error) );
    }

    // 執行檢查
    public function run() {

        foreach( $this->rule_queue AS $bundle ) {   //每一組 check指令

            $value = $bundle['value'];
            $error = $bundle['error'];
            foreach( $bundle['rules'] AS $rule ) {  //每一個 rule驗證項目

                // 驗證沒過, 紀錄error_code, error_rule
                // stepMode 模式下, 直接返回error_code
                if ( !$this->switchToChecker($rule, $value) ) {
                    array_push($this->error_code, $error);
                    array_push($this->error_rule, $rule);
                    $this->valid_flag = false;
                    if ($this->stepMode) {
                        return $error;
                    }
                }
            }
        }

        // 回傳驗證結果, 如果有error_code則回傳, 沒有則回傳flag
        if ( isset($this->error_code[0]) ) {
            return $this->error_code[0];
        } else {
            return $this->isAllValid();
        }

    }

    // 分配器
    // 呼叫對應的驗證function
    protected function switchToChecker( $rule, $value ) {

        // 某些特殊驗證由 ':' 來區格驗證規則和驗證值. ex. minLen:5
        // 切割出規則和驗證值, $rule_couple[0]:驗證規則,  $rule_couple[1]:驗證值, 類推.
        $rule_couple = explode(':', $rule);
        $method = 'checker_' . $rule_couple[0];

        // 呼叫前確認方法存在
        if ( method_exists($this, $method) ) {      // __CLASS__ 改為 $this, 否則子類別無法偵測到
            return $this->$method( $value, $rule_couple );
        } else {
            throw new Exception(self::WARN_INVALID_RULE . $rule_couple[0]);
        }
    }



    // 各種驗證模式
    // 可繼承類別擴充新的驗證方法
    // checker_ 開頭
    // @param $value: 將被驗證的值
    // @param $param array: 參數包裹, [0]=>驗證方法, [1]=>驗證參數1, [2]=>驗證參數2, etc...
    // @return boolean: 是否正確, true:正確

    // Required (not empty)
    protected function checker_required( $value, $param ) {
        //return ( is_null($value) )?false:true;
        return ( empty($value) )?false:true;
    }

    // Equal (string)
    protected function checker_equalsTo( $value, $param ) {
        if (!isset($param[1])) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        $comp_value = $param[1];
        return ( $value === $comp_value )?true:false;
    }

    // 最小長度
    protected function checker_minLen( $value, $param ) {
        if (!isset($param[1])) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        $min = $param[1];
        return ( mb_strlen($value, 'utf8') >= $min )?true:false;
    }

    // 最大長度
    protected function checker_maxLen( $value, $param ) {
        if (!isset($param[1])) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        $max = $param[1];
        return ( mb_strlen($value, 'utf8') <= $max )?true:false;
    }

    protected function checker_len( $value, $param ) {
        if (!isset($param[1])) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        $len = $param[1];
        return ( mb_strlen($value) == $len )?true:false;
    }

    // 最小值
    protected function checker_minNumber($value, $param) {
        if (!isset($param[1])) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        $min = $param[1];
        return ( (int)$value >= $min)?true:false;
    }

    // 最大值
    protected function checker_maxNumber($value, $param) {
        if (!isset($param[1])) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        $max = $param[1];
        return ( (int)$value <= $max )?true:false;
    }

    // ex. 2014/10/15, 2014-05-34
    protected function checker_dateFormat( $date, $param ) {
        //return ( preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date) )?true:false;
        return ( preg_match('/^\d{4}[\/\-](0[1-9]|1[0-2])[\/\-](0[1-9]|[1-2][0-9]|3[0-1])$/', $date) )?true:false;
    }

    protected function checker_timeFormat( $time, $param ) {
        //return ( preg_match('/^\d{2}:\d{2}:\d{2}$/', $time) )?true:false;
        return ( preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $time) )?true:false;
    }

    protected function checker_dateTimeFormat( $dateTime, $param ) {
        return ( preg_match('/^\d{4}[\/\-](0[1-9]|1[0-2])[\/\-](0[1-9]|[1-2][0-9]|3[0-1])\s(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $dateTime) )?true:false;
    }

    // 數字, 含字串型態, 含浮點數
    protected function checker_isNumeric( $value, $param ) {
        return ( is_numeric($value) )?true:false;
    }

    protected function checker_isAlphaNumeric( $value, $param ) {
        return ( ctype_alnum($value) )?true:false;
    }

    // 整數, 含字串型態
    protected function checker_isInteger( $value, $param ) {
        return ( filter_var($value, FILTER_VALIDATE_INT) )?true:false;
    }

    // IP
    protected function checker_validIP( $ip, $param ) {
        return ( filter_var($ip, FILTER_VALIDATE_IP) )?true:false;
    }

    // Url, without http://
    protected function checker_validUrl( $url, $param ) {
        return ( checkdnsrr($url) !== false )?true:false;
    }

    // Email
    protected function checker_validEmail( $email, $param ) {
        return ( filter_var($email, FILTER_VALIDATE_EMAIL) )?true:false;
    }

    // Regular Expression
    // @param[1] String: regular expression
    protected function checker_regExp( $value, $param ) {
        if ( !isset($param[1]) ) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        $regExp = $param[1];
        return ( preg_match($regExp, $value) )?true:false;
    }

    // 包含字串
    // @param[1] String: 要搜尋的字串
    // @param[2] boolean:大小寫偵測
    protected function checker_contains( $value, $param ) {

        if ( !isset($param[1]) ) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        if ( isset($param[2]) ) {
            $sensitive = $param[2];
        } else {
            $sensitive = false;
        }

        $needle = $param[1];

        if ( $sensitive ) {
            return ( strpos($value, $needle) !== false )?true:false;        // 使用 strpos()
        } else {
            return ( stripos($value, $needle) !== false )?true:false;       // 使用 stripos()
        }
    }

    // 是否在選項範圍內
    // @param[n]: String: 選項值
    protected function checker_choice( $value, $param ) {
        if ( !isset($param[1]) ) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }

        for( $i=1; $i <= sizeof($param) - 1 ;$i++ ) {
            if ( $param[$i] == $value ) {
                return true;
            }
        }
        return false;
    }
}