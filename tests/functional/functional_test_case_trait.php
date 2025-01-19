<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@proton.me>
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

		$this->update_config([
			'seo_metadata_default_image' => 'default_image.jpg',
			'seo_metadata_default_image_type' => 'image/jpeg',
			'seo_metadata_default_image_width' => '640',
			'seo_metadata_default_image_height' => '480',
			'seo_metadata_json_ld_logo' => 'default_logo.jpg',
			'seo_metadata_json_ld_logo_width' => '150',
			'seo_metadata_json_ld_logo_height' => '150'
		]);

		$this->init();
	}

	private function update_config(array $data = [])
	{
		if (empty($data))
		{
			return;
		}

		$db = $this->get_db();
		$db->sql_transaction('begin');

		foreach ($data as $key => $value)
		{
			if (!is_string($key) || !is_string($value))
			{
				continue;
			}

			$key = trim($key);
			$value = trim($value);

			if (empty($key))
			{
				continue;
			}

			$sql = 'UPDATE ' . CONFIG_TABLE . '
			SET ' . $db->sql_build_array('UPDATE',
				[
					'config_value' => $value,
					'is_dynamic' => 1 // Fix cache
				]
			) . '
			WHERE ' . $db->sql_build_array('UPDATE',
				['config_name' => $key]
			);
			$db->sql_query($sql);
		}

		$db->sql_transaction('commit');
	}
}
