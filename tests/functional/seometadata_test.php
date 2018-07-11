<?php

/**
 * SEO Metadata Extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\tests\functional;

use phpbb_functional_test_case;

/**
 * @group functional
 */
class seometadata_test extends phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return ['alfredoramos/seometadata'];
	}

	public function setUp()
	{
		parent::setUp();

		$db = $this->get_db();
		$sql = 'UPDATE ' . CONFIG_TABLE . '
			SET  ' . $db->sql_build_array('UPDATE',
				['config_value' => 'seo/default_image.png']
			) . '
			WHERE ' . $db->sql_build_array('UPDATE',
				['config_name' => 'seo_metadata_default_image']
			);
		$db->sql_query($sql);
		$db->sql_close();
		unset($db);

		$this->login();
	}

	public function test_open_graph()
	{
		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=1&sid=%s',
			$this->sid
		));

		$elements = [];
		$open_graph = [
			'locale',
			'site_name',
			'title',
			'description',
			'type',
			'url',
			'image'
		];

		foreach ($open_graph as $property)
		{
			$elements[$property] = $crawler->filter(
				sprintf('meta[property="og:%s"]', $property)
			);
			//$this->assertSame(1, $elements[$property]->count());
		}

		$this->assertSame(
			'en',
			$elements['locale']->attr('content')
		);
		$this->assertSame(
			'yourdomain.com',
			$elements['site_name']->attr('content')
		);
		$this->assertSame(
			'Welcome to phpBB3',
			$elements['title']->attr('content')
		);
		$this->assertSame(
			'This is an example post in your phpBB3 installation. Everything seems to be working. You may delete this post if you like and continue to set up your board. Dur',
			$elements['description']->attr('content')
		);
		$this->assertSame(
			'website',
			$elements['type']->attr('content')
		);
		$this->assertSame(
			'http://localhost/viewtopic.php?t=1',
			$elements['url']->attr('content')
		);
		/*$this->assertSame(
			'http://localhost/phpBB3/images/seo/default_image.png',
			$elements['image']->attr('content')
		);*/
	}
}
