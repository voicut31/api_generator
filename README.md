# API Generator

It's generate api endpoints based on database tables

## Install

### Install composer 

```
brew install composer
```

### Install packages
```
composer install
```

## Configure the aplication



## Usage

```
...
use ApiGenerator\Generator;
$configuration = new \Doctrine\DBAL\Configuration();
$conn = \Doctrine\DBAL\DriverManager::getConnection($config['database'], $configuration);

...

$generator = new Generator($conn);
$generator->api($module, $id, $params);

```

### Available request methods

'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'

