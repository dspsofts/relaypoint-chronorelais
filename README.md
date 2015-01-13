RelayPoint gateway for Chrono Relais
======

Chrono Relais Relay point search

[![Latest Version](https://img.shields.io/github/release/pfeyssaguet/relaypoint-chronorelais.svg?style=flat-square)](https://github.com/pfeyssaguet/relaypoint-chronorelais/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/pfeyssaguet/relaypoint-chronorelais/master.svg?style=flat-square)](https://travis-ci.org/pfeyssaguet/relaypoint-chronorelais)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/pfeyssaguet/relaypoint-chronorelais.svg?style=flat-square)](https://scrutinizer-ci.com/g/pfeyssaguet/relaypoint-chronorelais/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/pfeyssaguet/relaypoint-chronorelais.svg?style=flat-square)](https://scrutinizer-ci.com/g/pfeyssaguet/relaypoint-chronorelais)


## Install

For now you have to clone this repository. Install via Composer might come later :)

## Usage

``` php
$o = (new GatewayFactory())->create('Chronorelais');

$a = $o->search(array('zip' => '92100'));

var_dump($a);
```

## Testing

``` bash
$ phpunit
```

## Credits

- [Pierre Feyssaguet](https://github.com/pfeyssaguet)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
