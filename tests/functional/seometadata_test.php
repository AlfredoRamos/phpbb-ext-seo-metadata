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

	public function setUp()
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

	public function test_meta_description()
	{
		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=1&sid=%s',
			$this->sid
		));

		$element = $crawler->filter('meta[name="description"]');

		$this->assertSame(1, $element->count());
		$this->assertSame(
			'This is an example post in your phpBB3 installation. Everything seems to be working. You may delete this post if you like and continue to set up your board. Dur',
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
			'en_GB',
			$elements['locale']->attr('content')
		);
		$this->assertSame(
			'yourdomain.com',
			$elements['site_name']->attr('content')
		);
		$this->assertSame(
			'http://localhost/viewtopic.php?t=1',
			$elements['url']->attr('content')
		);
		$this->assertTrue(
			empty(preg_match(
				'#(?:\?|&amp;)sid=' . preg_quote($this->sid) . '#',
				$elements['url']->attr('content')
			))
		);
		$this->assertSame(
			'article',
			$elements['type']->attr('content')
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
			'http://localhost/images/default_image.jpg',
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

		$script = $crawler->filter('script[type="application/ld+json"]');
		$elements = json_decode($script->text(), true);

		$this->assertSame(1, $script->count());
		$this->assertFalse(empty($elements));

		$this->assertSame(
			'http://schema.org',
			$elements['@context']
		);
		$this->assertSame(
			'DiscussionForumPosting',
			$elements['@type']
		);
		$this->assertSame(
			'http://localhost/viewtopic.php?t=1',
			$elements['@id']
		);
		$this->assertTrue(
			empty(preg_match(
				'#(?:\?|&amp;)sid=' . preg_quote($this->sid) . '#',
				$elements['@id']
			))
		);
		$this->assertSame(
			'Welcome to phpBB3',
			$elements['headline']
		);
		$this->assertSame(
			'This is an example post in your phpBB3 installation. Everything seems to be working. You may delete this post if you like and continue to set up your board. Dur',
			$elements['description']
		);
		$this->assertSame(
			'http://localhost/images/default_image.jpg',
			$elements['image']
		);
		$this->assertSame(
			'Person',
			$elements['author']['@type']
		);
		$this->assertSame(
			'admin',
			$elements['author']['name']
		);
		$this->assertSame(
			1,
			preg_match(
				'#^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$#',
				$elements['datePublished']
			)
		);
		$this->assertSame(
			'Organization',
			$elements['publisher']['@type']
		);
		$this->assertSame(
			'yourdomain.com',
			$elements['publisher']['name']
		);
		$this->assertSame(
			'http://localhost',
			$elements['publisher']['url']
		);
		$this->assertSame(
			'ImageObject',
			$elements['publisher']['logo']['@type']
		);
		$this->assertSame(
			'http://localhost/images/default_logo.jpg',
			$elements['publisher']['logo']['url']
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
		$this->assertSame('default_image.jpg', $form->get('seo_metadata_default_image')->getValue());

		$this->assertTrue($form->has('seo_metadata_default_image_width'));
		$this->assertSame(0, (int) $form->get('seo_metadata_default_image_width')->getValue());

		$this->assertTrue($form->has('seo_metadata_default_image_height'));
		$this->assertSame(0, (int) $form->get('seo_metadata_default_image_height')->getValue());

		$this->assertTrue($form->has('seo_metadata_default_image_type'));
		$this->assertSame('', $form->get('seo_metadata_default_image_type')->getValue());

		$this->assertTrue($form->has('seo_metadata_local_images'));
		$this->assertSame(1, (int) $form->get('seo_metadata_local_images')->getValue());

		$this->assertTrue($form->has('seo_metadata_attachments'));
		$this->assertSame(0, (int) $form->get('seo_metadata_attachments')->getValue());

		$this->assertTrue($form->has('seo_metadata_prefer_attachments'));
		$this->assertSame(0, (int) $form->get('seo_metadata_prefer_attachments')->getValue());

		$this->assertTrue($form->has('seo_metadata_post_metadata'));
		$this->assertSame(0, (int) $form->get('seo_metadata_post_metadata')->getValue());

		$this->assertTrue($form->has('seo_metadata_open_graph'));
		$this->assertSame(1, (int) $form->get('seo_metadata_open_graph')->getValue());

		$this->assertTrue($form->has('seo_metadata_facebook_application'));
		$this->assertSame('', $form->get('seo_metadata_facebook_application')->getValue());

		$this->assertTrue($form->has('seo_metadata_facebook_publisher'));
		$this->assertSame('', $form->get('seo_metadata_facebook_publisher')->getValue());

		$this->assertTrue($form->has('seo_metadata_twitter_cards'));
		$this->assertSame(1, (int) $form->get('seo_metadata_twitter_cards')->getValue());

		$this->assertTrue($form->has('seo_metadata_twitter_publisher'));
		$this->assertSame('', $form->get('seo_metadata_twitter_publisher')->getValue());

		$this->assertTrue($form->has('seo_metadata_json_ld'));
		$this->assertSame(1, (int) $form->get('seo_metadata_json_ld')->getValue());

		$this->assertTrue($form->has('seo_metadata_json_ld_logo'));
		$this->assertSame('default_logo.jpg', $form->get('seo_metadata_json_ld_logo')->getValue());

		$this->assertTrue($form->has('seo_metadata_json_ld_logo_width'));
		$this->assertSame(0, (int) $form->get('seo_metadata_json_ld_logo_width')->getValue());

		$this->assertTrue($form->has('seo_metadata_json_ld_logo_height'));
		$this->assertSame(0, (int) $form->get('seo_metadata_json_ld_logo_height')->getValue());
	}

	public function test_update_acp_form_settings()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', sprintf(
			'adm/index.php?i=-alfredoramos-seometadata-acp-main_module&mode=settings&sid=%s',
			$this->sid
		));

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'seo_metadata_default_image' => 'default_image.jpg',
			'seo_metadata_default_image_width' => '0',
			'seo_metadata_default_image_height' => '0',
			'seo_metadata_default_image_type' => '',
			'seo_metadata_json_ld_logo' => 'default_logo.jpg',
			'seo_metadata_json_ld_logo_width' => '0',
			'seo_metadata_json_ld_logo_height' => '0',
		]);

		self::submit($form);

		// Check the new values in the ACP form
		$crawler = self::request('GET', sprintf(
			'adm/index.php?i=-alfredoramos-seometadata-acp-main_module&mode=settings&sid=%s',
			$this->sid
		));

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();

		// Try to guess default image width, height and MIME type
		$this->assertSame(250, (int) $form->get('seo_metadata_default_image_width')->getValue());
		$this->assertSame(250, (int) $form->get('seo_metadata_default_image_height')->getValue());
		$this->assertSame('image/jpeg', $form->get('seo_metadata_default_image_type')->getValue());
		$this->assertSame(150, (int) $form->get('seo_metadata_json_ld_logo_width')->getValue());
		$this->assertSame(150, (int) $form->get('seo_metadata_json_ld_logo_height')->getValue());

		// Check the new values in topics (fallback image)
		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=1&sid=%s',
			$this->sid
		));

		$elements = [
			'open_graph' => [
				'image' => $crawler->filter('meta[property="og:image"]'),
				'width' => $crawler->filter('meta[property="og:image:width"]'),
				'height' => $crawler->filter('meta[property="og:image:height"]'),
				'type' => $crawler->filter('meta[property="og:image:type"]')
			],
			'json_ld' => json_decode($crawler->filter('script[type="application/ld+json"]')->text(), true)
		];

		$this->assertSame(1, $elements['open_graph']['image']->count());
		$this->assertSame(
			'http://localhost/images/default_image.jpg',
			$elements['open_graph']['image']->attr('content')
		);

		$this->assertSame(1, $elements['open_graph']['width']->count());
		$this->assertSame(250, (int) $elements['open_graph']['width']->attr('content'));

		$this->assertSame(1, $elements['open_graph']['height']->count());
		$this->assertSame(250, (int) $elements['open_graph']['height']->attr('content'));

		$this->assertSame(1, $elements['open_graph']['type']->count());
		$this->assertSame('image/jpeg', $elements['open_graph']['type']->attr('content'));

		$this->assertSame(
			'http://localhost/images/default_logo.jpg',
			$elements['json_ld']['publisher']['logo']['url']
		);
		$this->assertSame(150, (int) $elements['json_ld']['publisher']['logo']['width']);
		$this->assertSame(150, (int) $elements['json_ld']['publisher']['logo']['height']);

		// Check the new values in topics (remote image)
		$this->update_config_value(
			'seo_metadata_local_images',
			'0'
		);

		$data = [
			'title' => 'SEO Metadata Functional Test 3',
			'body' => '[img]https://help.duckduckgo.com/duckduckgo-help-pages/images/fb5a7e58b23313e8c852b2f9ec6a2f6a.png[/img]'

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

		$elements = [
			'open_graph' => [
				'image' => $crawler->filter('meta[property="og:image"]'),
				'width' => $crawler->filter('meta[property="og:image:width"]'),
				'height' => $crawler->filter('meta[property="og:image:height"]'),
				'type' => $crawler->filter('meta[property="og:image:type"]')
			],
			'json_ld' => json_decode($crawler->filter('script[type="application/ld+json"]')->text(), true)
		];

		$this->assertSame(1, $elements['open_graph']['image']->count());
		$this->assertSame(
			'https://help.duckduckgo.com/duckduckgo-help-pages/images/fb5a7e58b23313e8c852b2f9ec6a2f6a.png',
			$elements['open_graph']['image']->attr('content')
		);

		$this->assertSame(1, $elements['open_graph']['width']->count());
		$this->assertSame(250, (int) $elements['open_graph']['width']->attr('content'));

		$this->assertSame(1, $elements['open_graph']['height']->count());
		$this->assertSame(200, (int) $elements['open_graph']['height']->attr('content'));

		$this->assertSame(1, $elements['open_graph']['type']->count());
		$this->assertSame('image/png', $elements['open_graph']['type']->attr('content'));

		$this->assertSame(
			'http://localhost/images/default_logo.jpg',
			$elements['json_ld']['publisher']['logo']['url']
		);
		$this->assertSame(150, (int) $elements['json_ld']['publisher']['logo']['width']);
		$this->assertSame(150, (int) $elements['json_ld']['publisher']['logo']['height']);

		$this->update_config_value(
			'seo_metadata_local_images',
			'1'
		);
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

		$elements = [];

		// Open Graph image
		$elements['opengraph'] = $crawler->filter('meta[property="og:image"]');

		// JSON-LD image
		$elements['jsonld'] = $crawler->filter('script[type="application/ld+json"]');
		$elements['jsonld'] = json_decode($elements['jsonld']->text(), true);

		$this->assertFalse(empty($elements['opengraph']->attr('content')));
		$this->assertSame(
			'http://localhost/images/default_image.jpg',
			$elements['opengraph']->attr('content')
		);

		$this->assertFalse(empty($elements['jsonld']['image']));
		$this->assertSame(
			'http://localhost/images/default_image.jpg',
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

		$elements = [];

		// Open Graph image
		$elements['opengraph'] = $crawler->filter('meta[property="og:image"]');

		// JSON-LD image
		$elements['jsonld'] = $crawler->filter('script[type="application/ld+json"]');
		$elements['jsonld'] = json_decode($elements['jsonld']->text(), true);

		$this->assertFalse(empty($elements['opengraph']->attr('content')));
		$this->assertSame(
			'http://localhost/images/default_image.jpg',
			$elements['opengraph']->attr('content')
		);

		$this->assertFalse(empty($elements['jsonld']['image']));
		$this->assertSame(
			'http://localhost/images/default_image.jpg',
			$elements['jsonld']['image']
		);
	}

	public function test_extracted_image_remote()
	{
		$this->login();

		$this->update_config_value(
			'seo_metadata_local_images',
			'0'
		);

		$data = [
			'title' => 'SEO Metadata Functional Test 3',
			'body' => '[img]https://help.duckduckgo.com/duckduckgo-help-pages/images/fb5a7e58b23313e8c852b2f9ec6a2f6a.png[/img]'

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

		$elements = [];

		// Open Graph image
		$elements['opengraph'] = $crawler->filter('meta[property="og:image"]');

		// JSON-LD image
		$elements['jsonld'] = $crawler->filter('script[type="application/ld+json"]');
		$elements['jsonld'] = json_decode($elements['jsonld']->text(), true);

		$this->assertFalse(empty($elements['opengraph']->attr('content')));
		$this->assertSame(
			'https://help.duckduckgo.com/duckduckgo-help-pages/images/fb5a7e58b23313e8c852b2f9ec6a2f6a.png',
			$elements['opengraph']->attr('content')
		);

		$this->assertFalse(empty($elements['jsonld']['image']));
		$this->assertSame(
			'https://help.duckduckgo.com/duckduckgo-help-pages/images/fb5a7e58b23313e8c852b2f9ec6a2f6a.png',
			$elements['jsonld']['image']
		);

		$this->update_config_value(
			'seo_metadata_local_images',
			'1'
		);
	}

	public function test_forum_description()
	{
		$crawler = self::request('GET', sprintf(
			'viewforum.php?f=2&sid=%s',
			$this->sid
		));

		$elements = [
			'meta_description' => $crawler->filter('meta[name="description"]'),
			'open_graph' => $crawler->filter('meta[property="og:description"]')
		];

		$description = 'Description of your first forum.';

		$this->assertSame($description, $elements['meta_description']->attr('content'));
		$this->assertSame($description, $elements['open_graph']->attr('content'));
	}

	public function test_forum_image()
	{
		$this->login();
		$this->admin_login();

		// Add forum image
		$crawler = self::request('GET', sprintf(
			'adm/index.php?i=acp_forums&mode=manage&f=2&action=edit&sid=%s',
			$this->sid
		));

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'forum_image' => 'images/forum_image.jpg'
		]);

		self::submit($form);

		// Check new values
		$crawler = self::request('GET', sprintf(
			'adm/index.php?i=acp_forums&mode=manage&f=2&action=edit&sid=%s',
			$this->sid
		));

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();

		$this->assertSame('images/forum_image.jpg', $form->get('forum_image')->getValue());

		// Check forum image
		$crawler = self::request('GET', sprintf(
			'viewforum.php?f=2&sid=%s',
			$this->sid
		));

		$image = $crawler->filter('meta[property="og:image"]');

		$this->assertSame(
			'http://localhost/images/forum_image.jpg',
			$image->attr('content')
		);

		// Check topic image
		$data = [
			'title' => 'SEO Metadata Functional Test 4',
			'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce tincidunt fermentum vehicula.'

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

		$image = $crawler->filter('meta[property="og:image"]');

		$this->assertSame(
			'http://localhost/images/forum_image.jpg',
			$image->attr('content')
		);
	}

	public function test_post_reply_metadata()
	{
		$this->login();
		$this->update_config_value(
			'seo_metadata_post_metadata',
			'1'
		);
		$this->update_config_value(
			'seo_metadata_local_images',
			'0'
		);

		$data = [
			'title' => 'SEO Metadata Functional test 5',
			'body' => 'Post reply test' . PHP_EOL . PHP_EOL .
				'[img]https://help.duckduckgo.com/duckduckgo-help-pages/images/fb5a7e58b23313e8c852b2f9ec6a2f6a.png[/img]'
		];

		$post = $this->create_post(
			2,
			1,
			$data['title'],
			$data['body']
		);

		$crawler = self::request('GET', vsprintf(
			'viewtopic.php?p=%d&sid=%s',
			[
				$post['post_id'],
				$this->sid
			]
		));

		$elements = [
			'meta_description' => $crawler->filter('meta[name="description"]'),
			'open_graph' => [
				'description' => $crawler->filter('meta[property="og:description"]'),
				'image' => $crawler->filter('meta[property="og:image"]'),
				'width' => $crawler->filter('meta[property="og:image:width"]'),
				'height' => $crawler->filter('meta[property="og:image:height"]'),
				'type' => $crawler->filter('meta[property="og:image:type"]')
			],
			'json_ld' => json_decode($crawler->filter('script[type="application/ld+json"]')->text(), true)
		];

		$this->assertSame(
			'Post reply test',
			$elements['meta_description']->attr('content')
		);

		$this->assertSame(
			'Post reply test',
			$elements['open_graph']['description']->attr('content')
		);
		$this->assertSame(
			'https://help.duckduckgo.com/duckduckgo-help-pages/images/fb5a7e58b23313e8c852b2f9ec6a2f6a.png',
			$elements['open_graph']['image']->attr('content')
		);
		$this->assertSame(250, (int) $elements['open_graph']['width']->attr('content'));
		$this->assertSame(200, (int) $elements['open_graph']['height']->attr('content'));
		$this->assertSame('image/png', $elements['open_graph']['type']->attr('content'));

		$this->assertSame(
			'https://help.duckduckgo.com/duckduckgo-help-pages/images/fb5a7e58b23313e8c852b2f9ec6a2f6a.png',
			$elements['json_ld']['image']
		);

		$this->update_config_value(
			'seo_metadata_post_metadata',
			'0'
		);
		$this->update_config_value(
			'seo_metadata_local_images',
			'1'
		);
	}
}
