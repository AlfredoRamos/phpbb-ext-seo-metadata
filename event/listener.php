<?php

/**
 * SEO Metadata Extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\event;

use phpbb\db\driver\factory as database;
use phpbb\config\config;
use alfredoramos\seometadata\includes\helper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{

	/** @var \phpbb\db\driver\factory */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \alfredoramos\seometadata\includes\helper */
	protected $helper;

	/**
	 * Listener constructor.
	 *
	 * @param \phpbb\db\driver\factory					$db
	 * @param \phpbb\config\config						$config
	 * @param \alfredoramos\seometadata\includes\helper	$helper
	 *
	 * @return void
	 */
	public function __construct(database $db, config $config, helper $helper)
	{
		$this->db = $db;
		$this->config = $config;
		$this->helper = $helper;
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
	 * Assign default template variables.
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

	/**
	 * Assign forum template variables.
	 *
	 * @param object $event
	 *
	 * @return void
	 */
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

	/**
	 * Assign topic template variables.
	 *
	 * @param object $event
	 *
	 * @return void
	 */
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
