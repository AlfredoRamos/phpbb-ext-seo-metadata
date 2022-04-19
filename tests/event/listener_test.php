<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@protonmail.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\tests\event;

use alfredoramos\seometadata\includes\helper;
use alfredoramos\seometadata\event\listener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @group event
 */
class listener_test extends \phpbb_test_case
{
	/** @var \alfredoramos\seometadata\includes\helper */
	protected $helper;

	protected function setUp(): void
	{
		parent::setUp();
		$this->helper = $this->getMockBuilder(helper::class)
			->disableOriginalConstructor()->getMock();
	}

	public function test_instance()
	{
		$this->assertInstanceOf(
			EventSubscriberInterface::class,
			new listener($this->helper)
		);
	}

	public function test_subscribed_events()
	{
		$this->assertSame(
			[
				'core.page_header_after',
				'core.viewforum_modify_page_title',
				'core.viewtopic_modify_post_data'
			],
			array_keys(listener::getSubscribedEvents())
		);
	}
}
