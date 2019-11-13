<?php

/**
 * SEO Metadata extension for phpBB.
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
			'core.page_header_after' => 'page_header',
			'core.viewforum_modify_page_title' => 'viewforum',
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
		$this->helper->set_metadata([
			'title' => $event['page_title']
		]);

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
		// Meta data helper
		$data = [
			'description' => $this->helper->clean_description($event['forum_data']['forum_desc']),
			'image' => $this->helper->forum_image(
				$event['forum_data']['forum_image'],
				$event['forum_data']['forum_id']
			)
		];

		$this->helper->set_metadata($data);
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
		// Meta data helper
		$data = [];

		// Helpers
		$first_post_id = $event['topic_data']['topic_first_post_id'];
		$post_id = $first_post_id;
		$data['title'] = $event['topic_data']['topic_title'];
		$data['author'] = $event['topic_data']['topic_first_poster_name'];
		$data['published_time'] = (int) $event['topic_data']['topic_time'];
		$data['section'] = $event['topic_data']['forum_name'];

		// Extract description
		if ($this->helper->check_replies() && $this->helper->is_reply($event['post_list'], $first_post_id, $post_id))
		{
			$data['description'] = $this->helper->extract_description($post_id);
		}
		else if ((int) $event['start'] > 0)
		{
			$data['description'] = $this->helper->extract_description($first_post_id);
		}
		else
		{
			$data['description'] = $event['rowset'][$first_post_id]['post_text'];
		}

		// Extract image
		$data['image'] = $this->helper->extract_image(
			$data['description'],
			$post_id,
			$event['topic_data']['forum_id']
		);

		// Clean helpers
		$data['description'] = $this->helper->clean_description($data['description']);
		$data['image']['url'] = $this->helper->clean_image($data['image']['url']);

		$this->helper->set_metadata($data);
	}
}
