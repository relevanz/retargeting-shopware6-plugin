#!/usr/bin/env bash
set -e

# Build compiled assets (storefront JS + administration JS/CSS)
if command -v shopware-cli &>/dev/null; then
    echo "Building assets with shopware-cli..."
    shopware-cli extension build .
elif docker inspect shopware &>/dev/null 2>&1 && docker exec shopware test -d /var/www/html/custom/plugins/RelevaRetargeting &>/dev/null 2>&1; then
    echo "Building assets from Docker container 'shopware'..."
    DOCKER_PLUGIN_PATH="shopware:/var/www/html/custom/plugins/RelevaRetargeting"
    docker cp "$DOCKER_PLUGIN_PATH/src/Resources/app/storefront/dist/" /tmp/releva-dist/
    rm -r ./src/Resources/app/storefront/dist/
    mv /tmp/releva-dist/ ./src/Resources/app/storefront/dist/
    docker cp "$DOCKER_PLUGIN_PATH/src/Resources/public/administration/css" /tmp/releva-admin-css/
    rm -r ./src/Resources/public/administration/css/
    mv /tmp/releva-admin-css/ ./src/Resources/public/administration/css/
    docker cp "$DOCKER_PLUGIN_PATH/src/Resources/public/administration/js" /tmp/releva-admin-js/
    rm -r ./src/Resources/public/administration/js/
    mv /tmp/releva-admin-js/ ./src/Resources/public/administration/js/
else
    echo "Neither shopware-cli nor Docker available — using existing compiled assets"
    echo "Install shopware-cli: brew install shopware-cli"
fi

# Clean folder
rm -rf RelevaRetargeting RelevaRetargeting.zip
rsync -a --exclude=nbproject --exclude=.git --exclude=.gitignore --exclude=.gitmodules --exclude=.claude --exclude=.DS_Store --exclude=RelevaRetargeting --exclude=RelevaRetargeting.zip --exclude=.directory ./ ./RelevaRetargeting/

# Remove shopware requires for standalone install
sed -i '' '/"shopware\/core": "/d' ./RelevaRetargeting/composer.json
sed -i '' '/"shopware\/administration": "/d' ./RelevaRetargeting/composer.json
sed -i '' '/"shopware\/storefront": "/d' ./RelevaRetargeting/composer.json

# Install dependencies
composer install --no-dev -n -o -d RelevaRetargeting

# Rollback composer.json to include shopware requires
cp composer.json RelevaRetargeting

# Zip + clean
zip -r RelevaRetargeting.zip RelevaRetargeting
rm -rf RelevaRetargeting
