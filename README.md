# relevanz-shopware6-plugin

### Installing

There a two ways of installing releva.nz plugin.

#### Composer
Add to the composer.json of your shopware-6 application
```
    "repositories": [
        {
            "type": "path",
            "url": "../path/to/releva.nz/plugin/repository",
            "options": {
                "symlink": true
            }
        }
    ]
```

Add repository
```
    $composer require releva/relevanz-shopware-plugin
```

#### Upload in Shopware-6 backend
Just go to Settings => System => Plugins and upload RelevanzTrackingShopware.zip from root-folder of releva.nz plugin.

The zip file could be created with
```
    php createShopwareZip.php 
```