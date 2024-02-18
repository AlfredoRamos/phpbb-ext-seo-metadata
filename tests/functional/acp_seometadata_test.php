<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@proton.me>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\tests\functional;

/**
 * @group functional
 */
class acp_seometadata_test extends \phpbb_functional_test_case
{
	use functional_test_case_trait;

	protected function init()
	{
		$this->login();
		$this->admin_login();
	}

	public function test_acp_form_settings()
	{
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

		$this->assertSame(0, $crawler->filter('#seo_metadata_default_image_width')->count());

		$this->assertSame(0, $crawler->filter('#seo_metadata_default_image_height')->count());

		$this->assertSame(0, $crawler->filter('#seo_metadata_default_image_type')->count());

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

		$this->assertSame(0, $crawler->filter('#seo_metadata_json_ld_logo_width')->count());

		$this->assertSame(0, $crawler->filter('#seo_metadata_json_ld_logo_height')->count());
	}

	public function test_update_acp_form_settings()
	{
		$crawler = self::request('GET', sprintf(
			'adm/index.php?i=-alfredoramos-seometadata-acp-main_module&mode=settings&sid=%s',
			$this->sid
		));

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'seo_metadata_default_image' => 'default_image.jpg',
			'seo_metadata_json_ld_logo' => 'default_logo.jpg'
		]);

		self::submit($form);

		// Check the new values in the ACP form
		$crawler = self::request('GET', sprintf(
			'adm/index.php?i=-alfredoramos-seometadata-acp-main_module&mode=settings&sid=%s',
			$this->sid
		));

		// Extract image width, height and MIME type
		$this->assertSame(250, (int) $crawler->filter('#seo_metadata_default_image_width')->text());
		$this->assertSame(250, (int) $crawler->filter('#seo_metadata_default_image_height')->text());
		$this->assertSame('image/jpeg', $crawler->filter('#seo_metadata_default_image_type')->text());
		$this->assertSame(150, (int) $crawler->filter('#seo_metadata_json_ld_logo_width')->text());
		$this->assertSame(150, (int) $crawler->filter('#seo_metadata_json_ld_logo_height')->text());

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
			'title' => 'SEO Metadata functional test 3',
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

	public function test_forum_image()
	{
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
			'title' => 'SEO Metadata functional test 4',
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
}
