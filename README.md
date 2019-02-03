# API Generator

It's generate api endpoints based on database tables

## Install

composer install

## Usage

```
$generator = new ApiGenerator\Generator($conn);
$generator->generate();
```

### Available request methods

'GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'

