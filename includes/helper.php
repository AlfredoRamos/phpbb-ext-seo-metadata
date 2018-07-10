<?php

/**
 * SEO Metadata Extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GNU GPL-2.0-only
 */

namespace alfredoramos\seometadata\includes;

class helper
{

	protected $config;
	protected $template;
	protected $user;
	protected $metadata;

	public function __construct()
	{
		global $phpbb_container, $phpbb_root_path;

		$this->config = $phpbb_container->get('config');
		$this->template = $phpbb_container->get('template');
		$this->user = $phpbb_container->get('user');

		$current_page = $this->user->extract_current_page($phpbb_root_path);
		$this->metadata = [
			'open_graph' => [
				'og:locale' => $this->config['default_lang'],
				'og:site_name' => $this->config['sitename'],
				'og:title' => '',
				'og:description' => $this->clean_description(
					$this->config['site_desc']
				),
				'og:type' => 'website',
				'og:image' => '',
				'og:url' => vsprintf(
					'%1$s/%2$s',
					[
						generate_board_url(),
						$current_page['page']
					]
				)
			]
		];

	}

	public function set_metadata($data = [], $key = '')
	{
		if (!empty($key) && !empty($this->metadata[$key]))
		{
			$this->metadata = array_merge($this->metadata[$key], $data);
		} else {
			$this->metadata = array_merge($this->metadata, $data);
		}
	}

	public function get_metadata($key = '')
	{
		if (!empty($key) && !empty($this->metadata[$key]))
		{
			return $this->metadata[$key];
		}

		return $this->metadata;
	}

	public function metadata_template_vars($data = [])
	{
		$this->template->destroy_block_vars('SEO_METADATA');

		foreach ($data as $key => $value)
		{
			$this->template->assign_block_vars(
				'SEO_METADATA',
				[
					'NAME' => strtoupper($key),
				]
			);

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

	public function clean_description($description = '', $max_length = 160)
	{
		// Cast values
		$description = (string) $description;
		$max_length = abs((int) $max_length);

		if (empty($description))
		{
			return '';
		}

		// max_length can't be greater than 255
		$max_length = ($max_length > 255) ? 255 : $max_length;

		// Remove BBCode
		strip_bbcode($description);
		$description = trim($description);

		// Check description length
		if (mb_strlen($description, 'UTF-8') > $max_length)
		{
			$description = mb_substr($description, 0, $max_length, 'UTF-8');
		}

		return trim($description);
	}
}
