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

### CSS

CSS is added to a project by including the Sass files that are distributed in `littled/stylesheets/shared/` in the local Sass compile. 

E.g. 

Add the path to the shared sass to `gulpfile.js`:

```javascript
const paths = {
    styles: {
        site: {
            src: 'sass/main.scss',
            includes: [
                'app/vendor/dbarchowsky/littled/stylesheets'
            ],
[...]
```

Then include the shared Sass files in local Sass files:

```sass
@import "shared/base/all";
@import "shared/modules/all";
@import "shared/cms/all";
@import "shared/vendor/jquery_ui";
```

Compile the local Sass to create a local stylesheet which will contain all the rules from the distribution.

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

### PHPStorm configuration

Make sure that the project autoloader script is used to load PHPUnit. (Otherwise there will be errors in the console to the effect that PHPUnit is not installed or cannot be found.)

* **Preferences** > **Languages & Frameworks** > **PHP** > **Test Frameworks** 
    * **Use Composer autoloader:** `/path/to/project/vender/autoload.php`

In **PHPStorm**, load the configuration files in the PHPUnit test configuration:
* **Run** > **Edit Configurations...**
  * **Use alternative configuration file**: `checked`
  * Enter the path to the XML file in `/tests/config/` containing the appropriate database connection values.
  * The XML files define constants (e.g. `MYSQL_HOST`, `MYSQL_SCHEMA`, etc.) that are used to establish database connections.
  
## PHP class structure

```text
Littled\App\AppBase
 |
 +- Littled\Database\MySQLConnection
     |
     +- Littled\PageContent\Serialized\SerializedContentUtils
         |
         +- Littled\Keyword\Keyword
         |
         +- Littled\PageContent\Serialized\SerializedContent
             |
             +- Littled\PageContent\Images\ImageBase
             |   |
             |   +- Littled\PageContent\Images\ImageFile
             |       | 
             |       +- Littled\PageContent\Images\Image
             |
             +- Littled\PageContent\SiteSection\SiteSection
             |
             +- Littled\PageContent\SiteSection\SectionContent
                |
                 +- Littled\PageContent\SiteSection\KeywordSectionContent
                     |
                     +- Littled\PageContent\Albums\Album
                     |   |
                     |   +- Littled\PageContent\Albums\SocialXPostAlbum
                     |
                     +- Littled\PageContent\Albums\Gallery
                     |
                     +- Littled\PageContent\Images\ImageLink
                         |
                         +- Littled\PageContent\Images\ImageUpload
                             |
                             +- Littled\PageContent\Images\SocialXPostImage
```