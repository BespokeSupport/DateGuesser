# Guess the format of a date string for Carbon

Allows the creation of a Carbon object when you are unsure of the format presented

```
$time = '2017-12-31';
// Carbon object
$carbonObj = DateGuesser::create($time);
```

### Allow new formats
```
DateGuesser::$attemptFormatsAdditional[] = 'd-m-y H:i';
// e.g. 31-12-17 23:59
```
