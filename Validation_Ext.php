<?php
/**
 * Created by PhpStorm.
 * User: Hiiir
 * Desc: Validation 子類別 範例
 * Date: 2014/11/21
 * Time: 下午 05:14
 */

require_once(dirname(__FILE__).'/Validation.php');
class Validation_Ext extends Validation{

    function __construct() {
        parent::__construct();
    }

    protected function checker_test($value, $param) {
        return ( $value === 'test' )?true:false;
    }
}