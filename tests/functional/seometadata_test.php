<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@protonmail.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\tests\functional;

/**
 * @group functional
 */
class seometadata_test extends \phpbb_functional_test_case
{
	use functional_test_case_trait;

	protected function init() {}

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
		$this->update_config_value(
			'seo_metadata_twitter_publisher',
			'@varsmx'
		);

		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=1&sid=%s',
			$this->sid
		));

		$elements = [];
		$twitter_cards = [
			'twitter' => [
				'card',
				'site',
				'title',
				'description',
				'image'
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
		$this->assertSame(
			'@varsmx',
			$elements['site']->attr('content')
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
			'https://schema.org',
			$elements['@context']
		);
		$this->assertSame(
			'DiscussionForumPosting',
			$elements['@type']
		);
		$this->assertSame(
			'http://localhost/viewtopic.php?t=1',
			$elements['url']
		);
		$this->assertTrue(
			empty(preg_match(
				'#(?:\?|&amp;)sid=' . preg_quote($this->sid) . '#',
				$elements['url']
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
			'This is an example post in your phpBB3 installation. Everything seems to be working. You may delete this post if you like and continue to set up your board. Dur',
			$elements['text']
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
			'Your first forum',
			$elements['articleSection']
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

	public function test_extracted_image_first_found_local()
	{
		$this->login();

		$data = [
			'title' => 'SEO Metadata functional test 1',
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
		$elements['open_graph'] = $crawler->filter('meta[property="og:image"]');

		// Twitter Cards image
		$elements['twitter_cards'] = $crawler->filter('meta[name="twitter:image"]');

		// JSON-LD image
		$elements['json_ld'] = $crawler->filter('script[type="application/ld+json"]');
		$elements['json_ld'] = json_decode($elements['json_ld']->text(), true);

		$this->assertFalse(empty($elements['open_graph']->attr('content')));
		$this->assertSame(
			'http://localhost/images/default_image.jpg',
			$elements['open_graph']->attr('content')
		);

		$this->assertFalse(empty($elements['twitter_cards']->attr('content')));
		$this->assertSame(
			'http://localhost/images/default_image.jpg',
			$elements['twitter_cards']->attr('content')
		);

		$this->assertFalse(empty($elements['json_ld']['image']));
		$this->assertSame(
			'http://localhost/images/default_image.jpg',
			$elements['json_ld']['image']
		);
	}

	public function test_extracted_image_fallback()
	{
		$this->login();

		$data = [
			'title' => 'SEO Metadata functional test 2',
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
		$elements['open_graph'] = $crawler->filter('meta[property="og:image"]');

		// Twitter Cards image
		$elements['twitter_cards'] = $crawler->filter('meta[name="twitter:image"]');

		// JSON-LD image
		$elements['json_ld'] = $crawler->filter('script[type="application/ld+json"]');
		$elements['json_ld'] = json_decode($elements['json_ld']->text(), true);

		$this->assertFalse(empty($elements['open_graph']->attr('content')));
		$this->assertSame(
			'http://localhost/images/default_image.jpg',
			$elements['open_graph']->attr('content')
		);

		$this->assertFalse(empty($elements['twitter_cards']->attr('content')));
		$this->assertSame(
			'http://localhost/images/default_image.jpg',
			$elements['twitter_cards']->attr('content')
		);

		$this->assertFalse(empty($elements['json_ld']['image']));
		$this->assertSame(
			'http://localhost/images/default_image.jpg',
			$elements['json_ld']['image']
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

		$elements = [];

		// Open Graph image
		$elements['open_graph'] = $crawler->filter('meta[property="og:image"]');

		// Twitter Cards image
		$elements['twitter_cards'] = $crawler->filter('meta[name="twitter:image"]');

		// JSON-LD image
		$elements['json_ld'] = $crawler->filter('script[type="application/ld+json"]');
		$elements['json_ld'] = json_decode($elements['json_ld']->text(), true);

		$this->assertFalse(empty($elements['open_graph']->attr('content')));
		$this->assertSame(
			'https://help.duckduckgo.com/duckduckgo-help-pages/images/fb5a7e58b23313e8c852b2f9ec6a2f6a.png',
			$elements['open_graph']->attr('content')
		);

		$this->assertFalse(empty($elements['twitter_cards']->attr('content')));
		$this->assertSame(
			'https://help.duckduckgo.com/duckduckgo-help-pages/images/fb5a7e58b23313e8c852b2f9ec6a2f6a.png',
			$elements['twitter_cards']->attr('content')
		);

		$this->assertFalse(empty($elements['json_ld']['image']));
		$this->assertSame(
			'https://help.duckduckgo.com/duckduckgo-help-pages/images/fb5a7e58b23313e8c852b2f9ec6a2f6a.png',
			$elements['json_ld']['image']
		);

		$this->update_config_value(
			'seo_metadata_local_images',
			'1'
		);
	}

	public function test_extracted_image_parameters()
	{
		$this->login();

		$this->update_config_value(
			'seo_metadata_local_images',
			'0'
		);

		$data = [
			'title' => 'SEO Metadata functional test 4',
			'body' => '[img]https://via.placeholder.com/600x300/08c/fff.jpg?text=placeholder[/img]'

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
		$elements['open_graph'] = $crawler->filter('meta[property="og:image"]');

		// Twitter Cards image
		$elements['twitter_cards'] = $crawler->filter('meta[name="twitter:image"]');

		// JSON-LD image
		$elements['json_ld'] = $crawler->filter('script[type="application/ld+json"]');
		$elements['json_ld'] = json_decode($elements['json_ld']->text(), true);

		$this->assertFalse(empty($elements['open_graph']->attr('content')));
		$this->assertSame(
			'https://via.placeholder.com/600x300/08c/fff.jpg?text=placeholder',
			$elements['open_graph']->attr('content')
		);

		$this->assertFalse(empty($elements['twitter_cards']->attr('content')));
		$this->assertSame(
			'https://via.placeholder.com/600x300/08c/fff.jpg?text=placeholder',
			$elements['twitter_cards']->attr('content')
		);

		$this->assertFalse(empty($elements['json_ld']['image']));
		$this->assertSame(
			'https://via.placeholder.com/600x300/08c/fff.jpg?text=placeholder',
			$elements['json_ld']['image']
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
			'open_graph' => $crawler->filter('meta[property="og:description"]'),
			'twitter_cards' => $crawler->filter('meta[name="twitter:description"]')
		];

		$description = 'Description of your first forum.';

		$this->assertSame($description, $elements['meta_description']->attr('content'));
		$this->assertSame($description, $elements['open_graph']->attr('content'));
		$this->assertSame($description, $elements['twitter_cards']->attr('content'));
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
			'title' => 'SEO Metadata functional test 5',
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
			'twitter_cards' => [
				'description' => $crawler->filter('meta[name="twitter:description"]'),
				'image' => $crawler->filter('meta[name="twitter:image"]'),
			],
			'json_ld' => json_decode($crawler->filter('script[type="application/ld+json"]')->text(), true)
		];

		$this->assertSame(
			'Welcome to phpBB3 Post reply test',
			$elements['meta_description']->attr('content')
		);

		$this->assertSame(
			'Welcome to phpBB3 Post reply test',
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
			'Welcome to phpBB3 Post reply test',
			$elements['twitter_cards']['description']->attr('content')
		);
		$this->assertSame(
			'https://help.duckduckgo.com/duckduckgo-help-pages/images/fb5a7e58b23313e8c852b2f9ec6a2f6a.png',
			$elements['twitter_cards']['image']->attr('content')
		);

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

	public function test_summary_large_image()
	{
		$this->login();
		$this->update_config_value(
			'seo_metadata_open_graph',
			'0'
		);

		$data = [
			'title' => 'SEO Metadata functional test 5',
			'body' => '[img]http://localhost/images/wide_image.jpg[/img]'

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
		$twitter_cards = [
			'twitter' => [
				'card',
				'image'
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

		$this->assertSame(1, $elements['card']->count());
		$this->assertSame('summary_large_image', $elements['card']->attr('content'));

		$this->assertSame(1, $elements['image']->count());
		$this->assertSame('http://localhost/images/wide_image.jpg', $elements['image']->attr('content'));

		$this->update_config_value(
			'seo_metadata_open_graph',
			'1'
		);
	}

	public function test_short_description()
	{
		$this->login();

		$data = [
			'title' => 'SEO Metadata functional test 6',
			'body' => 'Sample text [img]http://localhost/images/wide_image.jpg[/img]'

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

		$script = $crawler->filter('script[type="application/ld+json"]');
		$json =  json_decode($script->text(), true);

		$elements = [
			$crawler->filter('meta[name="description"]')->attr('content'),
			$crawler->filter('meta[property="og:description"]')->attr('content'),
			$crawler->filter('meta[name="twitter:description"]')->attr('content'),
			$json['description']
		];

		foreach ($elements as $value)
		{
			$this->assertSame(
				$data['title'] . ' Sample text',
				$value
			);
		}
	}
}
