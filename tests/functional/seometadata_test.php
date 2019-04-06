<?php

/**
 * SEO Metadata extension for phpBB.
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

	protected $expected_data;

	public function setUp()
	{
		parent::setUp();

		// Set default image
		$this->update_config_value(
			'seo_metadata_default_image',
			'seo/default_image.png'
		);

		$this->expected_data = [
			'title' => 'Welcome to phpBB3',
			'description' => 'This is an example post in your phpBB3 installation. Everything seems to be working. You may delete this post if you like and continue to set up your board. Dur',
			'url' => 'http://localhost/viewtopic.php?t=1',
			'image' => 'http://localhost/images/seo/default_image.png'
		];
	}

	protected function update_config_value($name = '', $value = '')
	{
		if (empty($name) || empty($value))
		{
			return;
		}

		$db = $this->get_db();
		$sql = 'UPDATE ' . CONFIG_TABLE . '
			SET  ' . $db->sql_build_array('UPDATE',
				[
					'config_value' => $value,
					'is_dynamic' => 1 // Fix cache
				]
			) . '
			WHERE ' . $db->sql_build_array('UPDATE',
				['config_name' => $name]
			);
		$db->sql_query($sql);
		$db->sql_close();
		unset($db);
	}

	public function test_meta_description()
	{
		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=1&sid=%s',
			$this->sid
		));

		$element = $crawler->filter('meta[name="description"]');

		$this->assertSame(1, $element->count());
		$this->assertSame(
			$this->expected_data['description'],
			$element->attr('content')
		);
	}

	public function test_open_graph()
	{
		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=1&sid=%s',
			$this->sid
		));

		$elements = [];
		$open_graph = [
			'og' => [
				'locale',
				'site_name',
				'url',
				'type',
				'title',
				'description',
				'image'
			],
			'article' => [
				'published_time',
				'section'
			]
		];

		foreach ($open_graph as $key => $value)
		{
			foreach ($value as $k => $v)
			{
				$elements[$v] = $crawler->filter(
					vsprintf('meta[property="%1$s:%2$s"]', [$key, $v])
				);

				$this->assertSame(1, $elements[$v]->count());
			}
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
			$this->expected_data['url'],
			$elements['url']->attr('content')
		);
		$this->assertSame(
			'article',
			$elements['type']->attr('content')
		);
		$this->assertSame(
			$this->expected_data['title'],
			$elements['title']->attr('content')
		);
		$this->assertSame(
			$this->expected_data['description'],
			$elements['description']->attr('content')
		);
		$this->assertSame(
			$this->expected_data['image'],
			$elements['image']->attr('content')
		);
		$this->assertSame(
			1,
			preg_match(
				'#^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$#',
				$elements['published_time']->attr('content')
			)
		);
		$this->assertSame(
			'Your first forum',
			$elements['section']->attr('content')
		);
	}

	public function test_twitter_cards()
	{
		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=1&sid=%s',
			$this->sid
		));

		$elements = [];
		$twitter_cards = [
			'twitter' => [
				'card'
			]
		];

		foreach ($twitter_cards as $key => $value)
		{
			foreach ($value as $k => $v)
			{
				$elements[$v] = $crawler->filter(
					vsprintf('meta[name="%1$s:%2$s"]', [$key, $v])
				);

				$this->assertSame(1, $elements[$v]->count());
			}
		}

		$this->assertSame(
			'summary',
			$elements['card']->attr('content')
		);
	}

	public function test_json_ld()
	{
		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=1&sid=%s',
			$this->sid
		));

		$elements = [
			'script' => $crawler->filter('script[type="application/ld+json"]')
		];
		$json_ld = [
			'@context',
			'@type',
			'@id',
			'headline',
			'description',
			'image'
		];
		$array_data = json_decode($elements['script']->text(), true);

		$this->assertSame(1, $elements['script']->count());
		$this->assertFalse(empty($array_data));

		foreach ($json_ld as $property)
		{
			$elements[$property] = $array_data[$property];
			$this->assertFalse(empty($elements[$property]));
		}

		$this->assertSame(
			'http://schema.org',
			$elements['@context']
		);
		$this->assertSame(
			'DiscussionForumPosting',
			$elements['@type']
		);
		$this->assertSame(
			$this->expected_data['url'],
			$elements['@id']
		);
		$this->assertSame(
			$this->expected_data['title'],
			$elements['headline']
		);
		$this->assertSame(
			$this->expected_data['description'],
			$elements['description']
		);
		$this->assertSame(
			$this->expected_data['image'],
			$elements['image']
		);
	}

	public function test_acp_form_settings()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', sprintf(
			'adm/index.php?i=-alfredoramos-seometadata-acp-main_module&mode=settings&sid=%s',
			$this->sid
		));

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();

		$this->assertSame(1, $crawler->filter('#seo_metadata_settings')->count());

		$this->assertTrue($form->has('seo_metadata_meta_description'));
		$this->assertSame(1, (int) $form->get('seo_metadata_meta_description')->getValue());

		$this->assertTrue($form->has('seo_metadata_desc_length'));
		$this->assertSame(160, (int) $form->get('seo_metadata_desc_length')->getValue());

		$this->assertTrue($form->has('seo_metadata_desc_strategy'));
		$this->assertSame(0, (int) $form->get('seo_metadata_desc_strategy')->getValue());

		$this->assertTrue($form->has('seo_metadata_image_strategy'));
		$this->assertSame(0, (int) $form->get('seo_metadata_image_strategy')->getValue());

		$this->assertTrue($form->has('seo_metadata_default_image'));
		$this->assertSame('seo/default_image.png', $form->get('seo_metadata_default_image')->getValue());

		$this->assertTrue($form->has('seo_metadata_local_images'));
		$this->assertSame(1, (int) $form->get('seo_metadata_local_images')->getValue());

		$this->assertTrue($form->has('seo_metadata_attachments'));
		$this->assertSame(0, (int) $form->get('seo_metadata_attachments')->getValue());

		$this->assertTrue($form->has('seo_metadata_prefer_attachments'));
		$this->assertSame(0, (int) $form->get('seo_metadata_prefer_attachments')->getValue());

		$this->assertTrue($form->has('seo_metadata_open_graph'));
		$this->assertSame(1, (int) $form->get('seo_metadata_open_graph')->getValue());

		$this->assertTrue($form->has('seo_metadata_json_ld'));
		$this->assertSame(1, (int) $form->get('seo_metadata_json_ld')->getValue());
	}

	public function test_extracted_image_first_found_local()
	{
		$this->login();

		$data = [
			'title' => 'SEO Metadata Functional Test 1',
			'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In varius augue ut enim eleifend varius id eget nisl. Suspendisse potenti. Vivamus fringilla tellus consequat lectus venenatis faucibus. Aliquam hendrerit eleifend turpis et ultrices. Cras accumsan, dui sollicitudin faucibus auctor, nunc tellus viverra ex, id viverra nulla enim eget neque. Praesent gravida magna vitae erat convallis dictum. Sed ante lacus, gravida et pharetra vel, porttitor non leo. Sed auctor dolor et ullamcorper consectetur. Ut leo lacus, aliquam at dui eget, convallis tempor sapien. Integer sed lectus quis augue ultricies maximus sit amet nec erat. Duis odio odio, tincidunt quis porta eget, vulputate at eros. Etiam bibendum fringilla libero, sed lobortis lorem placerat eget'.PHP_EOL.
				'[img]https://dummyimage.com/250x250/fff/000.jpg[/img]'.PHP_EOL.
				'[img]https://dummyimage.com/600x400/000/fff.png[/img]'
		];

		$post = $this->create_topic(
			2,
			$data['title'],
			$data['body']
		);

		$crawler = self::request('GET', vsprintf(
			'viewtopic.php?t=%d&sid=%s',
			[
				$post['topic_id'],
				$this->sid
			]
		));

		$image = 'http://localhost/images/seo/default_image.png';
		$elements = [];

		// Open Graph image
		$elements['opengraph'] = $crawler->filter('meta[property="og:image"]');

		// JSON-LD image
		$elements['jsonld'] = $crawler->filter('script[type="application/ld+json"]');
		$elements['jsonld'] = json_decode($elements['jsonld']->text(), true);

		$this->assertFalse(empty($elements['opengraph']->attr('content')));
		$this->assertSame(
			$image,
			$elements['opengraph']->attr('content')
		);

		$this->assertFalse(empty($elements['jsonld']['image']));
		$this->assertSame(
			$image,
			$elements['jsonld']['image']
		);
	}

	public function test_extracted_image_fallback()
	{
		$this->login();

		$data = [
			'title' => 'SEO Metadata Functional Test 2',
			'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In varius augue ut enim eleifend varius id eget nisl. Suspendisse potenti. Vivamus fringilla tellus consequat lectus venenatis faucibus. Aliquam hendrerit eleifend turpis et ultrices. Cras accumsan, dui sollicitudin faucibus auctor, nunc tellus viverra ex, id viverra nulla enim eget neque. Praesent gravida magna vitae erat convallis dictum. Sed ante lacus, gravida et pharetra vel, porttitor non leo. Sed auctor dolor et ullamcorper consectetur. Ut leo lacus, aliquam at dui eget, convallis tempor sapien. Integer sed lectus quis augue ultricies maximus sit amet nec erat. Duis odio odio, tincidunt quis porta eget, vulputate at eros. Etiam bibendum fringilla libero, sed lobortis lorem placerat eget'.PHP_EOL.
				'[img]https://dummyimage.com/150x150/fff/000.jpg[/img]'.PHP_EOL.
				'[img]https://dummyimage.com/60x40/000/fff.png[/img]'
		];

		$post = $this->create_topic(
			2,
			$data['title'],
			$data['body']
		);

		$crawler = self::request('GET', vsprintf(
			'viewtopic.php?t=%d&sid=%s',
			[
				$post['topic_id'],
				$this->sid
			]
		));

		$image = 'http://localhost/images/seo/default_image.png';
		$elements = [];

		// Open Graph image
		$elements['opengraph'] = $crawler->filter('meta[property="og:image"]');

		// JSON-LD image
		$elements['jsonld'] = $crawler->filter('script[type="application/ld+json"]');
		$elements['jsonld'] = json_decode($elements['jsonld']->text(), true);

		$this->assertFalse(empty($elements['opengraph']->attr('content')));
		$this->assertSame(
			$image,
			$elements['opengraph']->attr('content')
		);

		$this->assertFalse(empty($elements['jsonld']['image']));
		$this->assertSame(
			$image,
			$elements['jsonld']['image']
		);
	}
}
