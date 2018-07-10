<?php

/**
 * SEO Metadata Extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\event;

use phpbb\config\config;
use phpbb\template\template;
use phpbb\routing\helper as routing_helper;
use phpbb\session;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\routing\helper */
	protected $routing_helper;

	protected $user;

	protected $helper;

	/**
	 * Listener constructor.
	 *
	 * @param \phpbb\config\config		$config
	 * @param \phpbb\template\template	$template
	 * @param \phpbb\routing\helper		$routing_helper
	 *
	 * @return void
	 */
	public function __construct(config $config, template $template, routing_helper $routing_helper)
	{
		global $phpbb_container;

		$this->db = $phpbb_container->get('dbal.conn');
		$this->config = $config;
		$this->template = $template;
		$this->routing_helper = $routing_helper;
		$this->user = $phpbb_container->get('user');
		$this->helper = $phpbb_container->get('alfredoramos.seometadata.helper');
	}

	/**
	 * Assign functions defined in this class to event listeners in the core.
	 *
	 * @return array
	 */
	static public function getSubscribedEvents()
	{
		return [
			'core.page_header_after' => 'page_header',
			'core.viewforum_generate_page_after' => 'viewforum',
			'core.viewtopic_modify_post_data' => 'viewtopic'
		];
	}

	/**
	 * Assign template variables.
	 *
	 * @param object $event
	 *
	 * @return void
	 */
	public function page_header($event)
	{
		$data = [];

		$this->helper->set_metadata(
			[
				'og:title' => $event['page_title']
			],
			'open_graph'
		);

		if ((bool) $this->config['seo_metadata_open_graph'])
		{
			$data = array_merge(
				$data,
				[
					'open_graph' => $this->helper->get_metadata('open_graph')
				]
			);
		}

		$this->helper->metadata_template_vars($data);
	}

	public function viewforum($event)
	{
		if (empty($event['forum_data']['forum_desc']))
		{
			return;
		}

		$data = [];

		$this->helper->set_metadata(
			[
				'og:description' => $this->helper->clean_description($event['forum_data']['forum_desc'])
			]
		);

		if ((bool) $this->config['seo_metadata_open_graph'])
		{
			$data = array_merge(
				$data,
				[
					'open_graph' => $this->helper->get_metadata('open_graph')
				]
			);
		}

		$this->helper->metadata_template_vars($data);
	}

	public function viewtopic($event)
	{
		if ((int) $event['start'] > 0)
		{
			$sql = 'SELECT post_text
				FROM ' . POSTS_TABLE . '
				WHERE ' . $this->db->sql_build_array('SELECT', [
					'post_id' => (int) $event['topic_data']['topic_first_post_id']
				]);
			$result = $this->db->sql_query($sql);
			$description = $this->db->fetch_field('post_text');
			$this->db_freeresult($result);
		}
		else
		{
			$description = $event['rowset'][$event['topic_data']['topic_first_post_id']]['post_text'];
		}

		if (empty($description))
		{
			return;
		}

		$data = [];

		$this->helper->set_metadata(
			[
				'og:description' => $this->helper->clean_description($description)
			],
			'open_graph'
		);

		if ((bool) $this->config['seo_metadata_open_graph'])
		{
			$data = array_merge(
				$data,
				[
					'open_graph' => $this->helper->get_metadata('open_graph')
				]
			);
		}

		$this->helper->metadata_template_vars($data);
	}

}
