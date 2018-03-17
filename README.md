# PHP Ares API

## Install

```
composer require milankyncl/ares-api dev-master
```

## How to use ?

```php

use MilanKyncl\AresAPI;
 
...
 
$ares = new AresAPI();
 
$subject = $ares->findByIN('123456'); // Identification number of subject
 
$subjects = $ares->findByName('Name'); // Name of subject

```