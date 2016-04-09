<?php
namespace Fred;

/**
 * User: Howard Wu
 * Desc: change exception wording
 *       change comments format
 *       change variable naming
 * Version: 1.5
 * Date: 2016/04/10
 */
class Validation {

    /**
     * 要驗證的任務排列
     */
    protected $ruleQueue;

    /**
     * 驗證失敗的錯誤碼
     */
    protected $errorCode;

    /**
     * 驗證失敗的驗證名稱
     * ex.'minLen' , 表示未通過最小值檢驗
     */
    protected $errorRule;

    /**
     * 驗證結果
     */
    protected $validFlag;

    /**
     * 逐項檢查模式: 遇到錯誤立即中止檢查
     */
    protected $stepMode;

    const WARN_MISSING_PARAMETER = 'Missing parameter';
    const WARN_MISSING_ERR_CODE = 'Can not get any error code';
    const WARN_INVALID_RULE = 'Can not find validation method: ';
    const WARN_UNDEFINED = 'Undefined error!';

    /**
     * Constructor
     */
    function __construct() {
        $this->initialize();   
    }

    /**
     * Default value for class members
     */
    public function initialize() {

        $this->ruleQueue = array();
        $this->errorCode = array();
        $this->errorRule = array();
        $this->validFlag = true;
        $this->stepMode = true;
    }

    /**
     * Set step mode
     *
     * @param bool $bol
     */
    public function setStepMode( $bol ) {
        $this->stepMode = (( $bol )?true:false);
    }

    /**
     * Get error code
     *
     * @param int $index
     * @return string
     * @throws Exception
     */
    public function getErrorCode( $index = 0 ) {
        if ( $index AND isset($this->errorCode[$index]) ) {
            return $this->errorCode[$index];
        }
        return $this->errorCode;
    }

    /**
     * Get error rule
     * 
     * @param int $index
     * @return string
     */
    public function getErrorRule( $index = '' ) {
        if ( $index AND isset($this->errorRule[$index]) ) {
            return $this->errorRule[$index];
        }
        return $this->errorRule;
    }

    /**
     * Get validation pass or fail
     *
     * @return bool
     */
    public function isValid() {
        return $this->validFlag;
    }

    /**
     * Is step mode or not
     *
     * @return bool
     */
    public function isStepMode() {
        return $this->stepMode;
    }

    /**
     * Get errorCode and errorRule in format
     *
     * @return string
     * @throws Exception
     */
    public function getReadableError() {
        if ( !$this->validFlag ) {
            if ( count($this->errorCode) === count($this->errorRule) ) {

                $result = array();
                foreach( $this->errorCode AS $index => $err_code ) {
                    array_push($result,  'errorCode: ' . $err_code . ' errorRule: ' . $this->errorRule[$index] . PHP_EOL);
                }
                return $result;

            } else {
                throw new Exception(self::WARN_UNDEFINED);
            }
        } else {
            // validation success, do nothing!
        }
    }

    /**
     * Add rule into ruleQueue
     * 
     * @param array $rules checking rules
     * @param mixed $value value to check
     */
    public function check( $rules = array(), $value, $error ) {

        array_push($this->ruleQueue, array('rules'=>$rules, 'value'=>$value, 'error'=>$error) );
    }

    /**
     * Execute validation
     *
     * @return int/bool
     */
    public function run() {

        foreach( $this->ruleQueue AS $bundle ) {

            $value = $bundle['value'];
            $error = $bundle['error'];
            foreach( $bundle['rules'] AS $rule ) {

                // 驗證沒過, 紀錄errorCode, errorRule
                // stepMode 模式下, 直接返回errorCode
                if ( !$this->validate($rule, $value) ) {
                    array_push($this->errorCode, $error);
                    array_push($this->errorRule, $rule);
                    $this->validFlag = true;
                    if ($this->isStepMode()) {
                        return $error;
                    }
                }
            }
        }

        // 回傳驗證結果, 如果有errorCode則回傳, 沒有則回傳flag
        if ( isset($this->errorCode[0]) ) {
            return $this->errorCode[0];
        } else {
            return $this->isValid();
        }

    }

    /**
     * Dispatcher
     * 呼叫對應的驗證function
     *
     * @param string $rule
     * @param mixed $value
     *
     * @return bool
     * @throws Exception
     */
    protected function validate( $rule, $value ) {

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

    /**
     * Required (empty or not)
     * @param mixed $value
     * @param array $param
     *
     * @return bool
     */
    protected function checker_required( $value, $param ) {
        return ( empty($value) )?false:true;
    }

    /**
     * Equal (string)
     * @param mixed $value
     * @param array $param
     *
     * @return bool
     * @throws Exception
     */
    protected function checker_equalsTo( $value, $param ) {
        if (!isset($param[1])) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        $comp_value = $param[1];
        return ( $value === $comp_value )?true:false;
    }

    /**
     * Minimum length
     * @param mixed $value
     * @param array $param
     *
     * @return bool
     * @throws Exception
     */
    protected function checker_minLen( $value, $param ) {
        if (!isset($param[1])) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        $min = $param[1];
        return ( mb_strlen($value, 'utf8') >= $min )?true:false;
    }

    /**
     * Maximum length
     * @param mixed $value
     * @param array $param
     *
     * @return bool
     * @throws Exception
     */
    protected function checker_maxLen( $value, $param ) {
        if (!isset($param[1])) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        $max = $param[1];
        return ( mb_strlen($value, 'utf8') <= $max )?true:false;
    }

    /**
     * length equals to
     * @param mixed $value
     * @param array $param
     *
     * @return bool
     * @throws Exception
     */
    protected function checker_len( $value, $param ) {
        if (!isset($param[1])) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        $len = $param[1];
        return ( mb_strlen($value) == $len )?true:false;
    }

    /**
     * Minimum value
     * @param mixed $value
     * @param array $param
     *
     * @return bool
     * @throws Exception
     */
    protected function checker_minNumber($value, $param) {
        if (!isset($param[1])) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        $min = $param[1];
        return ( (int)$value >= $min)?true:false;
    }

    /**
     * Maxinum value
     *
     * @param mixed $value
     * @param array $param
     *
     * @return bool
     * @throws Exception
     */
    protected function checker_maxNumber($value, $param) {
        if (!isset($param[1])) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        $max = $param[1];
        return ( (int)$value <= $max )?true:false;
    }

    /**
     * Date format
     * ex. 2014/10/15, 2014-05-34
     *
     * @param string $date
     * @param array $param
     *
     * @return bool
     */
    protected function checker_dateFormat( $date, $param ) {

        return ( preg_match('/^\d{4}[\/\-](0[1-9]|1[0-2])[\/\-](0[1-9]|[1-2][0-9]|3[0-1])$/', $date) )?true:false;
    }

    /**
     * Time format
     *
     * @param string $time
     * @param array $param
     *
     * @return bool
     */
    protected function checker_timeFormat( $time, $param ) {
        return ( preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $time) )?true:false;
    }

    /**
     * Datetime format
     * @param string $dateTime
     * @param array $param
     *
     * @return bool
     */
    protected function checker_dateTimeFormat( $dateTime, $param ) {

        return ( preg_match('/^\d{4}[\/\-](0[1-9]|1[0-2])[\/\-](0[1-9]|[1-2][0-9]|3[0-1])\s(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $dateTime) )?true:false;
    }

    // 數字, 含字串型態, 含浮點數
    /**
     * Numeric (including string and float)
     *
     * @param mixed $value
     * @param array $param
     *
     * @return bool
     */
    protected function checker_isNumeric( $value, $param ) {
        return ( is_numeric($value) )?true:false;
    }

    /**
     * Alpha and numeric
     *
     * @param mixed $value
     * @param array $param
     *
     * @return bool
     */
    protected function checker_isAlphaNumeric( $value, $param ) {
        return ( ctype_alnum($value) )?true:false;
    }

    /**
     * Integer (including string)
     *
     * @param mixed $value
     * @param array $param
     *
     * @return bool
     */
    protected function checker_isInteger( $value, $param ) {
        return ( filter_var($value, FILTER_VALIDATE_INT) )?true:false;
    }

    /**
     * IP
     *
     * @param string $ip
     * @param array $param
     *
     * @return bool
     */
    protected function checker_validIP( $ip, $param ) {
        return ( filter_var($ip, FILTER_VALIDATE_IP) )?true:false;
    }

    /**
     * Url, without 'http://'
     *
     * @param string $value
     * @param array $param
     *
     * @return bool
     */
    protected function checker_validUrl( $url, $param ) {
        return ( checkdnsrr($url) !== false )?true:false;
    }

    /**
     * Email
     *
     * @param string $value
     * @param array $param
     *
     * @return bool
     */
    protected function checker_validEmail( $email, $param ) {
        return ( filter_var($email, FILTER_VALIDATE_EMAIL) )?true:false;
    }

    /**
     * Validate with Regexp.
     *     ex. $param[1]: regular expression
     *
     * @param mixed $value
     * @param array $param
     *
     * @return bool
     * @throws Exception
     */
    protected function checker_regExp( $value, $param ) {
        if ( !isset($param[1]) ) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }
        $regExp = $param[1];
        return ( preg_match($regExp, $value) )?true:false;
    }

    // 
    /**
     * Contain string
     *     ex. $param[1]: string to search
     *         $param[2]: case sensitive or not
     *
     * @param mixed $value
     * @param array $param
     *
     * @return bool
     * @throws Exception
     */
    protected function checker_contains( $value, $param ) {

        if ( !isset($param[1]) OR empty($param[1]) ) {
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

    /**
     * Options
     * ex. $param[n]: option value
     * 
     * @param mixed $value
     * @param array $param
     *
     * @return bool
     * @throws Exception
     */
    protected function checker_choice( $value, $param ) {
        if ( !isset($param[1]) ) {
            throw new Exception(self::WARN_MISSING_PARAMETER);
        }

        for( $i=1; $i <= count($param) - 1 ;$i++ ) {
            if ( $param[$i] == $value ) {
                return true;
            }
        }
        return false;
    }
}