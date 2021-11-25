# ConcreteCMS Foundation
[![Latest Version on Packagist](https://img.shields.io/packagist/v/xanweb/c5-foundation.svg?style=flat-square)](https://packagist.org/packages/xanweb/c5-foundation)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

## Installation

Include library to your composer.json
```bash
composer require xanweb/c5-foundation
```

## How to use Image Data Extractor
Data Extractor tries to use both IPTC and EXIF metadata to fill image information.

Register importer processor within `application/config/app.php`
```php
return [
    /*
     * Importer processors
     */
    'import_processors' => [
        'xw.image.data_extractor' => \Xanweb\C5\Foundation\File\Import\Processor\DataExtractor::class,
    ],
];
```
<br>
You need then to map image information to properties or attributes.<br>

Here is the list of available information keys:
* `image_title`: title of the image 
* `artist`: artist name
* `author_title`: author name
* `keywords`
* `image_description`
* `comments`
* `copyright`

The default mapping is like follows:
```php 
[
    'image_title' => 'title', // mapped to file title property
    'comments' => 'description', // mapped to file description property
    'keywords' => 'tags', // mapped to file tags property
]
```

To override the default mapping you need to add `xanweb.php` config file under `/application/config`.<br>
Here is an example of possible mapping:
```php
<?php

return [
    'file_manager' => [
        'images' => [
            'data_extractor' => [
                'image_title' => ['title', 'ak_alt'], // You can use multiple properties/attributes per field.
                'comments' => 'description',
                'keywords' => 'tags',
                'author_title' => 'ak_author', // Use 'ak_' prefix for attributes.
                'copyright' => 'ak_copyright',
            ]
        ],
    ],
];
```
