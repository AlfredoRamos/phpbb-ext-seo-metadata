<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\controller;

use phpbb\config\config;
use phpbb\template\template;
use phpbb\request\request;
use phpbb\language\language;
use phpbb\user;
use phpbb\log\log;

class acp
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\log\log */
	protected $log;

	/**
	 * Controller constructor.
	 *
	 * @param \phpbb\config\config		$config
	 * @param \phpbb\template\template	$template
	 * @param \phpbb\request\request	$request
	 * @param \phpbb\language\language	$language
	 * @param \phpbb\user				$user
	 * @param \phpbb\log\log			$log
	 *
	 * @return void
	 */
	public function __construct(config $config, template $template, request $request, language $language, user $user, log $log)
	{
		$this->config = $config;
		$this->template = $template;
		$this->request = $request;
		$this->language = $language;
		$this->user = $user;
		$this->log = $log;
	}

	/**
	 * Settings mode page.
	 *
	 * @param string $u_action
	 *
	 * @return void
	 */
	public function settings_mode($u_action = '')
	{
		if (empty($u_action))
		{
			return;
		}

		// Allowed description strategies
		$desc_strategies = [
			'cut',
			'ellipsis',
			'break_words'
		];

		// Allowed image strategies
		$image_strategies = [
			'first',
			'dimensions'
		];

		// Request form data
		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('alfredoramos_seometadata'))
			{
				trigger_error(
					$this->language->lang('FORM_INVALID') .
					adm_back_link($u_action),
					E_USER_WARNING
				);
			}

			// Meta description
			$meta_description = $this->request->variable('seo_metadata_meta_description', 1);
			$meta_description = (in_array($meta_description, [0, 1], true)) ? $meta_description : 1;
			$this->config->set(
				'seo_metadata_meta_description',
				$meta_description
			);

			// Description length
			$desc_length = $this->request->variable('seo_metadata_desc_length', 160);
			$desc_length = ($desc_length < 50) ? 50 : $desc_length;
			$desc_length = ($desc_length > 255) ? 255 : $desc_length;
			$this->config->set(
				'seo_metadata_desc_length',
				$desc_length
			);

			// Description strategy
			$desc_strategy = $this->request->variable('seo_metadata_desc_strategy', 0);
			$desc_strategy = (in_array($desc_strategy, array_keys($desc_strategies), true)) ? $desc_strategy : 0;
			$this->config->set(
				'seo_metadata_desc_strategy',
				$desc_strategy
			);

			// Image strategy
			$image_strategy = $this->request->variable('seo_metadata_image_strategy', 0);
			$image_strategy = (in_array($image_strategy, array_keys($image_strategies), true)) ? $image_strategy : 0;
			$this->config->set(
				'seo_metadata_image_strategy',
				$image_strategy
			);

			// Default image
			$this->config->set(
				'seo_metadata_default_image',
				$this->request->variable('seo_metadata_default_image', '')
			);

			// Local images
			$local_images = $this->request->variable('seo_metadata_local_images', 1);
			$local_images = (in_array($local_images, [0, 1], true)) ? $local_images : 1;
			$this->config->set(
				'seo_metadata_local_images',
				$local_images
			);

			// Include attachments
			$use_attachments = $this->request->variable('seo_metadata_attachments', 0);
			$use_attachments = (in_array($use_attachments, [0, 1], true)) ? $use_attachments : 0;
			$this->config->set(
				'seo_metadata_attachments',
				$use_attachments
			);

			// Prefer attachments
			$prefer_attachments = $this->request->variable('seo_metadata_prefer_attachments', 0);
			$prefer_attachments = (in_array($prefer_attachments, [0, 1], true)) ? $prefer_attachments : 0;
			$this->config->set(
				'seo_metadata_prefer_attachments',
				$prefer_attachments
			);

			// Open Graph
			$open_graph = $this->request->variable('seo_metadata_open_graph', 1);
			$open_graph = (in_array($open_graph, [0, 1], true)) ? $open_graph : 1;
			$this->config->set(
				'seo_metadata_open_graph',
				$open_graph
			);

			// Facebook application ID
			$this->config->set(
				'seo_metadata_facebook_application',
				$this->request->variable('seo_metadata_facebook_application', 0)
			);

			// Facebook publisher
			$this->config->set(
				'seo_metadata_facebook_publisher',
				$this->request->variable('seo_metadata_facebook_publisher', '')
			);

			// Twitter Cards
			$twitter_cards = $this->request->variable('seo_metadata_twitter_cards', 1);
			$twitter_cards = (in_array($twitter_cards, [0, 1], true)) ? $twitter_cards : 1;
			$this->config->set(
				'seo_metadata_twitter_cards',
				$twitter_cards
			);

			// Twitter publisher
			$twitter_publisher = $this->request->variable('seo_metadata_twitter_publisher', '');

			// Add "@" before the Twitter username
			if (!empty($twitter_publisher) && strpos($twitter_publisher, '@') === false)
			{
				$twitter_publisher = sprintf('@%s', $twitter_publisher);
			}

			$this->config->set(
				'seo_metadata_twitter_publisher',
				$twitter_publisher
			);

			// JSON-LD
			$json_ld = $this->request->variable('seo_metadata_json_ld', 1);
			$json_ld = (in_array($json_ld, [0, 1], true)) ? $json_ld : 1;
			$this->config->set(
				'seo_metadata_json_ld',
				$json_ld
			);

			// Admin log
			$this->log->add(
				'admin',
				$this->user->data['user_id'],
				$this->user->ip,
				'LOG_SEO_METADATA_DATA',
				false,
				[$this->language->lang('SETTINGS')]
			);

			// Confirm dialog
			trigger_error(
				$this->language->lang('ACP_SEO_METADATA_SETTINGS_SAVED') .
				adm_back_link($u_action)
			);
		}

		// Assign template variables
		$this->template->assign_vars([
			'SEO_METADATA_META_DESCRIPTION' => ((int) $this->config['seo_metadata_meta_description'] === 1),
			'SEO_METADATA_DESC_LENGTH' => (int) $this->config['seo_metadata_desc_length'],
			'SEO_METADATA_DEFAULT_IMAGE' => $this->config['seo_metadata_default_image'],
			'SEO_METADATA_LOCAL_IMAGES' => ((int) $this->config['seo_metadata_local_images'] === 1),
			'SEO_METADATA_ATTACHMENTS' => ((int) $this->config['seo_metadata_attachments'] === 1),
			'SEO_METADATA_PREFER_ATTACHMENTS' => ((int) $this->config['seo_metadata_prefer_attachments'] === 1),
			'SEO_METADATA_OPEN_GRAPH' => ((int) $this->config['seo_metadata_open_graph'] === 1),
			'SEO_METADATA_FACEBOOK_APPLICATION' => (int) $this->config['seo_metadata_facebook_application'],
			'SEO_METADATA_FACEBOOK_PUBLISHER' => $this->config['seo_metadata_facebook_publisher'],
			'SEO_METADATA_TWITTER_CARDS' => ((int) $this->config['seo_metadata_twitter_cards'] === 1),
			'SEO_METADATA_TWITTER_PUBLISHER' => $this->config['seo_metadata_twitter_publisher'],
			'SEO_METADATA_JSON_LD' => ((int) $this->config['seo_metadata_json_ld'] === 1),
			'SERVER_NAME' => $this->config['server_name'],
			'BOARD_IMAGES_URL' => generate_board_url() . '/images/'
		]);

		// Description strategies
		foreach ($desc_strategies as $key => $value)
		{
			$this->template->assign_block_vars('SEO_METADATA_DESC_STRATEGIES', [
				'NAME' => $this->language->lang(sprintf('ACP_SEO_METADATA_DESC_%s', strtoupper($value))),
				'VALUE' => $key,
				'SELECTED' => ($key === (int) $this->config['seo_metadata_desc_strategy'])
			]);
		}

		// Image strategies
		foreach ($image_strategies as $key => $value)
		{
			$this->template->assign_block_vars('SEO_METADATA_IMAGE_STRATEGIES', [
				'NAME' => $this->language->lang(sprintf('ACP_SEO_METADATA_IMAGE_%s', strtoupper($value))),
				'VALUE' => $key,
				'SELECTED' => ($key === (int) $this->config['seo_metadata_image_strategy'])
			]);
		}
	}
}
