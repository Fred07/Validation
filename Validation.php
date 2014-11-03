<?php
/**
 * Created by PhpStorm.
 * User: Howard Wu
 * Desc: 批次驗證
 * Date: 2014/10/30
 * Time: 上午 10:28
 */

// 直接擴充驗證區function, 遵守命名規則(checker_), 就可以直接使用
//Todo: error_rule 暫時拿掉功能 實作流程無法確定
//Todo: setMode 中途改變該怎麼處理
class Validation {

    private $rule_queue;        // 要驗證的任務排列
    private $error_code;        // 驗證失敗的錯誤碼
    private $error_rule;        // 驗證失敗的驗證名稱    ex.'minLen' , 表示未通過最小值檢驗
    private $valid_flag;        // 驗證狀態, true 正常
    private $stepMode;          // 逐項檢查模式: true遇到錯誤就中止檢查, false全部rule檢查

    const WARN_MISSING_PARAMETER = 'missing parameter';

    // constructor
    function Validation() {
        $this->initialize();
    }

    // Restart Validation Task
    public function initialize() {

        // 新開始一個驗證需要作的事情
        // 清空rule_queue
        // 清空error_code
        // 清空error_rule
        // 回復valid_flag

        $this->rule_queue = array();
        $this->error_code = array();
        $this->error_rule = array();
        $this->valid_flag = true;
        $this->stepMode = true;
    }

    // 改變模式
    public function stepMode( $bol ) {
        $this->stepMode = (( $bol )?true:false);
    }

    // Getter
    public function getErrorCode( $index = '' ) {
        if ( $index AND isset($this->error_code[$index]) ) {
            return $this->error_code[$index];
        }
        return $this->error_code;
    }

    // Getter
    public function getErrorRule() {
        return $this->error_rule;
    }

    // not yet!! 最後輸出的格式
    // Get error_code and error_rule in format
    public function getReadableError() {
        if ( !$this->valid_flag ) {
            $result = array();
            foreach( $this->error_code AS $index => $err_code ) {
                array_push($result,  'error_code: ' . $err_code);
            }
            return $result;
        } else {
            //no error, do nothing!!
        }
    }

    // 加入檢查規則項目到 rule_queue
    public function check ( $rules = array(), $value, $error ) {

        array_push($this->rule_queue, array('rules'=>$rules, 'value'=>$value, 'error'=>$error) );
    }

    // 執行檢查
    public function run() {

        foreach( $this->rule_queue AS $bundle ) {   //每一組 check指令

            $value = $bundle['value'];
            $error = $bundle['error'];
            foreach( $bundle['rules'] AS $rule ) {  //每一個 rule驗證項目

                // 驗證沒過, 寫入error_code
                // stepMode 模式下, 直接返回
                if ( !$this->switchToChecker($rule, $value) ) {
                    array_push($this->error_code, $error);
                    $this->valid_flag = false;
                    if ($this->stepMode) {
                        return $error;
                    }
                }
            }
        }
        return $this->valid_flag;
    }

    // 分配器
    // 呼叫對應的驗證function
    private function switchToChecker( $rule, $value )
    {
        // 某些特殊驗證由 ':' 來區格驗證規則和驗證值. ex. minLen:5
        // 切割出規則和驗證值, $rule_couple[0]:驗證規則,  $rule_couple[1]:驗證值, 類推.
        $rule_couple = explode(':', $rule);
        $method = 'checker_' . $rule_couple[0];

        // 呼叫前確認方法存在
        if ( method_exists(__CLASS__, $method) ) {
            return $this->$method( $value, $rule_couple );
        } else {
            throw new Exception('can not find validation method: ' . $rule_couple[0]);
        }
    }


    // 各種驗證模式
    // 可繼承類別擴充新的驗證方法
    // checker_ 開頭
    // @param $value: 將被驗證的值
    // @param $param array: 參數包裹, [0]=>驗證方法, [1]=>驗證參數1, [2]=>驗證參數2, etc...
    // @return boolean: 是否正確, true:正確

    protected function checker_required( $value, $param ) {
        return ( is_null($value) )?false:true;
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

    protected function checker_dateFormat( $date, $param ) {
        //return ( preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date) )?true:false;
        return ( preg_match('/^\d{4}\/(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])$/', $date) )?true:false;
    }

    protected function checker_timeFormat( $time, $param ) {
        //return ( preg_match('/^\d{2}:\d{2}:\d{2}$/', $time) )?true:false;
        return ( preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $time) )?true:false;
    }

    protected function checker_dateTimeFormat( $dateTime, $param ) {
        return ( preg_match('/^\d{4}\/(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\s(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $dateTime) )?true:false;
    }

    protected function checker_isNumeric( $value, $param ) {
        return ( is_numeric($value) )?true:false;
    }

    protected function checker_isAlphaNumeric( $value, $param ) {
        return ( ctype_alnum($value) )?true:false;
    }
}