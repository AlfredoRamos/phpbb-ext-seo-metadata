<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@proton.me>
 * @copyright 2018 Alfredo Ramos
 * @license GNU GPL-2.0-only
 */

namespace alfredoramos\seometadata\includes;

use phpbb\db\driver\factory as database;
use phpbb\config\config;
use phpbb\user;
use phpbb\auth\auth;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\language\language;
use phpbb\filesystem\filesystem;
use phpbb\cache\driver\driver_interface as cache;
use phpbb\controller\helper as controller_helper;
use phpbb\avatar\manager as avatar_manager;
use phpbb\event\dispatcher_interface as dispatcher;
use FastImageSize\FastImageSize;

class helper
{
	/** @var database */
	protected $db;

	/** @var config */
	protected $config;

	/** @var user */
	protected $user;

	/** @var auth */
	protected $auth;

	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var language */
	protected $language;

	/** @var filesystem */
	protected $filesystem;

	/** @var cache */
	protected $cache;

	/** @var controller_helper */
	protected $controller_helper;

	/** @var avatar_manager */
	protected $avatar_manager;

	/** @var dispatcher */
	protected $dispatcher;

	/** @var FastImageSize */
	protected $imagesize;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/** @var array */
	protected $metadata;

	/** @var array */
	protected $profile_metadata;

	/** @var array */
	protected $tables;

	/** @var integer */
	public const MIN_IMAGE_DIMENSION = 200;

	/** @var integer */
	public const MAX_IMG_EXTRACTION = 10;

	/**
	 * Helper constructor.
	 *
	 * @param database			$db
	 * @param config			$config
	 * @param user				$user
	 * @param auth				$auth
	 * @param request			$request
	 * @param template			$template
	 * @param language			$language
	 * @param filesystem		$filesystem
	 * @param cache				$cache
	 * @param controller_helper	$controller_helper
	 * @param avatar_manager	$avatar_manager
	 * @param dispatcher		$dispatcher
	 * @param FastImageSize		$imagesize
	 * @param string			$root_path
	 * @param string			$php_ext
	 * @param string			$posts_table
	 * @param string			$attachments_table
	 *
	 * @return void
	 */
	public function __construct(database $db, config $config, user $user, auth $auth, request $request, template $template, language $language, filesystem $filesystem, cache $cache, controller_helper $controller_helper, avatar_manager $avatar_manager, dispatcher $dispatcher, FastImageSize $imagesize, $root_path, $php_ext, $users_table, $posts_table, $attachments_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
		$this->auth = $auth;
		$this->request = $request;
		$this->template = $template;
		$this->language = $language;
		$this->filesystem = $filesystem;
		$this->cache = $cache;
		$this->controller_helper = $controller_helper;
		$this->avatar_manager = $avatar_manager;
		$this->dispatcher = $dispatcher;
		$this->imagesize = $imagesize;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->metadata = [];

		// Assign tables
		if (empty($this->tables))
		{
			$this->tables = [
				'users' => $users_table,
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
	public function set_metadata(array $data = []): void
	{
		// Set initial metadata
		if (empty($this->metadata))
		{
			$default = [
				'description' => $this->clean_description($this->config->offsetGet('site_desc')),
				'image' => [
					'url' => $this->clean_image($this->config->offsetGet('seo_metadata_default_image')),
					'width' => (int) $this->config->offsetGet('seo_metadata_default_image_width'),
					'height' => (int) $this->config->offsetGet('seo_metadata_default_image_height'),
					'type' => trim($this->config->offsetGet('seo_metadata_default_image_type'))
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
					'twitter:site' => trim($this->config->offsetGet('seo_metadata_twitter_publisher')),
					'twitter:title' => '',
					'twitter:description' => $default['description'],
					'twitter:image' => $default['image']['url']
				],
				'open_graph' => [
					'fb:app_id' => trim($this->config->offsetGet('seo_metadata_facebook_application')),
					'og:locale' => $this->extract_locale($this->language->lang('USER_LANG')),
					'og:site_name' => trim($this->config->offsetGet('sitename')),
					'og:url' => $default['url'],
					'og:type' => 'website',
					'og:title' => '',
					'og:description' => $default['description'],
					'og:image' => $default['image']['url'],
					'og:image:type' => $default['image']['type'],
					'og:image:width' => $default['image']['width'],
					'og:image:height' => $default['image']['height'],
					'article:author' => '',
					'article:published_time' => '',
					'article:section' => '',
					'article:publisher' => trim($this->config->offsetGet('seo_metadata_facebook_publisher'))
				],
				'json_ld' => [
					'@context' => 'https://schema.org',
					'@type' => 'DiscussionForumPosting',
					'url' => $default['url'],
					'mainEntityOfPage' => $default['url'],
					'headline' => '',
					'description' => $default['description'],
					'text' => $default['description'],
					'image' => $default['image']['url'],
					'author' => [
						'@type' => 'Person',
						'name' => '',
						'url' => ''
					],
					'datePublished' => '',
					'articleSection' => '',
					'publisher' => [
						'@type' => 'Organization',
						'name' => trim($this->config->offsetGet('sitename')),
						'url' => generate_board_url(),
						'logo' => [
							'@type' => 'ImageObject',
							'url' => $this->clean_image($this->config->offsetGet('seo_metadata_json_ld_logo')),
							'width' => (int) $this->config->offsetGet('seo_metadata_json_ld_logo_width'),
							'height' => (int) $this->config->offsetGet('seo_metadata_json_ld_logo_height')
						]
					],
					'comment' => [
						// ! Template
						// [
						// 	'@type' => 'Comment',
						// 	'identifier' => '',
						// 	'text' => '',
						//	'datePublished' => '',
						// 	'author' => [
						// 		'@type' => 'Person',
						// 		'name' => '',
						// 		'url' => ''
						// 	]
						// ]
					]
				]
			];
		}

		// Remove empty values
		$data = $this->filter_empty_items($data);

		// Map values to correct properties
		foreach ($data as $key => $value)
		{
			$value = $this->trim_items($value);

			switch ($key)
			{
				case 'title':
					$this->metadata['open_graph']['og:title'] = $value;
					$this->metadata['twitter_cards']['twitter:title'] = $value;
					$this->metadata['json_ld']['headline'] = $value;
				break;

				case 'description':
					$value = $this->clean_description($value);

					// Prefix with topic title if description length is < 25 to satisfy SEO recommendations
					if (mb_strlen($value, 'UTF-8') < 25 && !empty($data['title']))
					{
						$value = trim($data['title'] . ' ' . $value);
					}

					$this->metadata['meta_description']['description'] = $value;
					$this->metadata['open_graph']['og:description'] = $value;
					$this->metadata['twitter_cards']['twitter:description'] = $value;
					$this->metadata['json_ld']['description'] = $value;
					$this->metadata['json_ld']['text'] = $value;
				break;

				case 'image':
					if (isset($value['url']))
					{
						$value['url'] = $this->clean_image($value['url']);
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
					$this->metadata['json_ld']['articleSection'] = $value;
				break;

				case 'author':
					if (isset($value['name']))
					{
						$this->metadata['open_graph']['article:author'] = $value['name'];
						$this->metadata['json_ld']['author']['name'] = $value['name'];
					}

					if (isset($value['url']))
					{
						$this->metadata['json_ld']['author']['url'] = $value['url'];
					}
				break;

				case 'comment':
					if (isset($value['text'], $value['identifier']))
					{
						$data = [
							'identifier' => $value['identifier'],
							'text' => $value['text'],
						];
						$default = [
							'@type' => 'Comment',
							'identifier' => '',
							'text' => '',
							'datePublished' => '',
							'author' => [
								'@type' => 'Person',
								'name' => '',
								'url' => ''
							]
						];

						if (isset($value['date']))
						{
							$data['datePublished'] = date('c', (int) $value['date']);
						}

						if (isset($value['author']['name']))
						{
							$data['author']['name'] = $value['author']['name'];
						}

						if (isset($value['author']['url']))
						{
							$data['author']['url'] = $value['author']['url'];
						}

						if (!$this->comment_exists($this->metadata['json_ld']['comment'], $value['identifier']))
						{
							$this->metadata['json_ld']['comment'][] = array_replace_recursive($default, $data);
						}
					}
				break;

				case 'profile':
					$this->metadata['open_graph']['og:type'] = 'profile';

					if (isset($value['first_name']))
					{
						$this->metadata['open_graph']['profile:first_name'] = $value['first_name'];
					}

					if (isset($value['last_name']))
					{
						$this->metadata['open_graph']['profile:last_name'] = $value['last_name'];
					}

					if (isset($value['username']))
					{
						$this->metadata['open_graph']['profile:username'] = $value['username'];
					}
				break;
			}
		}
	}

	/**
	 * Check user profile visibility.
	 *
	 * @return bool
	 */
	public function public_profiles_enabled(): bool
	{
		return $this->auth->acl_get('u_viewprofile');
	}

	/**
	 * Add profile metadata.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function set_profile_metadata(array $data = []): void
	{
		if (!$this->profile_metadata_enabled() || empty($data))
		{
			return;
		}

		if (!in_array((int) $data['user_type'], [USER_NORMAL, USER_FOUNDER], true) || (int) $data['user_id'] === ANONYMOUS || !empty((int) $data['user_inactive_reason']))
		{
			return;
		}

		$this->profile_metadata = [
			'json_ld' => [
				'@context' => 'https://schema.org',
				'@type' => 'ProfilePage',
				'dateCreated' => !empty($data['user_regdate']) ? date('Y-m-d\TH:i:sP', (int) $data['user_regdate']) : '',
				'dateModified' => !empty($data['user_last_active']) ? date('Y-m-d\TH:i:sP', (int) $data['user_last_active']) : '',
				'mainEntity' => [
					'@type' => 'Person',
					'name' => !empty($data['username']) ? $data['username'] : '',
					'alternateName' => !empty($data['username_clean']) ? $data['username_clean'] : '',
					'identifier' => !empty($data['user_id']) ? $data['user_id'] : '',
					'image' => !empty($data['user_avatar']) ? $this->user_avatar_url($data)['src'] : '',
					'description' => '',
					'url' => $this->clean_url($this->controller_helper->get_current_url()),
					'agentInteractionStatistic' => [
						'@type' => 'InteractionCounter',
						'interactionType' => 'https://schema.org/WriteAction',
						'userInteractionCount' => !empty($data['user_posts']) ? (int) $data['user_posts'] : 0
					]
				]
			]
		];
	}

	/**
	 * Get internal metadata.
	 *
	 * @param string $key (Optional)
	 *
	 * @return array
	 */
	public function get_metadata(string $key = ''): array
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
	 * Get internal profile metadata.
	 *
	 * @param string $key (Optional)
	 *
	 * @return array
	 */
	public function get_profile_metadata(?string $key = null): array
	{
		if (!$this->profile_metadata_enabled())
		{
			return [];
		}

		if (!empty($key))
		{
			return (!empty($this->profile_metadata[$key])) ? $this->profile_metadata[$key] : [];
		}

		return $this->profile_metadata;
	}

	/**
	 * Checkc if is profile page.
	 *
	 * @return bool
	 */
	public function is_profile_page(): bool
	{
		$url = $this->clean_url($this->controller_helper->get_current_url());
		$components = parse_url($url);
		$query_ary = [];

		if (!empty($components['query']))
		{
			$components['query'] = html_entity_decode($components['query'], ENT_QUOTES | ENT_HTML5);
			parse_str($components['query'], $query_ary);
		}

		if (empty($components) || empty($components['path']) || empty($components['query']) || empty($query_ary))
		{
			return false;
		}

		$is_profile = str_contains($components['path'], 'memberlist.php');

		if (!empty($query_ary['u']))
		{
			$query_ary['u'] = (int) $query_ary['u'];
		}

		$is_profile = $is_profile &&
			(!empty($query_ary['mode']) && $query_ary['mode'] === 'viewprofile') &&
			(!empty($query_ary['u']) && $query_ary['u'] > ANONYMOUS);

		return $is_profile;
	}

	/**
	 * Assign or update template variables.
	 *
	 * @return void
	 */
	public function metadata_template_vars(): void
	{
		$this->template->destroy_block_vars('SEO_METADATA');
		$data = $this->get_metadata();
		$is_profile_metadata = $this->profile_metadata_enabled() && $this->is_profile_page();

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
				$data['open_graph']['article:author'],
				$data['open_graph']['article:published_time'],
				$data['open_graph']['article:section'],
				$data['open_graph']['article:publisher']
			);
		}

		if ($is_profile_metadata)
		{
			$data['json_ld'] = $this->get_profile_metadata('json_ld');

			// Open Graph profile metadata
			if ($data['open_graph']['og:type'] !== 'profile')
			{
				unset(
					$data['open_graph']['profile:first_name'],
					$data['open_graph']['profile:last_name'],
					$data['open_graph']['profile:username']
				);
			}
		}
		else
		{
			// JSON-LD author
			if (empty($data['json_ld']['author']['name']))
			{
				unset($data['json_ld']['author']);
			}

			// JSON-LD article section
			if (empty($data['json_ld']['datePublished']))
			{
				unset($data['json_ld']['articleSection']);
			}

			// JSON-LD logo
			if (empty($data['json_ld']['publisher']['logo']['url']))
			{
				unset($data['json_ld']['publisher']['logo']);
			}

			// JSON-LD comment
			foreach ($data['json_ld']['comment'] as $key => $value)
			{
				if (empty($value['text']))
				{
					unset($data['json_ld']['comment'][$key]);
				}
			}
		}

		// Ignore disabled options
		foreach ($data as $key => $value)
		{
			if ((int) $this->config->offsetGet(sprintf('seo_metadata_%s', $key)) !== 1 ||
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
			$type = strtoupper($key);

			$this->template->assign_block_vars(
				'SEO_METADATA',
				[
					'NAME' => $type,
				]
			);

			if ($key === 'json_ld')
			{
				$this->template->assign_block_vars(
					sprintf('SEO_METADATA.%s', $type),
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
						sprintf('SEO_METADATA.%s', $type),
						[
							'PROPERTY' => $k,
							'CONTENT' => $v
						]
					);
				}
			}
		}

		$this->template->assign_vars([
			'S_PROFILE_METADATA' => $is_profile_metadata
		]);
	}

	/**
	 * Clean post text.
	 *
	 * @param string $post_data
	 *
	 * @return string
	 */
	public function clean_post_data(string $post_data = ''): string
	{
		if (empty($post_data))
		{
			return '';
		}

		// Ensure it's XML
		if (!preg_match('#^<[rt][ >]#', $post_data))
		{
			$post_data = sprintf('<t>%s</t>', $post_data);
		}

		// Try to fix XML
		if (!$this->is_valid_xml($post_data))
		{
			$uid = $bitfield = $flags = null;
			generate_text_for_storage($post_data, $uid, $bitfield, $flags, true, true, true);
			$post_data = generate_text_for_display($post_data, $uid, $bitfield, $flags);
		}

		// Global encoding
		$encoding = 'UTF-8';

		// DOM manipulation
		$dom = new \DOMDocument;
		$dom->loadXML($post_data);
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
		$post_data = $dom->saveXML($dom->documentElement);

		/**
		 * Manipulate post data after it has been cleaned.
		 *
		 * @event alfredoramos.seometadata.clean_post_data_after
		 *
		 * @var string post_data The XML string of the post data.
		 *
		 * @since 2.0.0
		 */
		$vars = ['post_data'];
		extract($this->dispatcher->trigger_event('alfredoramos.seometadata.clean_post_data_after', compact($vars)));

		// Text censoring
		$post_data = censor_text($post_data);

		// Remove BBCode
		strip_bbcode($post_data);

		// Remove whitespaces
		$post_data = trim(preg_replace('#\s+#', ' ', $post_data));

		return $post_data;
	}

	/**
	 * Clean text to be used as description.
	 *
	 * @param string $description
	 *
	 * @return string
	 */
	public function clean_description(string $description = ''): string
	{
		$description = $this->clean_post_data($description);

		if (empty($description))
		{
			return '';
		}

		// Cast values
		$max_length = abs((int) $this->config->offsetGet('seo_metadata_desc_length'));
		$strategy = abs((int) $this->config->offsetGet('seo_metadata_desc_strategy'));

		// Global encoding
		$encoding = 'UTF-8';

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
	 * Get user avatar URL.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function user_avatar_url(array $data = []): array
	{
		$avatar_data = ['src' => '', 'width' => 0, 'height' => 0];

		if (empty($data))
		{
			return $avatar_data;
		}

		$row = $this->avatar_manager::clean_row($data, 'user');

		if (empty($row['avatar']) || empty($row['avatar_type']) || !in_array($row['avatar_type'], $this->avatar_manager->get_enabled_drivers(), true))
		{
			return $avatar_data;
		}

		$driver = $this->avatar_manager->get_driver($row['avatar_type'], false);

		if ($driver)
		{
			$avatar_data = array_replace($avatar_data, $driver->get_data($row));
		}

		if (!empty($avatar_data['src']))
		{
			$avatar_data['src'] = str_starts_with($avatar_data['src'], './') ? substr($avatar_data['src'], 2) : $avatar_data['src'];
			$avatar_data['src'] = $this->clean_url(sprintf('%s/%s', generate_board_url(), $avatar_data['src']));
		}

		return $avatar_data;
	}

	/**
	 * Clean URI to be used as image URL.
	 *
	 * @param string	$uri
	 * @param bool		$images_dir
	 *
	 * @return string
	 */
	public function clean_image(string $uri = '', bool $images_dir = true): string
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

		// Canonicalized absolute path
		$image_path = $this->filesystem->clean_path($base_path . '/' . $uri);

		// Avoid path traversal attack
		// Image must exist and be readable
		if (empty($image_path) || !str_starts_with($image_path, $base_path) || !$this->filesystem->is_readable($image_path))
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
		$url = sprintf('%s/%s', generate_board_url(), $image_path);

		return $url;
	}

	/**
	 * Clean URL to be used as metadata.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function clean_url(string $url = ''): string
	{
		$url = trim($url);

		if (empty($url))
		{
			return '';
		}

		// Escape ampersand
		$url = htmlspecialchars($url, ENT_COMPAT, 'UTF-8', false);

		// Remove app.php/ from URL
		if ((int) $this->config->offsetGet('enable_mod_rewrite') === 1)
		{
			$url = preg_replace('#app\.' . $this->php_ext . '/(.+)$#', '\1', $url);
		}

		// Remove SID from URL
		$url = preg_replace('#(?:&amp;)?sid=\w{0,128}#', '', $url);
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
	public function extract_locale(string $locale = ''): string
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
	public function extract_description(int $post_id = 0): string
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
	 *
	 * @return array url, width, height and type
	 */
	public function extract_image(string $description = '', int $post_id = 0, int $forum_id = 0): array
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

		$server_name = trim($this->config->offsetGet('server_name'));
		$image_strategy = abs((int) $this->config->offsetGet('seo_metadata_image_strategy'));
		$local_images = ((int) $this->config->offsetGet('seo_metadata_local_images') === 1) && !empty($server_name);
		$use_attachments = ((int) $this->config->offsetGet('seo_metadata_attachments') === 1);
		$prefer_attachments = ((int) $this->config->offsetGet('seo_metadata_prefer_attachments') === 1);
		$max_images = abs((int) $this->config->offsetGet('seo_metadata_max_images'));
		$max_images = empty($max_images) ? self::MAX_IMG_EXTRACTION : $max_images;
		$max_images = ($max_images > self::MAX_IMG_EXTRACTION) ? self::MAX_IMG_EXTRACTION : $max_images;
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
			if (count($images) > $max_images)
			{
				continue;
			}

			// Get image URL
			$url = trim($node->getAttribute('src'));

			// Get image path
			$components = parse_url($url);

			// Failed to parse image URL
			if (empty($components))
			{
				continue;
			}

			// Only JPEG, PNG and GIF images are supported
			if (empty($components['path']) || !preg_match('#\.(?:jpe?g|png|gif)$#', $components['path']))
			{
				continue;
			}

			// Get only local images
			if ($local_images)
			{
				// Invalid server or image host
				if (empty($server_name) || empty($components['host']))
				{
					continue;
				}

				// Server and image host do not match
				if (!preg_match('#\.?' . preg_quote($server_name) . '$#', $components['host']))
				{
					continue;
				}
			}

			if (in_array($url, $images))
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
	 * Generate image from profile image.
	 *
	 * @param array $member
	 *
	 * @return array url, width, height and type
	 */
	public function extract_profile_image(array $member = []): array
	{
		$default = [
			'url' => '',
			'width' => 0,
			'height' => 0,
			'type' => ''
		];

		if (empty($member))
		{
			return $default;
		}

		$avatar_data = $this->user_avatar_url($member);
		$image = ['file' => $avatar_data['src']];
		$errors = [];

		if ($this->validate_image($image, $errors, ['images_dir' => false]))
		{

			return $image['info'];
		}

		return $default;
	}

	/**
	 * Generate image from forum data.
	 *
	 * It will return the image information (url, width, height and type) on success, null otherwise.
	 *
	 * @param string	$forum_image
	 * @param integer	$forum_id
	 *
	 * @return array url, width, height and type
	 */
	public function forum_image(string $forum_image = '', int $forum_id = 0): array
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
		if ($this->validate_image($image, $errors, ['images_dir' => false]))
		{
			// Add image to cache
			$this->cache->put($cache_name, $image['info']);

			return $image['info'];
		}

		// Use default image
		return $default;
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
	public function get_image_info(string $url = ''): bool|array
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
	public function validate(array &$fields = null, array &$filters = null, array &$errors = null): bool
	{
		$fields = $fields ?? [];
		$filters = $filters ?? [];
		$errors = $errors ?? [];

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
	public function validate_image(array &$data = null, array &$errors = null, array $extra = []): bool
	{
		$data = $data ?? [];
		$errors = $errors ?? [];

		if (empty($data) || empty($data['file']))
		{
			return false;
		}

		// Extra parameters
		$extra['images_dir'] = isset($extra['images_dir']) ? (bool) $extra['images_dir'] : true;

		// Minimum dimensions
		$min = [
			'width' => !empty($extra[0]) ? (int) $extra[0] : self::MIN_IMAGE_DIMENSION,
			'height' => !empty($extra[1]) ? (int) $extra[1] : self::MIN_IMAGE_DIMENSION
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
	private function supported_locales(): array
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
	private function is_valid_xml(string $xml = ''): bool
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
	public function check_replies(): bool
	{
		return ((int) $this->config->offsetGet('seo_metadata_post_metadata') === 1);
	}

	/**
	 * Check if profile metadata for user profile page is enabled.
	 *
	 * @return bool
	 */
	public function profile_metadata_enabled()
	{
		return ((int) $this->config->offsetGet('seo_metadata_user_profile_metadata') === 1) &&
			$this->public_profiles_enabled();
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
	public function is_reply(array $post_list = [], int $first_post_id = 0, int &$post_id = null): bool
	{
		if ($post_id === null)
		{
			$post_id = 0;
		}

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

		$is_reply = !empty($pid) && in_array($pid, $post_list, true) && $pid !== $first_post_id;

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
	public function is_wide_image(int $width = 0, int $height = 0): bool
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
	 * Trim strings from given data, recursively.
	 *
	 * @param mixed		$data
	 * @param integer	$depth
	 *
	 * @return mixed
	 */
	public function trim_items(mixed $data = [], int $depth = 0): mixed
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

		if (!is_string($data) && !is_array($data))
		{
			return $data;
		}

		if (is_string($data))
		{
			return trim($data);
		}

		// Trim strings
		return array_map(function($item) use ($depth) {
			if (is_string($item))
			{
				return trim($item);
			}

			if (is_array($item))
			{
				return $this->trim_items($item, $depth);
			}

			return $item;
		}, $data);
	}

	/**
	 * Remove empty items from an array, recursively.
	 *
	 * @param array		$data
	 * @param integer	$depth
	 *
	 * @return array
	 */
	public function filter_empty_items(array $data = [], int $depth = 0): array
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

	/**
	 * Generate URL for user profile.
	 *
	 * @param integer $user_id
	 *
	 * @return string
	 */
	public function generate_user_url(int $user_id = 0): string
	{
		if (empty($user_id))
		{
			return '';
		}

		return sprintf('%s/memberlist.%s?mode=viewprofile&u=%d', generate_board_url(), $this->php_ext, $user_id);
	}

	/**
	 * Generate author data from topic or post data.
	 *
	 * @param string|null	$name
	 * @param integer|null	$user_id
	 * @param integer		$post_id
	 *
	 * @return array
	 */
	public function extract_author(?string $name = null, ?int $user_id = null, int $post_id = 0): array
	{
		$data = [
			'name' => $name ?? '',
			'url' => $this->generate_user_url($user_id ?? 0)
		];

		if (empty($post_id))
		{
			return $data;
		}

		$sql_array = [
			'SELECT' => 'u.user_id, u.username',
			'FROM' => [$this->tables['users'] => 'u'],
			'LEFT_JOIN' => [
				[
					'FROM' => [$this->tables['posts'] => 'p'],
					'ON' => 'p.poster_id = u.user_id'
				]
			],
			'WHERE' => 'p.post_id = ' . $post_id
		];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		// Cache query for 24 hours
		$result = $this->db->sql_query($sql, (24 * 60 * 60));
		$user = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$data = [
			'name' => $user['username'],
			'url' => $this->generate_user_url($user['user_id'])
		];

		return $data;
	}

	/**
	 * Generate profile data from user profile page.
	 *
	 * @param array $member
	 *
	 * @return array
	 */
	public function extract_profile(array $member = []): array
	{
		$data = [
			'first_name' => '',
			'last_name' => '',
			'username' => ''
		];

		if (empty($member))
		{
			return $data;
		}

		if (!empty($member['username_clean']))
		{
			$data['username'] = $member['username_clean'];
		}

		if (!empty($member['username']))
		{
			$name_ary = $this->filter_empty_items(explode(' ', trim($member['username'])));
			$name_ary_size = count($name_ary);
			$max_iterations = 5;

			if ($name_ary_size > 0)
			{
				$last_names = [];

				foreach ($name_ary as $key => $value)
				{
					if ($key >= $max_iterations)
					{
						break;
					}

					if ($key === 0)
					{
						$data['first_name'] = $value;
						continue;
					}

					$last_names[] = $value;
				}

				$data['last_name'] = implode(' ', $last_names);
			}
		}

		return $data;
	}

	/**
	 * Generate URL for user profile.
	 *
	 * @param integer $id
	 *
	 * @return string
	 */
	public function generate_post_url(int $post_id = 0): string
	{
		if (empty($post_id))
		{
			return '';
		}

		return sprintf('%1$s/viewtopic.%2$s?p=%3$d#p%3$d', generate_board_url(), $this->php_ext, $post_id);
	}

	/**
	 * Check if JSON-LD comment exists.
	 *
	 * @param array		$comment_list
	 * @param string	$identifier
	 *
	 * @param bool
	 */
	private function comment_exists(array $comment_list = [], string $identifier = ''): bool
	{
		$identifier = trim($identifier);

		if (empty($comment_list) || empty($identifier))
		{
			return false;
		}

		foreach ($comment_list as $comment)
		{
			if (!empty($comment['identifier']) && $comment['identifier'] === $identifier)
			{
				return true;
			}
		}

		return false;
	}
}
