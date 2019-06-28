<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\migrations\v10x;

use phpbb\db\migration\migration;

class m00_configuration extends migration
{
	/**
	 * Add configuration.
	 *
	 * @return array
	 */
	public function update_data()
	{
		return [
			[
				'config.add',
				['seo_metadata_meta_description', 1]
			],
			[
				'config.add',
				['seo_metadata_desc_length', 160]
			],
			[
				'config.add',
				['seo_metadata_desc_strategy', 0]
			],
			[
				'config.add',
				['seo_metadata_image_strategy', 0]
			],
			[
				'config.add',
				['seo_metadata_default_image', '']
			],
			[
				'config.add',
				['seo_metadata_local_images', 1]
			],
			[
				'config.add',
				['seo_metadata_open_graph', 1]
			],
			[
				'config.add',
				['seo_metadata_facebook_application', 0]
			],
			[
				'config.add',
				['seo_metadata_facebook_publisher', '']
			],
			[
				'config.add',
				['seo_metadata_twitter_cards', 1]
			],
			[
				'config.add',
				['seo_metadata_twitter_publisher', '']
			],
			[
				'config.add',
				['seo_metadata_json_ld', 1]
			]
		];
	}
}
