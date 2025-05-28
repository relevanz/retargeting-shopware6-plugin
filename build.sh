#!/usr/bin/env bash

#clean generated webpack files and copy to repository
rm -rf ./src/Resources/app/storefront/dist/
cp -r ../shopware-6.7.docker/custom/plugins/RelevaRetargeting/src/Resources/app/storefront/dist/ ./src/Resources/app/storefront/dist/
rm -rf ./src/Resources/public/administration/.vite/
cp -r ../shopware-6.7.docker/custom/plugins/RelevaRetargeting/src/Resources/public/administration/.vite ./src/Resources/public/administration/
rm -rf ./src/Resources/public/administration/assets/
cp -r ../shopware-6.7.docker/custom/plugins/RelevaRetargeting/src/Resources/public/administration/assets ./src/Resources/public/administration/

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
