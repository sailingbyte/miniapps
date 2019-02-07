# MiniApps library

This is PHP library for Sailing Byte MiniApps API

## Prerequisites

You will need at least PHP 7.0, but 7.2 is recommended. You need PHP CURL extension installed and enabled. This package is designed for Laravel usage, but you can also use this as standalone library.

## Installing

Installation is fairly simple

Ensure you have curl installed

```
apt-get install php7.2 php7.2-curl
yum install php7.2 php7.2-curl
```

install via composer

```
composer require sailingbyte/miniappslib
```

Get API key at https://miniapps.sailingbyte.com/ - use on library initialisation

```
$obj = MiniAppsLib(YOUR_API_KEY);
```

##Examples

Run DIFF

```
$diff = $obj->diff('text1', 'text2');
```

etc

## Running the tests

At the moment there are no automated tests written

## Authors

* **Łukasz Pawłowski** - *Initial work* - [Sailing Byte](https://sailingbyte.com)

See also the list of [contributors](https://github.com/your/project/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
