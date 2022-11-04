# PHP Validate

[![License](https://img.shields.io/packagist/l/inhere/php-validate.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/badge/php-%3E=8.1-brightgreen.svg?maxAge=2592000)](https://packagist.org/packages/inhere/php-validate)
[![Latest Stable Version](http://img.shields.io/packagist/v/inhere/php-validate.svg)](https://packagist.org/packages/inhere/php-validate)
[![Coverage Status](https://coveralls.io/repos/github/inhere/php-validate/badge.svg?branch=master)](https://coveralls.io/github/inhere/php-validate?branch=master)
[![Github Actions Status](https://github.com/inhere/php-validate/workflows/Unit-tests/badge.svg)](https://github.com/inhere/php-validate/actions)
[![zh-CN readme](https://img.shields.io/badge/中文-Readme-brightgreen.svg?style=for-the-badge&maxAge=2592000)](README.md)

Lightweight and feature-rich PHP validation and filtering library.

- Simple and convenient, support to add custom validator
- Support pre-verification check, customize how to judge non-empty
- Support grouping rules by scene. Or partial verification
- Supports the use of filters to purify and filter values before verification [built-in filter](#built-in-filters)
- Support pre-processing and post-processing of verification [independent verification processing](#on-in-Validation)
- Support to customize the error message, field translation, message translation of each verification, and support the default value
- Supports basic array checking, checking of children (`'goods.apple'`) values ​​of arrays, checking of children of wildcards (`'users.*.id' 'goods.*'`)
- Easy access to error information and secure data after verification (only data that has been checked regularly)
- More than 60 commonly used validators have been built-in [built-in validator](#built-in-validators)
- Rule setting reference `yii`, `laravel`, `Respect/Validation`
- Independent filter `Inhere\Validate\Filter\Filtration`, can be used for data filtering alone

## License

[MIT](LICENSE)
