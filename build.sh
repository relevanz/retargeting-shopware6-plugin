#!/usr/bin/env bash

#clean folder
rm -rf RelevaRetargeting RelevaRetargeting.zip
rsync -a --exclude=nbproject --exclude=.git --exclude=.gitignore --exclude=.gitmodules --exclude=RelevaRetargeting --exclude=RelevaRetargeting.zip --exclude=.directory ./ ./RelevaRetargeting/

#remove shopware-requieres
sed -i '/"shopware\/core": "/d' ./RelevaRetargeting/composer.json
sed -i '/"shopware\/administration": "/d' ./RelevaRetargeting/composer.json
sed -i '/"shopware\/storefront": "/d' ./RelevaRetargeting/composer.json

#install
composer install --no-dev -n -o -d RelevaRetargeting

#rollback remove requieres
cp composer.json RelevaRetargeting

#zip + clean
zip -r RelevaRetargeting.zip RelevaRetargeting
rm -rf RelevaRetargeting
