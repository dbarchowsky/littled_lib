# littled
`dbarchowsky/littled` PHP site framework and utility libraries.

## Prerequisites

* PHP 5.6, PHP 7.0, 7.1, 7.2

## Installation

Install with composer, e.g.

```json
{
	"require": {
		"dbarchowsky/littled": "dev-dist"
	},
	"repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/dbarchowsky/littled"
		}
	]
}
```

## Unit Tests

### Prerequisites

* [Composer](https://getcomposer.org/download/)  
* [Homebrew](https://brew.sh/) - Installs PHP and xdebug.
* [xdebug](https://xdebug.org/) - PHP debugging library.
* [PHPUnit](https://phpunit.de/getting-started/phpunit-7.html) - PHP debugging library.

[This article](https://medium.com/@romaninsh/install-php-7-2-xdebug-on-macos-high-sierra-with-homebrew-july-2018-d7968fe7e8b8) has nice instructions on how to install PHP with xdebug, including installing multiple versions of PHP, e.g. 7.1 alongside 5.6.

The environment of PHP installed via homebrew is more easily controlled than the built-in Mac OS PHP.

Install PHP from the command line using homebrew:
```text
brew install php@7.2
```

Then install `xdebug`:
```text
pecl install xdebug
```

(In PHPStorm, set the PHP interpreter with **PHPStorm** > **Preferences** > **Languages & Frameworks** > **PHP** > **CLI Interpreter**.)

[Install Composer locally](https://getcomposer.org/download/) in the `app` directory.

Install PHPUnit with composer:
```$xslt
php composer.phar install
```
or 
```$xslt
php composer.phar upgrade
```

Database connection properties are defined in different PHPUnit config files located in `/tests/config/`. 

The PHPUnit config files are excluded from the git repo. Copy them from an existing development environment.

In **PHPStorm**, load the configuration files in the PHPUnit test configuration:
* **Run** > **Edit Configurations...**
  * **Use alternative configuration file**: `checked`
  * Enter the path to the XML file in `/tests/config/` containing the appropriate database connection values.
  * The XML files define constants (e.g. `MYSQL_HOST`, `MYSQL_SCHEMA`, etc.) that are used to establish database connections.