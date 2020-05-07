#!/bin/bash --

set -e
set -x

EXTNAME="${1}"
EXTDEPS="${2}"
NOTESTS="${3}"

# Add required files for tests
if [[ "${NOTESTS}" != 1 ]]; then
	images=(
		'default_image.jpg'
		'default_logo.jpg'
		'forum_image.jpg'
		'wide_image.jpg'
	)

	for image in "${images[@]}"; do
		file=phpBB/images/"${image}"
		fixture="${TRAVIS_BUILD_DIR}"/tests/functional/fixtures/images/"${image}"

		if [[ ! -f "${file}" ]]; then
			cp "${fixture}" "$(dirname "${file}")"/
		fi
	done
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
