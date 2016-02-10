# Addvilz/autoload_patcher

## Purpose of this library

Purpose of this library is to allow patching the source code of class files during runtime.

There are some cases when you need to modify functionality of vendor code but you are not able to replace the code,
or extend it (static calls from other vendor code is a good example).

This library allows you to replace, augment or otherwise change original source code and eval it afterwards.

## Installation

`composer require addvilz/autoload_patcher`

## Example use:

```php
$loader = require 'vendor/autoload.php';
$patcher = new \Addvilz\AutoloadPatcher\Patcher($loader);
$patcher
    ->register()
    ->addPatcher('Some\Vendor\UtilityClass', function ($code) {
    
        // Let's rename the class
        $code = str_replace(
            'class UtilityClass',
            'class VendorUtilityClass',
            $code
        );
        
        // ... More modification here, runtime code generation, etc.
        
        return $code;
    });
```

## How does it work?

`Patcher->register()` method call appends an autoload callback to autoload stack. This means, all class loading
will be proxied tru `Patcher` instance, and if patcher callback for given class is registered, it will be executed against
source code from file determined by Composer `ClassLoader` class.

NB: Before the source code of the class is passed to the callback function, opening PHP tags are removed!

NB: Whatever the patcher callback function returns is passed on to `eval()`.

## License

Licensed under terms and conditions of Apache 2.0 license.
