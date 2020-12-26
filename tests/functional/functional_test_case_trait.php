<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\tests\functional;

trait functional_test_case_trait
{
	static protected function setup_extensions()
	{
		return ['alfredoramos/seometadata'];
	}

	abstract protected function init();

	protected function setUp(): void
	{
		parent::setUp();

		// Set default image
		$this->update_config_value(
			'seo_metadata_default_image',
			'default_image.jpg'
		);

		// Set JSON-LD logo
		$this->update_config_value(
			'seo_metadata_json_ld_logo',
			'default_logo.jpg'
		);

		$this->init();
	}

	private function update_config_value($name = '', $value = '')
	{
		$name = trim($name);
		$value = trim($value);

		if (empty($name))
		{
			return;
		}

		$db = $this->get_db();
		$sql = 'UPDATE ' . CONFIG_TABLE . '
			SET ' . $db->sql_build_array('UPDATE',
				[
					'config_value' => $value,
					'is_dynamic' => 1 // Fix cache
				]
			) . '
			WHERE ' . $db->sql_build_array('UPDATE',
				['config_name' => $name]
			);
		$db->sql_query($sql);
	}
}
