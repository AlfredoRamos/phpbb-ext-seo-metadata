<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@proton.me>
 * @copyright 2018 Alfredo Ramos
 * @license GNU GPL-2.0-only
 */

namespace alfredoramos\seometadata;

use phpbb\extension\base;

class ext extends base
{
	/**
	 * Check whether or not the extension can be enabled.
	 *
	 * @return bool
	 */
	public function is_enableable()
	{
		return phpbb_version_compare(PHPBB_VERSION, '3.3.0', '>=');
	}

	/**
	* {@inheritdoc}
	*/
	public function enable_step($old_state)
	{
		$parent_state = parent::enable_step($old_state);

		if ($parent_state === false)
		{
			$this->handle_seo_metadata('enable');
		}

		return $parent_state;
	}

	/**
	* {@inheritdoc}
	*/
	public function purge_step($old_state)
	{
		switch ($old_state)
		{
			case '':
				$state = $this->handle_seo_metadata('purge');
				break;

			default:
				$state = parent::purge_step($old_state);
				break;
		}

		return $state;
	}

	/**
	* {@inheritdoc}
	*/
	public function disable_step($old_state)
	{
		switch ($old_state)
		{
			case '':
				$state = $this->handle_seo_metadata('disable');
				break;

			default:
				$state = parent::disable_step($old_state);
				break;
		}

		return $state;
	}

	/**
	 * SEO Metadata step configuration handler.
	 *
	 * @param string $step The name of the step.
	 *
	 * @return bool|string
	 */
	private function handle_seo_metadata($step = '')
	{
		if (empty($step))
		{
			return false;
		}

		$config = $this->container->get('config');
		$log = $this->container->get('log');
		$user = $this->container->get('user');
		$language = $this->container->get('language');

		switch ($step)
		{
			case 'enable':
				$config->set('seo_metadata_enable_debug', 1, true);
				$log->add('admin', $user->data['user_id'], $user->ip, 'LOG_SEO_METADATA_DEBUG_ENABLE', false, [extension_loaded('gd')]);
				break;

			case 'disable':
				$config->set('seo_metadata_enable_debug', 0, true);
				break;

			case 'purge':
				$config->delete('seo_metadata_enable_debug');
				break;
		}

		return 'seo_metadata_handled';
	}
}
