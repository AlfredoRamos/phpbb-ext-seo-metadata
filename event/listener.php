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
		// Helper
		$page_title = $event['page_title'];

		$this->helper->set_metadata(
			[
				'open_graph' => [
					'og:title' => $page_title
				],
				'json_ld' => [
					'headline' => $page_title
				]
			]
		);

		$this->helper->metadata_template_vars();
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

		// Helper
		$description = $this->helper->clean_description($event['forum_data']['forum_desc']);

		$this->helper->set_metadata(
			[
				'open_graph' => [
					'og:description' => $description
				],
				'json_ld' => [
					'description' => $description
				]
			]
		);

		$this->helper->metadata_template_vars();
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
		$description = $event['rowset'][$event['topic_data']['topic_first_post_id']]['post_text'];

		if ((int) $event['start'] > 0)
		{
			$description = $this->helper->extract_description($event['topic_data']['topic_first_post_id']);
		}

		if (empty($description))
		{
			return;
		}

		// Extract image
		$image = $this->helper->extract_image(
			$description,
			$event['topic_data']['topic_first_post_id']
		);

		// Helpers
		$description = $this->helper->clean_description($description);
		$image = $this->helper->clean_image($image);

		$this->helper->set_metadata(
			[
				'open_graph' => [
					'og:description' => $description,
					'og:image' => $image
				],
				'json_ld' => [
					'description' => $description,
					'image'	=> $image
				]
			]
		);

		$this->helper->metadata_template_vars();
	}

}
