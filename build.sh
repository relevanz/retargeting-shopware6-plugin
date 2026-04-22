#!/usr/bin/env bash
set -e

DOCKER_PLUGIN_PATH="../shopware-6.7.docker/custom/plugins/RelevaRetargeting"

# Build compiled assets (storefront JS + administration JS/CSS)
if command -v shopware-cli &>/dev/null; then
    echo "Building assets with shopware-cli..."
    shopware-cli extension build .
    # shopware-cli wipes public/administration/; restore tracked static assets (plugin icon, etc.)
    git checkout HEAD -- src/Resources/public/administration/static 2>/dev/null || true
    # shopware-cli writes a stray src/Storefront/ scaffold; drop it
    rm -rf src/Storefront
elif [ -d "$DOCKER_PLUGIN_PATH" ]; then
    echo "Copying assets from Docker plugin path..."
    rm -rf ./src/Resources/app/storefront/dist/
    cp -r "$DOCKER_PLUGIN_PATH/src/Resources/app/storefront/dist/" ./src/Resources/app/storefront/dist/
    rm -rf ./src/Resources/public/administration/.vite/
    cp -r "$DOCKER_PLUGIN_PATH/src/Resources/public/administration/.vite" ./src/Resources/public/administration/
    rm -rf ./src/Resources/public/administration/assets/
    cp -r "$DOCKER_PLUGIN_PATH/src/Resources/public/administration/assets" ./src/Resources/public/administration/
else
    echo "Neither shopware-cli nor Docker available — using existing compiled assets"
    echo "Install shopware-cli: brew install shopware-cli"
fi

# Clean folder
rm -rf RelevaRetargeting RelevaRetargeting.zip
rsync -a --exclude=nbproject --exclude=.git --exclude=.gitignore --exclude=.gitmodules --exclude=.claude --exclude=.DS_Store --exclude=/src/Storefront --exclude=RelevaRetargeting --exclude=RelevaRetargeting.zip --exclude=.directory ./ ./RelevaRetargeting/

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
