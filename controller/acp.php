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
use FastImageSize\FastImageSize;
use alfredoramos\seometadata\includes\helper;

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

	/** @var \FastImageSize\FastImageSize */
	protected $imagesize;

	/** @var \alfredoramos\seometadata\includes\helper */
	protected $helper;

	/**
	 * Controller constructor.
	 *
	 * @param \phpbb\config\config						$config
	 * @param \phpbb\template\template					$template
	 * @param \phpbb\request\request					$request
	 * @param \phpbb\language\language					$language
	 * @param \phpbb\user								$user
	 * @param \phpbb\log\log							$log
	 * @param \FastImageSize\FastImageSize				$imagesize
	 * @param \alfredoramos\seometadata\includes\helper	$helper
	 *
	 * @return void
	 */
	public function __construct(config $config, template $template, request $request, language $language, user $user, log $log, FastImageSize $imagesize, helper $helper)
	{
		$this->config = $config;
		$this->template = $template;
		$this->request = $request;
		$this->language = $language;
		$this->user = $user;
		$this->log = $log;
		$this->imagesize = $imagesize;
		$this->helper = $helper;
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

		// Validation errors
		$errors = [];

		// Field filters
		$filters = [
			// Global
			'seo_metadata_meta_description' => [
				'filter' => FILTER_VALIDATE_BOOLEAN
			],
			'seo_metadata_desc_length' => [
				'filter' => FILTER_VALIDATE_INT,
				'min_range' => 50,
				'max_range' => 255
			],
			'seo_metadata_desc_strategy' => [
				'filter' => FILTER_VALIDATE_INT,
				'min_range' => 0,
				'max_range' => 2
			],
			'seo_metadata_image_strategy' => [
				'filter' => FILTER_VALIDATE_INT,
				'min_range' => 0,
				'max_range' => 1
			],
			'seo_metadata_default_image' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'regexp' => '#^[\w\.\-\/]+\.(?:jpe?g|png|gif)$#'
			],
			'seo_metadata_default_image_width' => [
				'filter' => FILTER_VALIDATE_INT,
				'min_range' => 200,
				'max_range' => 1000
			],
			'seo_metadata_default_image_height' => [
				'filter' => FILTER_VALIDATE_INT,
				'min_range' => 200,
				'max_range' => 1000
			],
			'seo_metadata_default_image_type' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'regexp' => '#^image\/(?:jpe?g|png|gif)$#'
			],
			'seo_metadata_local_images' => [
				'filter' => FILTER_VALIDATE_BOOLEAN
			],
			'seo_metadata_attachments' => [
				'filter' => FILTER_VALIDATE_BOOLEAN
			],
			'seo_metadata_prefer_attachments' => [
				'filter' => FILTER_VALIDATE_BOOLEAN
			],

			// Open Graph
			'seo_metadata_open_graph' => [
				'filter' => FILTER_VALIDATE_BOOLEAN
			],
			'seo_metadata_facebook_application' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'regexp' => '#^\d{10,25}$#'
			],
			'seo_metadata_facebook_publisher' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'regexp' => '#^https?://(?:www\.)?facebook\.com\/(?:pages\/)?[\w\.\-]+$#'
			],

			// Twitter Cards
			'seo_metadata_twitter_cards' => [
				'filter' => FILTER_VALIDATE_BOOLEAN
			],
			'seo_metadata_twitter_publisher' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'regexp' => '#^\@[\w\.\-]+$#'
			],

			// JSON-LD
			'seo_metadata_json_ld' => [
				'filter' => FILTER_VALIDATE_BOOLEAN
			]
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

			// Form data
			$fields = [
				// Global
				'seo_metadata_meta_description' => $this->request->variable(
					'seo_metadata_meta_description',
					1
				),
				'seo_metadata_desc_length' => $this->request->variable(
					'seo_metadata_desc_length',
					160
				),
				'seo_metadata_desc_strategy' => $this->request->variable(
					'seo_metadata_desc_strategy',
					0
				),
				'seo_metadata_image_strategy' => $this->request->variable(
					'seo_metadata_image_strategy',
					0
				),
				'seo_metadata_default_image' => $this->request->variable(
					'seo_metadata_default_image',
					''
				),
				'seo_metadata_default_image_width' => $this->request->variable(
					'seo_metadata_default_image_width',
					0
				),
				'seo_metadata_default_image_height' => $this->request->variable(
					'seo_metadata_default_image_height',
					0
				),
				'seo_metadata_default_image_type' => $this->request->variable(
					'seo_metadata_default_image_type',
					''
				),
				'seo_metadata_local_images' => $this->request->variable(
					'seo_metadata_local_images',
					1
				),
				'seo_metadata_attachments' => $this->request->variable(
					'seo_metadata_attachments',
					0
				),
				'seo_metadata_prefer_attachments' => $this->request->variable(
					'seo_metadata_prefer_attachments',
					0
				),

				// Open Graph
				'seo_metadata_open_graph' => $this->request->variable(
					'seo_metadata_open_graph',
					1
				),
				'seo_metadata_facebook_application' => $this->request->variable(
					'seo_metadata_facebook_application',
					''
				),
				'seo_metadata_facebook_publisher' => $this->request->variable(
					'seo_metadata_facebook_publisher',
					''
				),

				// Twitter Cards
				'seo_metadata_twitter_cards' => $this->request->variable(
					'seo_metadata_twitter_cards',
					1
				),
				'seo_metadata_twitter_publisher' => $this->request->variable(
					'seo_metadata_twitter_publisher',
					''
				),

				// JSON-LD
				'seo_metadata_json_ld' => $this->request->variable(
					'seo_metadata_json_ld',
					1
				)
			];

			// Convert default image filename to URL
			if (!empty($fields['seo_metadata_default_image']))
			{
				$fields['seo_metadata_default_image'] = $this->helper->clean_image(
					$fields['seo_metadata_default_image']
				);
			}

			// Add "@" before the Twitter username
			if (!empty($fields['seo_metadata_twitter_publisher']))
			{
				if (strpos($fields['seo_metadata_twitter_publisher']) === false)
				{
					$fields['seo_metadata_twitter_publisher'] = sprintf(
						'@%s',
						$fields['seo_metadata_twitter_publisher']
					);
				}
			}

			// Validation check
			if ($this->helper->validate($fields, $filters, $errors))
			{
				// Save configuration
				foreach ($fields as $key => $value)
				{
					$this->config->set($key, $value);
				}

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
					$this->language->lang('CONFIG_UPDATED') .
					adm_back_link($u_action)
				);
			}
		}

		// Assign template variables
		$this->template->assign_vars([
			'SEO_METADATA_META_DESCRIPTION' => ((int) $this->config['seo_metadata_meta_description'] === 1),
			'SEO_METADATA_DESC_LENGTH' => (int) $this->config['seo_metadata_desc_length'],
			'SEO_METADATA_DEFAULT_IMAGE' => $this->config['seo_metadata_default_image'],
			'SEO_METADATA_DEFAULT_IMAGE_WIDTH' => (int) $this->config['seo_metadata_default_image_width'],
			'SEO_METADATA_DEFAULT_IMAGE_HEIGHT' => (int) $this->config['seo_metadata_default_image_height'],
			'SEO_METADATA_DEFAULT_IMAGE_TYPE' => trim($this->config['seo_metadata_default_image_type']),
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

		// Validation errors
		foreach ($errors as $key => $value)
		{
			$this->template->assign_block_vars('VALIDATION_ERRORS', [
				'MESSAGE' => $value['message']
			]);
		}
	}
}
