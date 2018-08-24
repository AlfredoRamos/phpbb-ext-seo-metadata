<?php

/**
 * SEO Metadata Extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\event;

use alfredoramos\seometadata\includes\helper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{

	/** @var \alfredoramos\seometadata\includes\helper */
	protected $helper;

	/**
	 * Listener constructor.
	 *
	 * @param \alfredoramos\seometadata\includes\helper $helper
	 *
	 * @return void
	 */
	public function __construct(helper $helper)
	{
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
			'core.viewtopic_modify_post_data' => 'viewtopic'
		];
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
		$data['description'] = $event['rowset'][$event['topic_data']['topic_first_post_id']]['post_text'];

		if ((int) $event['start'] > 0)
		{
			$data['description'] = $this->helper->extract_description($event['topic_data']['topic_first_post_id']);
		}

		// Extract image
		$data['image'] = $this->helper->extract_image(
			$data['description'],
			$event['topic_data']['topic_first_post_id']
		);

		// Helpers
		$data['title'] = $event['topic_data']['topic_title'];
		$data['description'] = $this->helper->clean_description($data['description']);
		$data['image'] = $this->helper->clean_image($data['image']);
		$data['datetime'] = date('c', $event['topic_data']['topic_time']);
		$data['author'] = $event['topic_data']['topic_first_poster_name'];
		$data['section'] = $event['topic_data']['forum_name'];

		$this->helper->set_metadata(
			[
				'open_graph' => [
					'og:title' => $data['title'],
					'og:description' => $data['description'],
					'og:image' => $data['image'],
					'article:published_time' => $data['datetime'],
					'article:author:username' => $data['author'],
					'article:section' => $data['section']
				],
				'json_ld' => [
					'headline' => $data['title'],
					'description' => $data['description'],
					'image'	=> $data['image']
				]
			]
		);

		$this->helper->metadata_template_vars();
	}

}
