# UFO Framework

[![Software License][ico-license]](LICENSE.txt)
[![Build Status][ico-travis]][link-travis]
[![codecov.io][ico-codecov]][link-codecov]

The UFO Framework is a small and simple PHP framework.
It's allow to create different projects - web-sites, web-services,  API 
and other.
Base structure of project - section with own unique URL, wich handled 
by module.
Module have to implement logic and generate information. It can handle 
one or many sections and provide widgets.
Widget is a block of information, can be placed on any page (pages).
Framework implements only routing, parameters parsing, caching and 
composing data from modules and widgets.

## Install
To create UFO Framework based project use special [package with project](https://packagist.org/packages/enikeishik/ufoproject).

## Requirements
* PHP >= 7.2

## Tests
To execute the test suite, you'll need [codeception](https://codeception.com/).
```bash
vendor\bin\codecept run
```

[ico-license]: https://img.shields.io/badge/license-GPL-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/enikeishik/ufoframework/master.svg?style=flat-square
[link-travis]: https://travis-ci.org/enikeishik/ufoframework
[ico-codecov]: https://codecov.io/gh/enikeishik/ufoframework/branch/master/graphs/badge.svg
[link-codecov]: https://codecov.io/gh/enikeishik/ufoframework/
