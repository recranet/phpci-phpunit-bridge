# PHPCI Symfony PHPUnit Bridge Plugin
A PHPCI plugin to enable testing with the Symfony PHPUnit Bridge.

## Configuration
### Options
* **directory** [optional, string] - Directory within which you want PHPUnit to run. (default: build root) 
* **options** [optional, string|array] - Options you wish to pass to PHPUnit. Check the [PHPUnit documentation](https://phpunit.de/manual/current/en/textui.html) for the available options.
* **path** [optional, string] - The path to the tests that PHPUnit needs to run, this can either be a directory or a specific test file.

### Example
```yml
test:
    Recranet\Plugin\PhpUnitBridge:
        directory: "tests"
        options:
            - "--tap"
        path: "tests/../../ExampleControllerTest.php"
```

## License & Copyright
Copyright (c) 2017 Recranet <info@recranet.com>.
This extension is open-source software licensed under the GPLv3 license.
