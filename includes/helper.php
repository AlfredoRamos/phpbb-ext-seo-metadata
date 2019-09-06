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

	/**
	 * Helper constructor.
	 *
	 * @param \phpbb\db\driver\factory				$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\user							$user
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \phpbb\cache\driver\driver_interface	$cache
	 * @param \phpbb\controller\helper				$controller_helper
	 * @param \phpbb\event\dispatcher_interface		$dispatcher
	 * @param \FastImageSize\FastImageSize			$imagesize
	 * @param string								$root_path
	 * @param string								$php_ext
	 *
	 * @return void
	 */
	public function __construct(database $db, config $config, user $user, template $template, language $language, filesystem $filesystem, cache $cache, controller_helper $controller_helper, dispatcher $dispatcher, FastImageSize $imagesize, $root_path, $php_ext)
	{
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
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
			$this->metadata = array_replace_recursive($this->metadata, [
				'meta_description' => [
					'description' => $default['description']
				],
				'twitter_cards' => [
					'twitter:card' => 'summary',
					'twitter:site' => $this->config['seo_metadata_twitter_publisher'],
					'twitter:title' => '',
					'twitter:description' => $default['description'],
					'twitter:image' => $default['image']['url']
				],
				'open_graph' => [
					'fb:app_id' => $this->config['seo_metadata_facebook_application'],
					'og:locale' => $this->extract_locale($this->language->lang('USER_LANG')),
					'og:site_name' => $this->config['sitename'],
					'og:url' => $default['url'],
					'og:type' => 'website',
					'og:title' => '',
					'og:description' => $default['description'],
					'og:image' => $default['image']['url'],
					'og:image:type' => $default['image']['type'],
					'og:image:width' => $default['image']['width'],
					'og:image:height' => $default['image']['height']
				],
				'json_ld' => [
					'@context' => 'http://schema.org',
					'@type' => 'DiscussionForumPosting',
					'@id' => $default['url'],
					'headline' => '',
					'description' => $default['description'],
					'image' => $default['image']['url']
				]
			]);
		}

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

		// Open Graph extra check for default image
		if (empty($data['open_graph']['og:image']))
		{
			unset(
				$data['open_graph']['og:image:type'],
				$data['open_graph']['og:image:width'],
				$data['open_graph']['og:image:height']
			);
		}

		// Twitter cards can use Open Graph data
		if ((int) $this->config['seo_metadata_open_graph'] === 1 &&
			(int) $this->config['seo_metadata_twitter_cards'] === 1)
		{
			unset(
				$data['twitter_cards']['twitter:title'],
				$data['twitter_cards']['twitter:description'],
				$data['twitter_cards']['twitter:image']
			);
		}

		foreach ($data as $key => $value)
		{
			// Ignore disabled options
			if ((int) $this->config[sprintf('seo_metadata_%s', $key)] !== 1)
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
		$dom->preserveWhiteSpace = false;
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
	 * @param string $uri
	 *
	 * @return string
	 */
	public function clean_image($uri = '')
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

		// Image must exist inside the phpBB's images path
		$base_path = $this->filesystem->realpath($this->root_path . 'images/');

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
		$url = str_replace(['&amp;', '&'], ['&', '&amp;'], $url);

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
			FROM ' . POSTS_TABLE . '
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
	 * @param integer	$max_images
	 *
	 * @return array	url, width, height and type
	 */
	public function extract_image($description = '', $post_id = 0, $max_images = 3)
	{
		$description = trim($description);
		$post_id = (int) $post_id;

		if (empty($description) || empty($post_id))
		{
			return '';
		}

		$cache_name = sprintf('seo_metadata_image_post_%d', $post_id);
		$cached_image = $this->cache->get($cache_name);

		// Check cached image first
		if (!empty($cached_image['url']))
		{
			return $cached_image;
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
			$sql = 'SELECT attach_id FROM ' . ATTACHMENTS_TABLE . '
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
			$info = $this->imagesize->getImageSize($value);

			// Can't get image dimensions
			if (empty($info))
			{
				unset($images[$key]);
				continue;
			}

			// Images should be at least 200x200 px
			if (($info['width'] < 200) || ($info['height'] < 200))
			{
				unset($images[$key]);
				continue;
			}

			$images[$key] = [
				'url' => $value,
				'width' => $info['width'],
				'height' => $info['height'],
				'type' => image_type_to_mime_type($info['type'])
			];
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
		if (empty($images[0]))
		{
			return [
				'url' => trim($this->config['seo_metadata_default_image']),
				'width' => (int) $this->config['seo_metadata_default_image_width'],
				'height' => (int) $this->config['seo_metadata_default_image_height'],
				'type' => trim($this->config['seo_metadata_default_image_type'])
			];
		}

		// Add image to cache
		$this->cache->put($cache_name, $images[0]);

		return $images[0];
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

		libxml_use_internal_errors(true);

		$dom = new \DOMDocument;
		$dom->loadXML($xml);

		$errors = libxml_get_errors();
		libxml_clear_errors();

		return empty($errors);
	}
}
