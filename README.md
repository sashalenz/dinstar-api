
[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/support-ukraine.svg?t=1" />](https://supportukrainenow.org)

# Dinstar New API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sashalenz/dinstar-api.svg?style=flat-square)](https://packagist.org/packages/sashalenz/dinstar-api)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/sashalenz/dinstar-api/run-tests?label=tests)](https://github.com/sashalenz/dinstar-api/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/sashalenz/dinstar-api/Check%20&%20fix%20styling?label=code%20style)](https://github.com/sashalenz/dinstar-api/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/sashalenz/dinstar-api.svg?style=flat-square)](https://packagist.org/packages/sashalenz/dinstar-api)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/dinstar-api.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/dinstar-api)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require sashalenz/dinstar-api
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="dinstar-api-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$dinstarApi = new Sashalenz\DinstarApi();
echo $dinstarApi->echoPhrase('Hello, Sashalenz!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Oleksandr Petrovskyi](https://github.com/sashalenz)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
