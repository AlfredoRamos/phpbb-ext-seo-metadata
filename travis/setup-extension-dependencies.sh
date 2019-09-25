#!/bin/bash --

set -e
set -x

EXTNAME="${1}"
EXTDEPS="${2}"
NOTESTS="${3}"

# Add required files for tests
if [[ "${NOTESTS}" != 1 ]]; then
	if [[ ! -f phpBB/images/default_image.jpg ]]; then
		cp "${TRAVIS_BUILD_DIR}"/tests/functional/fixtures/images/default_image.jpg phpBB/images/
	fi

	if [[ ! -f phpBB/images/default_logo.jpg ]]; then
		cp "${TRAVIS_BUILD_DIR}"/tests/functional/fixtures/images/default_logo.jpg phpBB/images/
	fi
fi

# Check if package have dependencies in the
# 'require' object, inside the composer.json file
if [[ "${EXTDEPS}" == '1' ]]; then
	composer install \
		--working-dir=phpBB/ext/"${EXTNAME}" \
		--prefer-dist \
		--no-dev \
		--no-interaction
fi
