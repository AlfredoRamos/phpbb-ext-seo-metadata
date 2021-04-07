#!/bin/bash --

set -e
set -x

EXTNAME="${1}"
NOTESTS="${2}"

# Install dependencies
composer update \
	-n \
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
