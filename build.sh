#!/usr/bin/env bash

rm -rf RelevaRetargeting RelevaRetargeting.zip
rsync -a --exclude=nbproject --exclude=.git --exclude=.gitignore --exclude=.gitmodules --exclude=RelevaRetargeting --exclude=RelevaRetargeting.zip --exclude=.directory ./ ./RelevaRetargeting/
composer install --no-dev -n -o -d RelevaRetargeting
zip -r RelevaRetargeting.zip RelevaRetargeting
rm -rf RelevaRetargeting