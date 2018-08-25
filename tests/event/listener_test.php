<?php

/**
 * SEO Metadata Extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2018 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\seometadata\tests\event;

use phpbb_test_case;
use phpbb\config\config;
use alfredoramos\seometadata\includes\helper;
use alfredoramos\seometadata\event\listener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @group event
 */
class listener_test extends phpbb_test_case
{

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \alfredoramos\seometadata\includes\helper */
	protected $helper;

	public function setUp()
	{
		parent::setUp();

		$this->config = $this->getMockBuilder(config::class)
			->disableOriginalConstructor()->getMock();
		$this->helper = $this->getMockBuilder(helper::class)
			->disableOriginalConstructor()->getMock();
	}

	public function test_instance()
	{
		$this->assertInstanceOf(
			EventSubscriberInterface::class,
			new listener($this->config, $this->helper)
		);
	}

	public function test_suscribed_events()
	{
		$this->assertSame(
			[
				'core.page_header_after',
				'core.viewforum_generate_page_after',
				'core.viewtopic_modify_post_data'
			],
			array_keys(listener::getSubscribedEvents())
		);
	}

}
