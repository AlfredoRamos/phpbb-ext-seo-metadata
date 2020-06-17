<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GNU GPL-2.0-only
 */

namespace alfredoramos\seometadata\includes;

use phpbb\db\driver\factory as database;
use phpbb\config\config;
use phpbb\user;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\language\language;
use phpbb\filesystem\filesystem;
use phpbb\cache\driver\driver_interface as cache;
use phpbb\controller\helper as controller_helper;
use phpbb\event\dispatcher_interface as dispatcher;
use FastImageSize\FastImageSize;

class helper
{
	/** @var \phpbb\db\driver\factory */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\filesystem\filesystem */
	protected $filesystem;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/** @var \FastImageSize\FastImageSize */
	protected $imagesize;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/** @var array */
	protected $metadata;

	/** @var array */
	protected $tables;

	/**
	 * Helper constructor.
	 *
	 * @param \phpbb\db\driver\factory				$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\user							$user
	 * @param \phpbb\request\request				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \phpbb\cache\driver\driver_interface	$cache
	 * @param \phpbb\controller\helper				$controller_helper
	 * @param \phpbb\event\dispatcher_interface		$dispatcher
	 * @param \FastImageSize\FastImageSize			$imagesize
	 * @param string								$root_path
	 * @param string								$php_ext
	 * @param string								$posts_table
	 * @param string								$attachments_table
	 *
	 * @return void
	 */
	public function __construct(database $db, config $config, user $user, request $request, template $template, language $language, filesystem $filesystem, cache $cache, controller_helper $controller_helper, dispatcher $dispatcher, FastImageSize $imagesize, $root_path, $php_ext, $posts_table, $attachments_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
		$this->request = $request;
		$this->template = $template;
		$this->language = $language;
		$this->filesystem = $filesystem;
		$this->cache = $cache;
		$this->controller_helper = $controller_helper;
		$this->dispatcher = $dispatcher;
		$this->imagesize = $imagesize;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->metadata = [];

		// Assign tables
		if (empty($this->tables))
		{
			$this->tables = [
				'posts' => $posts_table,
				'attachments' => $attachments_table
			];
		}
	}

	/**
	 * Add or replace metadata.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function set_metadata($data = [])
	{
		// Set initial metadata
		if (empty($this->metadata))
		{
			$default = [
				'description' => $this->clean_description($this->config['site_desc']),
				'image' => [
					'url' => $this->clean_image($this->config['seo_metadata_default_image']),
					'width' => (int) $this->config['seo_metadata_default_image_width'],
					'height' => (int) $this->config['seo_metadata_default_image_height'],
					'type' => trim($this->config['seo_metadata_default_image_type'])
				],
				'url' => $this->clean_url($this->controller_helper->get_current_url())
			];
			$this->metadata = [
				'meta_description' => [
					'description' => $default['description']
				],
				'twitter_cards' => [
					'twitter:card' => $this->is_wide_image(
						$default['image']['width'],
						$default['image']['height']
					) ? 'summary_large_image' : 'summary',
					'twitter:site' => trim($this->config['seo_metadata_twitter_publisher']),
					'twitter:title' => '',
					'twitter:description' => $default['description'],
					'twitter:image' => $default['image']['url']
				],
				'open_graph' => [
					'fb:app_id' => trim($this->config['seo_metadata_facebook_application']),
					'og:locale' => $this->extract_locale($this->language->lang('USER_LANG')),
					'og:site_name' => trim($this->config['sitename']),
					'og:url' => $default['url'],
					'og:type' => 'website',
					'og:title' => '',
					'og:description' => $default['description'],
					'og:image' => $default['image']['url'],
					'og:image:type' => $default['image']['type'],
					'og:image:width' => $default['image']['width'],
					'og:image:height' => $default['image']['height'],
					'article:published_time' => '',
					'article:section' => '',
					'article:publisher' => trim($this->config['seo_metadata_facebook_publisher'])
				],
				'json_ld' => [
					'@context' => 'http://schema.org',
					'@type' => 'DiscussionForumPosting',
					'@id' => $default['url'],
					'headline' => '',
					'description' => $default['description'],
					'image' => $default['image']['url'],
					'author' => [
						'@type' => 'Person',
						'name' => ''
					],
					'datePublished' => '',
					'publisher' => [
						'@type' => 'Organization',
						'name' => trim($this->config['sitename']),
						'url' => generate_board_url(),
						'logo' => [
							'@type' => 'ImageObject',
							'url' => $this->clean_image($this->config['seo_metadata_json_ld_logo']),
							'width' => (int) $this->config['seo_metadata_json_ld_logo_width'],
							'height' => (int) $this->config['seo_metadata_json_ld_logo_height']
						]
					]
				]
			];
		}

		// Remove empty values
		$data = $this->filter_empty_items($data);

		// Map values to correct properties
		foreach ($data as $key => $value)
		{
			if (is_string($value))
			{
				$value = trim($value);
			}
			else if (is_array($value))
			{
				$value = array_map('trim', $value);
			}

			switch ($key)
			{
				case 'title':
					$this->metadata['open_graph']['og:title'] = $value;
					$this->metadata['twitter_cards']['twitter:title'] = $value;
					$this->metadata['json_ld']['headline'] = $value;
				break;

				case 'description':
					$this->metadata['meta_description']['description'] = $value;
					$this->metadata['open_graph']['og:description'] = $value;
					$this->metadata['twitter_cards']['twitter:description'] = $value;
					$this->metadata['json_ld']['description'] = $value;
				break;

				case 'image':
					if (isset($value['url']))
					{
						$this->metadata['open_graph']['og:image'] = $value['url'];
						$this->metadata['twitter_cards']['twitter:image'] = $value['url'];
						$this->metadata['json_ld']['image'] = $value['url'];
					}

					if (isset($value['type']))
					{
						$this->metadata['open_graph']['og:image:type'] = $value['type'];
					}

					if (isset($value['width'], $value['height']))
					{
						$value['width'] = (int) $value['width'];
						$value['height'] = (int) $value['height'];

						$this->metadata['open_graph']['og:image:width'] = $value['width'];
						$this->metadata['open_graph']['og:image:height'] = $value['height'];
						$this->metadata['twitter_cards']['twitter:card'] = $this->is_wide_image(
							$value['width'], $value['height']
						) ? 'summary_large_image' : 'summary';
					}
				break;

				case 'published_time':
					$value = date('c', (int) $value);
					$this->metadata['open_graph']['og:type'] = 'article';
					$this->metadata['open_graph']['article:published_time'] = $value;
					$this->metadata['json_ld']['datePublished'] = $value;
				break;

				case 'section':
					$this->metadata['open_graph']['article:section'] = $value;
				break;

				case 'author':
					$this->metadata['json_ld']['author']['name'] = $value;
				break;
			}
		}
	}

	/**
	 * Get internal metadata.
	 *
	 * @param string $key (Optional)
	 *
	 * @return array
	 */
	public function get_metadata($key = '')
	{
		if (!empty($key))
		{
			if (empty($this->metadata[$key]))
			{
				return [];
			}

			return $this->metadata[$key];
		}

		return $this->metadata;
	}

	/**
	 * Assign or update template variables.
	 *
	 * @return void
	 */
	public function metadata_template_vars()
	{
		$this->template->destroy_block_vars('SEO_METADATA');
		$data = $this->get_metadata();

		// Twitter Cards can use Open Graph data
		if ((int) $this->config['seo_metadata_open_graph'] === 1 &&
			(int) $this->config['seo_metadata_twitter_cards'] === 1)
		{
			unset(
				$data['twitter_cards']['twitter:title'],
				$data['twitter_cards']['twitter:description'],
				$data['twitter_cards']['twitter:image']
			);
		}

		// Open Graph extra check for default image
		if (empty($data['open_graph']['og:image']))
		{
			unset(
				$data['open_graph']['og:image:type'],
				$data['open_graph']['og:image:width'],
				$data['open_graph']['og:image:height']
			);
		}

		// Open Graph article metadata
		if ($data['open_graph']['og:type'] !== 'article')
		{
			unset(
				$data['open_graph']['article:published_time'],
				$data['open_graph']['article:section'],
				$data['open_graph']['article:publisher']
			);
		}

		// JSON-LD author
		if (empty($data['json_ld']['author']['name']))
		{
			unset($data['json_ld']['author']);
		}

		// JSON-LD logo
		if (empty($data['json_ld']['publisher']['logo']['url']))
		{
			unset($data['json_ld']['publisher']['logo']);
		}

		// Ignore disabled options
		foreach ($data as $key => $value)
		{
			if ((int) $this->config[sprintf('seo_metadata_%s', $key)] !== 1 ||
				empty($value))
			{
				unset($data[$key]);
				continue;
			}
		}

		// Remove empty values
		$data = $this->filter_empty_items($data);

		// Assign data to template
		foreach ($data as $key => $value)
		{
			$this->template->assign_block_vars(
				'SEO_METADATA',
				[
					'NAME' => strtoupper($key),
				]
			);

			if ($key === 'json_ld')
			{
				$this->template->assign_block_vars(
					sprintf('SEO_METADATA.%s', strtoupper($key)),
					[
						'CONTENT' => json_encode($data[$key], JSON_UNESCAPED_SLASHES)
					]
				);
				continue;
			}
			else
			{
				foreach ($value as $k => $v)
				{
					$this->template->assign_block_vars(
						sprintf('SEO_METADATA.%s', strtoupper($key)),
						[
							'PROPERTY' => $k,
							'CONTENT' => $v
						]
					);
				}
			}
		}
	}

	/**
	 * Clean text to be used as description.
	 *
	 * @param string $description
	 *
	 * @return string
	 */
	public function clean_description($description = '')
	{
		// Cast values
		$description = trim($description);
		$max_length = abs((int) $this->config['seo_metadata_desc_length']);
		$strategy = abs((int) $this->config['seo_metadata_desc_strategy']);

		if (empty($description))
		{
			return '';
		}

		// Ensure it's XML
		if (!preg_match('#^<[rt][ >]#', $description))
		{
			$description = sprintf('<t>%s</t>', $description);
		}

		// Try to fix XML
		if (!$this->is_valid_xml($description))
		{
			$uid = $bitfield = $flags = null;
			generate_text_for_storage($description, $uid, $bitfield, $flags, true, true, true);
			$description = generate_text_for_display($description, $uid, $bitfield, $flags);
		}

		// Global encoding
		$encoding = 'UTF-8';

		// DOM manipulation
		$dom = new \DOMDocument;
		$dom->loadXML($description);
		$xpath = new \DOMXPath($dom);

		// Remove images
		foreach ($xpath->query('//IMG') as $node)
		{
			if (empty($node->nodeType) || empty($node->parentNode))
			{
				continue;
			}

			$node->parentNode->removeChild($node);
		}

		// Remove attachments
		foreach ($xpath->query('//ATTACHMENT') as $node)
		{
			if (empty($node->nodeType) || empty($node->parentNode))
			{
				continue;
			}

			$node->parentNode->removeChild($node);
		}

		// Remove URLs
		foreach ($xpath->query('//URL/text()') as $node)
		{
			if (empty($node->nodeType) || empty($node->parentNode))
			{
				continue;
			}

			// Replace URL with its text
			// or remove it if it's the same as the URL
			if ($node->parentNode->getAttribute('url') === $node->nodeValue)
			{
				$node->parentNode->parentNode->removeChild($node->parentNode);
			}
			else
			{
				$node->parentNode->parentNode->replaceChild(
					$node,
					$node->parentNode
				);
			}
		}

		// Save changes
		$description = $dom->saveXML($dom->documentElement);

		/**
		 * Manipulate description after it has been cleaned.
		 *
		 * @event alfredoramos.seometadata.clean_description_after
		 *
		 * @var string description The XML string of the post description.
		 *
		 * @since 1.0.0
		 */
		$vars = ['description'];
		extract($this->dispatcher->trigger_event('alfredoramos.seometadata.clean_description_after', compact($vars)));

		// Text censoring
		$description = censor_text($description);

		// Remove BBCode
		strip_bbcode($description);

		// Remove whitespaces
		$description = trim(preg_replace('#\s+#', ' ', $description));

		// Check description length
		if (mb_strlen($description, $encoding) > $max_length)
		{
			switch ($strategy)
			{
				case 1: // Ellipsis
					$ellipsis = '…'; // UTF-8 ellipsis
					$desc_length = $max_length - strlen($ellipsis);
					$description = vsprintf(
						'%1$s%2$s',
						[
							trim(mb_substr($description, 0, $desc_length, $encoding)),
							$ellipsis
						]
					);
				break;

				case 2: // Break words
					$last_space_pos = mb_strrpos(mb_substr($description, 0, $max_length), ' ');
					$desc_length = ($last_space_pos !== false) ? $last_space_pos : $max_length;
					$description = trim(mb_substr($description, 0, $desc_length, $encoding));
				break;

				default: // Cut
					$description = trim(mb_substr($description, 0, $max_length, $encoding));
				break;
			}
		}

		// Convert HTML characters
		$description = utf8_htmlspecialchars($description);

		return $description;
	}

	/**
	 * Clean URI to be used as image URL.
	 *
	 * @param string	$uri
	 * @param bool		$images_dir
	 *
	 * @return string
	 */
	public function clean_image($uri = '', $images_dir = true)
	{
		$uri = trim($uri);

		if (empty($uri))
		{
			return '';
		}

		// It's already an URL
		if (preg_match('#^https?://#', $uri))
		{
			return $this->clean_url($uri);
		}

		// Whether to check in the /images/ path or in the root
		$dir = !empty($images_dir) ? 'images/' : '';

		// Image must exist inside the phpBB's images path
		$base_path = $this->filesystem->realpath($this->root_path . $dir);

		// \phpbb\filesystem\filesystem::resolve_path() throws warnings when called from
		// \phpbb\filesystem\filesystem::realpath() and open_basedir is set.
		//
		// It passes directories not allowed (like the web server root directory) as parameter
		// to is_link(), is_dir() and is_file()
		//
		// https://tracker.phpbb.com/browse/PHPBB3-15643
		// https://github.com/phpbb/phpbb/pull/5673
		//
		//$image_path = $this->filesystem->realpath($base_path . '/' . $uri);
		$image_path = $this->filesystem->clean_path($base_path . '/' . $uri);

		// Avoid path traversal attack
		// Image must exist and be readable
		if (empty($image_path) || strpos($image_path, $base_path) !== 0 || !$this->filesystem->is_readable($image_path))
		{
			return '';
		}

		// Relative path
		$image_path = str_replace($this->filesystem->realpath($this->root_path), '', $image_path);

		if (substr($image_path, 0, 1) === DIRECTORY_SEPARATOR)
		{
			$image_path = substr($image_path, 1);
		}

		// Absolute URL
		$url = sprintf(
			'%s/%s',
			generate_board_url(),
			$image_path
		);

		return $url;
	}

	/**
	 * Clean URL to be used as metadata.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function clean_url($url = '')
	{
		$url = trim($url);

		if (empty($url))
		{
			return '';
		}

		// Remove app.php/ from URL
		if ((int) $this->config['enable_mod_rewrite'] === 1)
		{
			$url = preg_replace('#app\.' . $this->php_ext . '/(.+)$#', '\1', $url);
		}

		// Escape ampersand
		$url = htmlspecialchars($url, ENT_COMPAT, 'UTF-8', false);

		// Remove SID from URL
		$url = str_replace($this->user->session_id, '', $url);
		$url = preg_replace('#(?:&amp;|\?)?sid=#', '', $url);
		$url = str_replace('?&amp;', '?', $url);

		// Remove index.php without parameters
		$url = preg_replace('#index\.' . $this->php_ext . '$#', '', $url);

		return $url;
	}

	/**
	 * Generates correct localization name for Open Graph.
	 *
	 * @param string $locale The localization in the format en-gb (ISO 639-1 + - + ISO 3166-2)
	 *
	 * @return string The localization in the format en_GB
	 */
	public function extract_locale($locale = '')
	{
		if (empty($locale))
		{
			return '';
		}

		// Split the language and country code
		$locale = explode('-', $locale);

		// It does not have country code
		if (empty($locale[1]))
		{
			// Set the country code to be the same as the language code
			// Examples: es_ES, de_DE, pl_PL, etc.
			$locale[1] = $locale[0];
		}

		// Uppercase country code
		$locale[1] = strtoupper($locale[1]);

		// Construct the string
		$locale = implode('_', $locale);

		// Validate the locale
		if (!in_array($locale, $this->supported_locales(), true))
		{
			// The locale is invalid, ignore it
			return '';
		}

		return $locale;
	}

	/**
	 * Generate description from post body.
	 *
	 * @param integer $post_id
	 *
	 * @return string
	 */
	public function extract_description($post_id = 0)
	{
		$post_id = (int) $post_id;

		if (empty($post_id))
		{
			return '';
		}

		$sql = 'SELECT post_text
			FROM ' . $this->tables['posts'] . '
			WHERE ' . $this->db->sql_build_array('SELECT', ['post_id' => $post_id]);
		// Cache query for 24 hours
		$result = $this->db->sql_query($sql, (24 * 60 * 60));
		$description = $this->db->sql_fetchfield('post_text');
		$this->db->sql_freeresult($result);

		return $description;
	}

	/**
	 * Generate image from post body.
	 *
	 * @param string	$description
	 * @param integer	$post_id
	 * @param integer	$forum_id
	 * @param integer	$max_images
	 *
	 * @return array	url, width, height and type
	 */
	public function extract_image($description = '', $post_id = 0, $forum_id = 0, $max_images = 3)
	{
		$description = trim($description);
		$post_id = (int) $post_id;
		$forum_id = (int) $forum_id;
		$default = [
			'url' => '',
			'width' => 0,
			'height' => 0,
			'type' => ''
		];

		if (empty($description) || empty($post_id) || empty($forum_id))
		{
			return $default;
		}

		$cached = [
			'topic' => [
				'name' => sprintf('seo_metadata_image_post_%d', $post_id)
			],
			'forum' => [
				'name' => sprintf('seo_metadata_image_forum_%d', $forum_id)
			]
		];

		foreach ($cached as $key => $value)
		{
			$cached[$key]['image'] = $this->cache->get($value['name']);
		}

		// Check cached image first
		if (!empty($cached['topic']['image']['url']))
		{
			return $cached['topic']['image'];
		}

		$server_name = trim($this->config['server_name']);
		$image_strategy = abs((int) $this->config['seo_metadata_image_strategy']);
		$local_images = ((int) $this->config['seo_metadata_local_images'] === 1) && !empty($server_name);
		$use_attachments = ((int) $this->config['seo_metadata_attachments'] === 1);
		$prefer_attachments = ((int) $this->config['seo_metadata_prefer_attachments'] === 1);
		$max_images = abs((int) $max_images);
		$max_images = empty($max_images) ? 5 : $max_images;
		$max_images = ($max_images > 5) ? 5 : $max_images;
		$images = [];

		// Ensure it's XML
		if (!preg_match('#^<[rt][ >]#', $description))
		{
			$description = sprintf('<t>%s</t>', $description);
		}

		// Try to fix XML
		if (!$this->is_valid_xml($description))
		{
			$uid = $bitfield = $flags = null;
			generate_text_for_storage($description, $uid, $bitfield, $flags, true, true, true);
			$description = generate_text_for_display($description, $uid, $bitfield, $flags);
		}

		// DOM manipulation
		$dom = new \DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($description);
		$xpath = new \DOMXPath($dom);

		// Get post images
		foreach ($xpath->query('//IMG') as $node)
		{
			// Get image URL
			$url = trim($node->getAttribute('src'));

			// Only JPEG, PNG and GIF images are supported
			if (!preg_match('#\.(?:jpe?g|png|gif)$#', $url))
			{
				continue;
			}

			// Get only local images
			if ($local_images &&
				!preg_match('#^https?://(?:\w+\.)?' . preg_quote($server_name) . '#', $url))
			{
				continue;
			}

			$images[] = $url;
		}

		// Get attachment images
		if ($use_attachments)
		{
			$sql = 'SELECT attach_id FROM ' . $this->tables['attachments'] . '
				WHERE post_msg_id = ' . $post_id . '
					AND ' . $this->db->sql_in_set('extension', ['jpg', 'jpeg', 'png', 'gif']) . '
					AND is_orphan = 0
				ORDER BY attach_id ASC';
			// Cache query for 24 hours
			$result = $this->db->sql_query_limit($sql, $max_images, 0, (24 * 60 * 60));
			$attachment_ids = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);
			$attachments = [];

			foreach ($attachment_ids as $attachment)
			{
				$attachments[] = $this->clean_url(vsprintf(
					'%1$s/download/file.%2$s?id=%3$s&amp;mode=view',
					[generate_board_url(), $this->php_ext, $attachment['attach_id']]
				));
			}

			// Prepend or append attachments
			if ($prefer_attachments)
			{
				$images = array_merge($attachments, $images);
			}
			else
			{
				$images = array_merge($images, $attachments);
			}
		}

		// Remove duplicated images
		$images = array_unique($images);

		// Limit array length
		if (count($images) > $max_images)
		{
			$images = array_slice($images, 0, $max_images);
		}

		// Filter images
		foreach ($images as $key => $value)
		{
			$image = ['file' => $value];

			// Did not pass validation
			if (!$this->validate_image($image))
			{
				unset($images[$key]);
				continue;
			}

			$images[$key] = $image['info'];
		}

		// Reindex array
		$images = array_values($images);

		// Sort images array
		if (count($images) > 1)
		{
			switch ($image_strategy)
			{
				case 1: // Image dimensions
					array_multisort(
						array_column($images, 'width'),
						SORT_DESC,
						array_column($images, 'height'),
						SORT_DESC,
						$images
					);
				break;

				default: // First found
				break;
			}
		}

		// Fallback image
		if (empty($images[0]) && !empty($cached['forum']['image']['url']))
		{
			// Forum image
			return $cached['forum']['image'];
		}
		else if (empty($images[0]) && empty($cached['forum']['image']['url']))
		{
			// Use default image
			return $default;
		}

		// Add image to cache
		$this->cache->put($cached['topic']['name'], $images[0]);

		return $images[0];
	}

	/**
	 * Generate image from forum data.
	 *
	 * It will return the image information (url, width, height and type) on success, null otherwise.
	 *
	 * @param string	$forum_image
	 * @param integer	$forum_id
	 *
	 * @return null|array
	 */
	public function forum_image($forum_image = '', $forum_id = 0)
	{
		$forum_image = trim($forum_image);
		$forum_id = (int) $forum_id;
		$default = [
			'url' => '',
			'width' => 0,
			'height' => 0,
			'type' => ''
		];

		if (empty($forum_image) || empty($forum_id))
		{
			return $default;
		}

		$cache_name = sprintf('seo_metadata_image_forum_%d', $forum_id);
		$cached_image = $this->cache->get($cache_name);

		// Check cached image first
		if (!empty($cached_image['url']))
		{
			return $cached_image;
		}

		// Get image from forum data
		$image = ['file' => $forum_image];
		$errors = [];

		// Validate forum image
		if ($this->validate_image($image, $errors, ['images_dir' => 0]))
		{
			// Add image to cache
			$this->cache->put($cache_name, $image['info']);

			return $image['info'];
		}

		// Use default image
		return null;
	}

	/**
	 * Get image information (width, height and MIME type).
	 *
	 * It will return an array (url, width, height and type) on success, false otherwise.
	 *
	 * @param string $url
	 *
	 * @return bool|array
	 */
	public function get_image_info($url = '')
	{
		$url = trim($url);

		if (empty($url))
		{
			return false;
		}

		// Try to get image information
		$info = $this->imagesize->getImageSize($url);

		if (!empty($info))
		{
			// Replace default values
			if (is_array($info))
			{
				$info = array_merge([
					'url' => $url,
					'type' => '',
					'width' => 0,
					'height' => 0
				], $info);
			}

			// Return MIME type as string
			if (is_int($info['type']))
			{
				$info['type'] = image_type_to_mime_type($info['type']);
			}
		}

		return $info;
	}

	/**
	 * Validate form fields with given filters.
	 *
	 * @param array $fields		Pair of field name and value
	 * @param array $filters	Filters that will be passed to filter_var_array()
	 * @param array $errors		Array of message errors
	 *
	 * @return bool
	 */
	public function validate(&$fields = [], &$filters = [], &$errors = [])
	{
		if (empty($fields) || empty($filters))
		{
			return false;
		}

		// Filter fields
		$data = filter_var_array($fields, $filters, false);

		// Invalid fields helper
		$invalid = [];

		// Validate fields
		foreach ($data as $key => $value)
		{
			// Remove and generate error if field did not pass validation
			// Not using empty() because an empty string can be a valid value
			if (!isset($value) || $value === false)
			{
				$invalid[] = $this->language->lang(
					sprintf('ACP_%s', strtoupper($key))
				);
				unset($fields[$key]);
				continue;
			}
		}

		if (!empty($invalid))
		{
			$errors[]['message'] = $this->language->lang(
				'ACP_SEO_METADATA_VALIDATE_INVALID_FIELDS',
				implode(', ', $invalid)
			);
		}

		// Validation check
		return empty($errors);
	}

	/**
	 * Validate image for use in meta tags.
	 *
	 * It will return the given data array with information generated from it.
	 *
	 * @param array $data	Image data, only the key file containing the path is required
	 * @param array $errors	Array of message errors
	 * @param array $extra	Minimum image dimensions, by default 200x200
	 *
	 * @return bool
	 */
	public function validate_image(&$data = [], &$errors = [], $extra = [])
	{
		if (empty($data) || empty($data['file']))
		{
			return false;
		}

		// Extra parameters
		$extra['images_dir'] = isset($extra['images_dir']) ? (int) $extra['images_dir'] : 1;

		// Minimum dimensions
		$min = [
			'width' => !empty($extra[0]) ? (int) $extra[0] : 200,
			'height' => !empty($extra[1]) ? (int) $extra[1] : 200
		];

		// Allowed mime types
		$types = [
			'image/jpeg',
			'image/png',
			'image/gif'
		];

		// Image URL
		$url = $this->clean_image($data['file'], $extra['images_dir']);

		// Validate image URL
		if (empty($url))
		{
			$errors[]['message'] = $this->language->lang(
				'ACP_SEO_METADATA_VALIDATE_INVALID_IMAGE',
				$data['file']
			);

			// Further code depends on the URL
			return false;
		}

		// Add image information (URL, width, height, and MIME type)
		$data['info'] = $this->get_image_info($url);

		// Could not get image information
		if (empty($data['info']))
		{
			return false;
		}

		// Fix MIME type
		if (isset($data['info']['type']) && is_int($data['info']['type']))
		{
			$data['info']['type'] = image_type_to_mime_type($data['info']['type']);
		}

		// Validate image dimensions
		if ((!empty($data['info']['width']) && $data['info']['width'] < $min['width']) ||
			(!empty($data['info']['height']) && $data['info']['height'] < $min['height']))
		{
			$errors[]['message'] = $this->language->lang(
				'ACP_SEO_METADATA_VALIDATE_SMALL_IMAGE',
				$data['file'], $min['width'], $min['height']
			);
		}

		// Validate image MIME type
		if (!empty($data['info']['type']) && !in_array($data['info']['type'], $types, true))
		{
			$errors[]['message'] = $this->language->lang(
				'ACP_SEO_METADATA_VALIDATE_INVALID_MIME_TYPE',
				$data['file'], $data['info']['type']
			);
		}

		// Validation check
		return (empty($errors) && !empty($data['info']));
	}

	/**
	 * Supported Open Graph locales.
	 *
	 * https://developers.facebook.com/docs/internationalization
	 * https://developers.facebook.com/docs/messenger-platform/messenger-profile/supported-locales/
	 *
	 * Last updated: 2019-09-06
	 *
	 * @return array
	 */
	private function supported_locales()
	{
		return [
			'af_ZA','ar_AR','as_IN','az_AZ','be_BY','bg_BG','bn_IN','br_FR','bs_BA','ca_ES',
			'cb_IQ','co_FR','cs_CZ','cx_PH','cy_GB','da_DK','de_DE','el_GR','en_GB','en_UD',
			'en_US','es_ES','es_LA','et_EE','eu_ES','fa_IR','ff_NG','fi_FI','fo_FO','fr_CA',
			'fr_FR','fy_NL','ga_IE','gl_ES','gn_PY','gu_IN','ha_NG','he_IL','hi_IN','hr_HR',
			'hu_HU','hy_AM','id_ID','is_IS','it_IT','ja_JP','ja_KS','jv_ID','ka_GE','kk_KZ',
			'km_KH','kn_IN','ko_KR','ku_TR','lt_LT','lv_LV','mg_MG','mk_MK','ml_IN','mn_MN',
			'mr_IN','ms_MY','mt_MT','my_MM','nb_NO','ne_NP','nl_BE','nl_NL','nn_NO','or_IN',
			'pa_IN','pl_PL','ps_AF','pt_BR','pt_PT','qz_MM','ro_RO','ru_RU','rw_RW','sc_IT',
			'si_LK','sk_SK','sl_SI','so_SO','sq_AL','sr_RS','sv_SE','sw_KE','sz_PL','ta_IN',
			'te_IN','tg_TJ','th_TH','tl_PH','tr_TR','tz_MA','uk_UA','ur_PK','uz_UZ','vi_VN',
			'zh_CN','zh_HK','zh_TW'
		];
	}

	/**
	 * Checks if is a valid XML string.
	 *
	 * @param string $xml
	 *
	 * @return bool
	 */
	private function is_valid_xml($xml = '')
	{
		$xml = trim($xml);

		if (empty($xml))
		{
			return false;
		}

		// Suppress errors
		libxml_clear_errors();
		$previous = libxml_use_internal_errors(true);

		$dom = new \DOMDocument;
		$dom->loadXML($xml);

		// Validation
		$errors = libxml_get_errors();

		// Clear error buffer and restore previous value
		libxml_clear_errors();
		libxml_use_internal_errors($previous);

		return empty($errors);
	}

	/**
	 * Check if the admin aproved showing metadata for specific posts.
	 *
	 * Helper for listener.
	 *
	 * @return bool
	 */
	public function check_replies()
	{
		return ((int) $this->config['seo_metadata_post_metadata'] === 1);
	}

	/**
	 * Check if fiven post ID is a reply of the first post.
	 *
	 * @param array		$post_list
	 * @param integer	$first_post_id
	 * @param integer	$post_id (reference)
	 *
	 * @return bool
	 */
	public function is_reply($post_list = [], $first_post_id = 0, &$post_id = 0)
	{
		// Cast values
		$first_post_id = (int) $first_post_id;

		// It needs to be checked agains a valid post list
		// and must be different from the first post ID
		if (empty($post_list) || empty($first_post_id))
		{
			return false;
		}

		// Get post ID
		$pid = $this->request->variable('p', 0);

		$is_reply = !empty($pid) &&
			in_array($pid, $post_list, true) &&
			$pid !== $first_post_id;

		// Update post ID
		if ($is_reply)
		{
			$post_id = $pid;
		}

		return $is_reply;
	}

	/**
	 * Twitter Cards summary with large image.
	 *
	 * https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/summary-card-with-large-image
	 *
	 * @param integer $width
	 * @param integer $height
	 *
	 * @param bool
	 */
	public function is_wide_image($width = 0, $height = 0)
	{
		$width = abs((int) $width);
		$height = abs((int) $height);

		if (empty($width) || empty($height))
		{
			return false;
		}

		$is_wide = ($width >= 300 && $height >= 157);
		$is_wide = $is_wide && ($width >= (($height - 10) * 1.5));

		return $is_wide;
	}

	/**
	 * Remove empty items from an array, recursively.
	 *
	 * @param array		$data
	 * @param integer	$depth
	 *
	 * @return array
	 */
	public function filter_empty_items($data = [], $depth = 0)
	{
		if (empty($data))
		{
			return [];
		}

		$max_depth = 5;
		$depth = abs($depth) + 1;

		// Do not go deeper, return data as is
		if ($depth > $max_depth)
		{
			return $data;
		}

		// Remove empty elements
		foreach ($data as $key => $value)
		{
			if (empty($value))
			{
				unset($data[$key]);
			}

			if (is_array($value) && !empty($value))
			{
				$data[$key] = $this->filter_empty_items($data[$key], $depth);
			}
		}

		// Return a copy
		return $data;
	}
}
