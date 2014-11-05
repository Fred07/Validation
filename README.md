# Documentation

## Functions
- initialize() `初始化`
- check( $rules = array(), $value, $error  ) `設定針對value的驗證規則以及對應錯誤碼`
- run() `執行驗證`

## Example
```php
$validation = new Validation();
$validation->check(array('required','minLen:1','maxLen:20'), $username, 'Invalid Username!');
$validation->check(array('required','isInteger','minLen:1','maxLen:3'), $age, 'Invalid Age');
// more rules...

$error = $validation->run();
if ($error !== true  ) {
  echo $error;
}
```
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

附屬參數皆用 `:` 來分隔
