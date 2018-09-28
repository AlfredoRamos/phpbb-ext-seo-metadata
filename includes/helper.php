<?php

/**
 * SEO Metadata Extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GNU GPL-2.0-only
 */

namespace alfredoramos\seometadata\includes;

use phpbb\db\driver\factory as database;
use phpbb\config\config;
use phpbb\template\template;
use phpbb\cache\driver\driver_interface as cache;
use FastImageSize\FastImageSize;
use phpbb\user;

class helper
{

	/** @var \phpbb\db\driver\factory */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\user */
	protected $user;

	/** @var \FastImageSize\FastImageSize */
	protected $imagesize;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/** @var array */
	protected $metadata;

	/**
	 * Helper constructor.
	 *
	 * @param \phpbb\db\driver\factory				$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\cache\driver\driver_interface	$cache
	 * @param \phpbb\user							$user
	 * @param \FastImageSize\FastImageSize			$imagesize
	 * @param string								$root_path
	 * @param string								$php_ext
	 *
	 * @return void
	 */
	public function __construct(database $db, config $config, template $template, cache $cache, user $user, FastImageSize $imagesize, $root_path, $php_ext)
	{
		$this->db = $db;
		$this->config = $config;
		$this->template = $template;
		$this->cache = $cache;
		$this->user = $user;
		$this->imagesize = $imagesize;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		// Current page
		$current_page = $this->user->extract_current_page($this->root_path);

		// Absolute URL of current page
		$current_url = vsprintf('%1$s/%2$s', [generate_board_url(), $current_page['page']]);

		// Set initial metadata
		$this->metadata = [
			'twitter_cards' => [
				'twitter:card' => 'summary',
				'twitter:site' => $this->config['seo_metadata_twitter_publisher']
			],
			'open_graph' => [
				'fb:app_id' => $this->config['seo_metadata_facebook_application'],
				'og:locale' => $this->config['default_lang'],
				'og:site_name' => $this->config['sitename'],
				'og:url' => $this->clean_url($current_url),
				'og:type' => 'website',
				'og:title' => '',
				'og:description' => $this->clean_description(
					$this->config['site_desc']
				),
				'og:image' => $this->clean_image(
					$this->config['seo_metadata_default_image']
				)
			],
			'json_ld' => [
				'@context' => 'http://schema.org',
				'@type' => 'DiscussionForumPosting',
				'@id' => $this->clean_url($current_url),
				'headline' => '',
				'description' => $this->clean_description(
					$this->config['site_desc']
				),
				'image' => $this->clean_image(
					$this->config['seo_metadata_default_image']
				)
			]
		];
	}

	/**
	 * Add or replace metadata.
	 *
	 * @param array		$data
	 * @param string	$key	(Optional)
	 *
	 * @return void
	 */
	public function set_metadata($data = [], $key = '')
	{
		if (!empty($key) && !empty($this->metadata[$key]))
		{
			$this->metadata = array_replace($this->metadata[$key], $data);
		}
		else
		{
			$this->metadata = array_replace_recursive($this->metadata, $data);
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
		if (!empty($key) && !empty($this->metadata[$key]))
		{
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

		foreach ($data as $key => $value)
		{
			// Ignore disabled options
			if (!((int) $this->config[sprintf('seo_metadata_%s', $key)] === 1))
			{
				continue;
			}

			// Ignore empty options
			if (empty($value))
			{
				continue;
			}

			$this->template->assign_block_vars(
				'SEO_METADATA',
				[
					'NAME' => strtoupper($key),
				]
			);

			foreach ($value as $k => $v)
			{
				// Ignore empty options
				if (empty($k) || empty($v))
				{
					continue;
				}

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
		$description = (string) $description;
		$max_length = abs((int) $this->config['seo_metadata_desc_length']);
		$strategy = abs((int) $this->config['seo_metadata_desc_strategy']);

		if (empty($description))
		{
			return '';
		}

		// Global encoding
		$encoding = 'UTF-8';

		// Text censoring
		$description = censor_text($description);

		// Remove BBCode
		strip_bbcode($description);

		// Remove images
		$description = trim(preg_replace(
			'#(?:http(?:s)?://)(?:[\w-./]+)(?:\.[a-z]{2,4})#',
			'',
			$description
		));

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
	 * @param string $uri
	 *
	 * @return string
	 */
	public function clean_image($uri = '')
	{
		if (empty($uri))
		{
			return '';
		}

		// Clean URI
		$uri = preg_replace('#^\./#', '', $uri);

		// Absolute URL
		$url = preg_match('#^https?#', $uri) ? $uri : vsprintf(
			'%1$s/images/%2$s',
			[
				generate_board_url(),
				$uri
			]
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
	public function clean_url($url)
	{
		if (empty($url))
		{
			return '';
		}

		// Remove index.php without parameters
		$url = preg_replace('#index\.' . $this->php_ext . '$#', '', $url);

		// Remove app.php/ from URL
		if ((int) $this->config['enable_mod_rewrite'] === 1)
		{
			$url = preg_replace('#app\.' . $this->php_ext . '/(.+)$#', '\1', $url);
		}

		// Escape ampersand
		$url = str_replace(['&amp;', '&'], ['&', '&amp;'], $url);

		return $url;
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
			FROM ' . POSTS_TABLE . '
			WHERE ' . $this->db->sql_build_array('SELECT', ['post_id' => $post_id]);
		$result = $this->db->sql_query($sql, (6 * 60 * 60)); // Cache query for 6 hour
		$description = $this->db->sql_fetchfield('post_text');
		$this->db->sql_freeresult($result);

		return $description;
	}


	/**
	 * Generate image from post body.
	 *
	 * @param string	$description
	 * @param integer	$post_id
	 * @param integer	$max_images
	 *
	 * @return array
	 */
	public function extract_image($description = '', $post_id = 0, $max_images = 3)
	{
		$image_strategy = abs((int) $this->config['seo_metadata_image_strategy']);
		$post_id = (int) $post_id;

		if (empty($description) || empty($post_id))
		{
			return '';
		}

		$max_images = abs((int) $max_images);
		$max_images = empty($max_images) ? 3 : $max_images;
		$max_images = ($max_images > 5) ? 5 : $max_images;
		$cache_name = sprintf('seo_metadata_image_post_%d', $post_id);
		$cached_image = $this->cache->get($cache_name);
		$images = [];

		// Check cached image first
		if (!empty($cached_image['url']))
		{
			return $cached_image['url'];
		}

		// Get images from description
		preg_match_all(
			'#<IMG src="(https?://(?:[\w-./]+)(?:\.jp(?:e?)g|png))"#',
			$description,
			$images
		);

		// Remove duplicated images
		$images = array_unique($images[1]);

		// Limit array length
		if (count($images) > $max_images)
		{
			$images = array_slice($images, 0, $max_images);
		}

		// Get image dimensions
		foreach ($images as $key => $value)
		{
			$size = $this->imagesize->getImageSize($value);

			// Can't get image dimensions
			if (empty($size))
			{
				unset($images[$key]);
				continue;
			}

			// Images should be at least 200x200 px
			if (($size['width'] < 200) || ($size['height'] < 200))
			{
				unset($images[$key]);
				continue;
			}

			$images[$key] = [
				'url' => $value,
				'width' => $size['width'],
				'height' => $size['height'],
				'type' => image_type_to_mime_type($size['type'])
			];
		}

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
		if (empty($images[0]))
		{
			return $this->clean_image(
				$this->config['seo_metadata_default_image']
			);
		}

		// Add image to cache
		$cached_image = $images[0];
		$this->cache->put($cache_name, $cached_image);

		return $cached_image['url'];
	}

}
