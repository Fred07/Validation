# Example

$validation = new Validation();

```php
$validation->check(array('required','minLen:1','maxLen:20'), $username, 'Invalid Username!');
$validation->check(array('required','isInteger','minLen:1','maxLen:3'), $age, 'Invalid Age');
```

## Validation Method
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

* 附屬參數皆用 `:` 來分隔
