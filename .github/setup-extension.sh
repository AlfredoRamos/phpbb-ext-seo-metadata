#!/bin/bash --

set -e
set -x

EXTNAME="${1}"
NOTESTS="${2}"

# Fix for Composer 2
composer require \
	-n \
	--ignore-platform-reqs \
	--prefer-dist \
	--no-progress \
	'composer/package-versions-deprecated:^1.11.99' \
	'ocramius/proxy-manager:~2.1.1'

# Install dependencies
composer update \
	-n \
	--ignore-platform-reqs \
	--prefer-dist \
	--no-progress

# Prepare extension structure
mkdir -p ../phpBB3/phpBB/ext/"${EXTNAME}"

# Build extension package
vendor/bin/phing

# Copy extension files and directories
cp -a build/package/"${EXTNAME}"/* ../phpBB3/phpBB/ext/"${EXTNAME}"/

# Add required files for tests
if [[ "${NOTESTS}" != 1 ]]; then
	cp -a {phpunit.xml.dist,tests/} ../phpBB3/phpBB/ext/"${EXTNAME}"/
fi
