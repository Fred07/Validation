# Documentation

## Mode
###逐項檢查模式(Step Mode)
- 預設為此模式，驗證過程中，驗證失敗將停止驗證流程，並回傳驗證失敗訊息
  Process of validation stop and return while first validation error occur. (Default mode)

###非逐項檢查模式
- 此模式將會跑完所有驗證，並統一記錄起來

## Functions
- initialize() `初始化`
- check( $rules = array(), $value, $error  ) `設定針對value的驗證規則以及對應錯誤碼`
- run() `執行驗證`

- setStepMode($boolean) `使用逐項檢查模式`
- getErrorCode($index) `取得錯誤碼`
- getErrorRule($index) `取得未通過的驗證方法`
- getReadableError() `取得所有錯誤訊息(非逐項模式)`


## Example
```php
$username = 'Five Wu';
$age = 1001;
$birthday = '2001-10-11';

$validation = new Validation();
$validation->check(array('required','minLen:1','maxLen:20'), $username, 'Invalid Username!');
$validation->check(array('required','isInteger','minLen:1','maxLen:3'), $age, 'Invalid Age!');
$validation->check(array('required','dateFormat'), $birthday, 'Invalid Date Format');
// more rules...

$error = $validation->run();
if ($error !== true  ) {
  echo $error;
}
```

####Result:
```
Invalid Age!
```
username則通過驗證
但 1001 超出驗證設定的範圍 (maxLen:3)

`run()`將會回傳驗證過程中，驗證失敗時所對應的錯誤碼，若驗證全部通過，將回傳 `true`

## Validation Rules
- required `not null`
- minLen `最小長度 ex. minLen:1`
- maxLen `最大長度 ex. maxLen:20`
- minNumber `最小值 ex. minNumber:0`
- maxNumber `最大值 ex. maxNumber:100`
- dateFormat `日期格式`
- timeFormat `時間格式`
- dateTimeFormat `日期時間格式`
- isNumeric `是否為數字( 包含字串型態, 浮點數型態 )`
- isAlphaNumeric `是否為一般字元組成, a-z,A-Z,0-9`
- isInteger `是否為整數 ( 包含字串型態 )`
- validIP `是否為正常IP`
- validUrl `是否為可連結的網域名`
- validEmail `是否為格式正常的email`
- regExp `是否符合自自訂的regular expression`

附屬參數皆用 `:` 來分隔

## Class to Extend
繼承Validation
自訂驗證方法，命名名稱checker_{RULE_NAME}
return boolean.
