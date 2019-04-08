<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\migrations\v10x;

use phpbb\db\migration\migration;

class m01_modules extends migration
{
	/**
	 * Add SEO Metadata ACP settings.
	 *
	 * @return array
	 */
	public function update_data()
	{
		return [
			[
				'module.add',
				[
					'acp',
					'ACP_CAT_DOT_MODS',
					'ACP_SEO_METADATA'
				]
			],
			[
				'module.add',
				[
					'acp',
					'ACP_SEO_METADATA',
					[
						'module_basename' => '\alfredoramos\seometadata\acp\main_module',
						'modes'	=> ['settings']
					]
				]
			]
		];
	}
}
