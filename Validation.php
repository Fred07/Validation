<?php
/**
 * Created by PhpStorm.
 * User: Howard Wu
 * Desc: 批次驗證
 * Date: 2014/10/30
 * Time: 上午 10:28
 */

// 目前架構: 遇到第一個error就終止check
class Validation {

    private $rule_queue = array();      // 要驗證的任務排列

    private $error_code = '';           // 驗證失敗的錯誤碼
    private $error_rule = '';           // 驗證失敗的驗證名稱  ex. 'minLen' , 表示未通過最小值檢驗

    // constructor
    function Validation() {

    }

    //將檢查項目加入到 rule_queue
    public function check ( $rules = array(), $value, $error ) {

        array_push($this->rule_queue, array('rules'=>$rules, 'value'=>$value, 'error'=>$error) );
    }

    public function run() {

        foreach( $this->rule_queue AS $bundle ) {   //每一組check指令

            $value = $bundle['value'];
            $error = $bundle['error'];
            foreach( $bundle['rules'] AS $rule ) {  //每一個rule 驗證項目

                // 驗證失敗就中止驗證
                if ( !$this->switchToChecker($rule, $value) ) {
                    $this->error_code = $error;
                    return $this->error_code;
                }
            }
        }
        return true;    //全部驗證成功
    }

    // 配對驗證規則到對應的驗證function
    private function switchToChecker( $rule, $value )
    {
        // 某些特殊驗證由':' 來區格規則和值. ex. min_len:5
        // 切割出規則和驗證值
        $rule_couple = explode(':', $rule);

        switch ($rule_couple[0]) {
            case 'required':
                $this->error_rule = 'required';
                return $this->checker_required($value);
                break;
            case 'minLen':
                if ( isset($rule_couple[1]) ) {
                    $this->error_rule = 'minLen';
                    return $this->checker_minLen($value, $rule_couple[1]);
                }
                break;
            case 'maxLen':
                if ( isset($rule_couple[1]) ) {
                    $this->error_rule = 'maxLen';
                    return $this->checker_maxLen($value, $rule_couple[1]);
                }
                break;
            case 'dateFormat':
                $this->error_rule = 'dateFormat';
                return $this->checker_dateFormat($value);
                break;
            case 'timeFormat':
                $this->error_rule = 'timeFormat';
                return $this->checker_timeFormat($value);
                break;
            case 'isNumeric':
                $this->error_rule = 'isNumeric';
                return $this->checker_isNumeric($value);
                break;
            case 'isAlphaNumeric':
                $this->error_rule = 'isAlphaNumeric';
                return $this->checker_isAlphaNumeric($value);
                break;
            default:
                break;
        }
    }

    /*各種驗證模式*/

    public function checker_required( $value ) {
        return ( !empty($value) )?true:false;
    }

    public function checker_minLen( $value, $min ) {
        return ( mb_strlen($value, 'utf8') >= $min )?true:false;
    }

    public function checker_maxLen( $value, $max ) {
        return ( mb_strlen($value, 'utf8') <= $max )?true:false;
    }

    public function checker_dateFormat( $date ) {
        return ( preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date) )?true:false;
    }

    public function checker_timeFormat( $time ) {
        return ( preg_match('/^\d{2}:\d{2}:\d{2}$/', $time) )?true:false;
    }

    public function checker_isNumeric( $value ) {
        return ( is_numeric($value) )?true:false;
    }

    public function checker_isAlphaNumeric( $value ) {
        return ( ctype_alnum($value) )?true:false;
    }
}