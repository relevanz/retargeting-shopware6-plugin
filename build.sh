#!/usr/bin/env bash

#clean generated webpack files and copy to repository
rm -r ./src/Resources/app/storefront/dist/
docker cp shopware:/var/www/html/custom/plugins/RelevaRetargeting/src/Resources/app/storefront/dist/ ./src/Resources/app/storefront/dist/
rm -r ./src/Resources/public/administration/css/
docker cp shopware:/var/www/html/custom/plugins/RelevaRetargeting/src/Resources/public/administration/css ./src/Resources/public/administration/
rm -r ./src/Resources/public/administration/js/
docker cp shopware:/var/www/html/custom/plugins/RelevaRetargeting/src/Resources/public/administration/js ./src/Resources/public/administration/

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
