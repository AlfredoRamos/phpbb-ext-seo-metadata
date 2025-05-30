<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@proton.me>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\migrations\v14x;

use phpbb\db\migration\migration;

class m00_configuration extends migration
{
	/**
	 * Migration dependencies.
	 *
	 * @return array
	 */
	static public function depends_on()
	{
		return ['\alfredoramos\seometadata\migrations\v13x\m00_configuration'];
	}

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
				['seo_metadata_max_images', 5]
			]
		];
	}
}
