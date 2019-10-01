<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\acp;

class main_module
{
	/** @var string */
	public $u_action;

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $page_title;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \alfredoramos\seometadata\controller\acp */
	protected $acp_controller;

	/**
	 * ACP module constructor.
	 *
	 * @return void
	 */
	public function __construct()
	{
		global $phpbb_container;

		$this->template = $phpbb_container->get('template');
		$this->language = $phpbb_container->get('language');
		$this->acp_controller = $phpbb_container->get('alfredoramos.seometadata.acp.controller');
	}

	/**
	 * Main module method.
	 *
	 * @param string $id
	 * @param string $mode
	 *
	 * @return void
	 */
	public function main($id, $mode)
	{
		// Load translations
		$this->language->add_lang('acp/settings', 'alfredoramos/seometadata');

		// Set form token
		add_form_key('alfredoramos_seometadata');

		switch ($mode)
		{
			case 'settings':
				$this->tpl_name = 'acp_seo_metadata_settings';
				$this->page_title = sprintf(
					'%s - %s',
					$this->language->lang('SETTINGS'),
					$this->language->lang('ACP_SEO_METADATA')
				);
				$this->acp_controller->settings_mode($this->u_action);
			break;

			default:
				trigger_error(
					$this->language->lang('NO_MODE') .
					adm_back_link($this->u_action),
					E_USER_WARNING
				);
			break;
		}

		// Assign global template variables
		$this->template->assign_var('U_ACTION', $this->u_action);
	}
}
