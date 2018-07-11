<?php

/**
 * SEO Metadata Extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\migrations\v10x;

use phpbb\db\migration\migration;

class m1_seometadata_data extends migration
{

	/**
	 * Add Imgur configuration.
	 *
	 * @return array
	 */
	public function update_data()
	{
		return [
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
				['seo_metadata_default_image', '']
			],
			[
				'config.add',
				['seo_metadata_open_graph', 1]
			],
			[
				'config.add',
				['seo_metadata_json_ld', 1]
			]
		];
	}

}
