#!/bin/bash --

set -e
set -x

EXTNAME="${1}"
NOTESTS="${2}"

# Add required files for tests
if [[ "${NOTESTS}" != 1 ]]; then
	images=(
		'default_image.jpg'
		'default_logo.jpg'
		'forum_image.jpg'
		'wide_image.jpg'
	)

	for image in "${images[@]}"; do
		file=images/"${image}"
		fixture=ext/"${EXTNAME}"/tests/functional/fixtures/images/"${image}"

		if [[ ! -f "${file}" ]]; then
			cp "${fixture}" "$(dirname "${file}")"/
		fi
	done
fi
