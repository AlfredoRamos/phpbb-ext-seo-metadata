<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\acp;

class main_info
{
	/**
	 * Set up ACP module.
	 *
	 * @return array
	 */
	public function module()
	{
		return [
			'filename'	=> '\alfredoramos\seometadata\acp\main_module',
			'title'		=> 'ACP_SEO_METADATA',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'SETTINGS',
					'auth'	=> 'ext_alfredoramos/seometadata && acl_a_board',
					'cat'	=> ['ACP_SEO_METADATA']
				]
			]
		];
	}
}
