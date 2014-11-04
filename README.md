# Example

$validation = new Validation();

```
$validation->check(array('required','minLen:1','maxLen:20'), $username, 'Invalid Username!');
$validation->check(array('required','isInteger','minLen:1','maxLen:3'), $age, 'Invalid Age');
```

## Validation Method
//...
