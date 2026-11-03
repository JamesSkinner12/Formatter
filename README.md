# Formatter
Useful BaseFormatter class that provided many tools for working with very deeply-nested datasets

## Usage
```php
<?php
require_once(__DIR__ . "/vendor/autoload.php");
use JamesSkinner12\Formatter\SampleFormatter;

$data = []; // Load your data here
$formatter = new SampleFormatter($data);

// Would return
$formatter->getItem("people|*|name");
```
