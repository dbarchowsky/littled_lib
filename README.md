# littled

`dbarchowsky/littled` PHP site framework and utility libraries.

## Prerequisites

* PHP 8.4+

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