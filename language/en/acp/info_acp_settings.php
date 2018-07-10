<?php

/**
 * SEO Metadata Extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
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
	'ACP_SEO_METADATA' => 'SEO Metadata settings',
	'ACP_SEO_METADATA_SETTINGS_SAVED' => 'SEO Metadata settings have been succesfully saved.',

	'ACP_OPEN_GRAPH_SETTINGS' => 'Open Graph settings',
	'ACP_OPEN_GRAPH' => 'Enable Open Graph',
	'ACP_OPEN_GRAPH_EXPLAIN' => 'Metadata are dynamically generated from your board data.',

	'LOG_SEO_METADATA_DATA' => '<strong>SEO Metadata data changed</strong><br />» %s'
]);
