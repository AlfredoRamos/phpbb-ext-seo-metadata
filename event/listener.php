<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\event;

use phpbb\config\config;
use alfredoramos\seometadata\includes\helper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \alfredoramos\seometadata\includes\helper */
	protected $helper;

	/**
	 * Listener constructor.
	 *
	 * @param \phpbb\config\config						$config
	 * @param \alfredoramos\seometadata\includes\helper	$helper
	 *
	 * @return void
	 */
	public function __construct(config $config, helper $helper)
	{
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
		$data['title'] = $event['page_title'];

		$this->helper->set_metadata(
			[
				'twitter_cards' => [
					'twitter:title' => $data['title']
				],
				'open_graph' => [
					'og:title' => $data['title']
				],
				'json_ld' => [
					'headline' => $data['title']
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
		$data['description'] = $this->helper->clean_description($event['forum_data']['forum_desc']);

		$this->helper->set_metadata(
			[
				'meta_description' => [
					'description' => $data['description']
				],
				'twitter_cards' => [
					'twitter:description' => $data['description']
				],
				'open_graph' => [
					'og:description' => $data['description']
				],
				'json_ld' => [
					'description' => $data['description']
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
		// Extract description
		if ((int) $event['start'] > 0)
		{
			$data['description'] = $this->helper->extract_description($event['topic_data']['topic_first_post_id']);
		}
		else
		{
			$data['description'] = $event['rowset'][$event['topic_data']['topic_first_post_id']]['post_text'];
		}

		// Extract image
		$data['image'] = $this->helper->extract_image(
			$data['description'],
			$event['topic_data']['topic_first_post_id']
		);

		// Helpers
		$data['title'] = $event['topic_data']['topic_title'];
		$data['description'] = $this->helper->clean_description($data['description']);
		$data['image']['url'] = $this->helper->clean_image($data['image']['url']);
		$data['datetime'] = date('c', $event['topic_data']['topic_time']);
		$data['section'] = $event['topic_data']['forum_name'];
		$data['publisher'] = $this->config['seo_metadata_facebook_publisher'];

		$this->helper->set_metadata(
			[
				'meta_description' => [
					'description' => $data['description']
				],
				'twitter_cards' => [
					'twitter:title' => $data['title'],
					'twitter:description' => $data['description'],
					'twitter:image' => $data['image']['url']
				],
				'open_graph' => [
					'og:type' => 'article',
					'og:title' => $data['title'],
					'og:description' => $data['description'],
					'og:image' => $data['image']['url'],
					'og:image:type' => $data['image']['type'],
					'og:image:width' => $data['image']['width'],
					'og:image:height' => $data['image']['height'],
					'article:published_time' => $data['datetime'],
					'article:section' => $data['section'],
					'article:publisher' => $data['publisher'],
				],
				'json_ld' => [
					'headline' => $data['title'],
					'description' => $data['description'],
					'image'	=> $data['image']['url']
				]
			]
		);

		$this->helper->metadata_template_vars();
	}
}
