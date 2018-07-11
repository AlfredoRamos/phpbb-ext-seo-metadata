<?php

/**
 * SEO Metadata Extension for phpBB.
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

			// Open Graph
			$this->config->set(
				'seo_metadata_open_graph',
				$this->request->variable('seo_metadata_open_graph', 0)
			);

			// JSON+LD
			$this->config->set(
				'seo_metadata_json_ld',
				$this->request->variable('seo_metadata_json_ld', 0)
			);

			// Description length
			$desc_length = $this->request->variable('seo_metadata_desc_length', 160);
			$desc_length = ($desc_length < 50) ? 50 : $desc_length;
			$desc_length = ($desc_length > 255) ? 255 : $desc_length;
			$this->config->set(
				'seo_metadata_desc_length',
				$desc_length
			);
			
			// Description handling
			$this->config->set(
				'seo_metadata_desc_handling',
				$this->request->variable('seo_metadata_desc_handling', 1)
			);

			// Default image
			$this->config->set(
				'seo_metadata_default_image',
				$this->request->variable('seo_metadata_default_image', '')
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
			'SEO_METADATA_DESC_LENGTH' => (int) $this->config['seo_metadata_desc_length'],
			'SEO_METADATA_DESC_HANDLING' => (int) $this->config['seo_metadata_desc_handling'],
			'SEO_METADATA_DEFAULT_IMAGE' => $this->config['seo_metadata_default_image'],
			'SEO_METADATA_OPEN_GRAPH' => ((int) $this->config['seo_metadata_open_graph'] === 1),
			'SEO_METADATA_JSON_LD' => ((int) $this->config['seo_metadata_json_ld'] === 1),
			'BOARD_URL' => generate_board_url() . '/images/'
		]);
	}

}
