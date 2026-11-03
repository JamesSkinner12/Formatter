# Formatter
Useful class that provided many tools for working with very deeply-nested datasets
## Usage/Examples

#### Basic Usage 
```php
<?php

use JamesSkinner12\Formatter\SampleFormatter;

// Using the sample.json file provided
$data = json_decode(file_get_contents(__DIR__ . "/sample.json", true));

// Instantiate the class with the dataset
$formatter = new SampleFormatter($data);

// To get a list of the entire array structure in a flattened pipe-seperated format
$formatter->pipeSchema();

// To get a nested array structure of the dataset, but with data removed
$formatter->hierachicalSchema();

// To extract the name from every sub-record of 'people'
$formatter->getItem("people|*|name");

// Retrieves the same data as `getItem`, but also removes it from the dataset.
$formatter->pluckItem("people|*|name");

// Moves the data in the set from it's original path to the new path
$formatter->moveItem("people|*|name", "people|*|label");

// Removes the data in the specified path from the dataset
$formatter->removeItem("people|*|details|hair");

// Inserts the provided data at the specified path
$formatter->putItem("people|*|details|hairColor", ["blue", "yellow", "green"]);
```

