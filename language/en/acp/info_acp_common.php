<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@proton.me>
 * @copyright 2018 Alfredo Ramos
 * @license GNU GPL-2.0-only
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * @ignore
 */
if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'ACP_SEO_METADATA' => 'SEO Metadata',
	'LOG_SEO_METADATA_DATA' => '<strong>SEO Metadata data changed</strong><br>» %s',

	// TODO: Remove debug language
	'LOG_SEO_METADATA_DEBUG_ENABLE' => '<strong>[DEBUG] SEO Metadata</strong><br>»GD extension loaded: <code>%s</code>',
]);
