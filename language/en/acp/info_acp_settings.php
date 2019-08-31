<?php

/**
 * SEO Metadata extension for phpBB.
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
	'ACP_SEO_METADATA' => 'SEO Metadata',
	'ACP_SEO_METADATA_EXPLAIN' => 'Changes to the following values will only be applied to new topics, if you want it to be applied to old topics too, you will need to purge the cache.',

	'ACP_SEO_METADATA_META_DESCRIPTION' => 'Enable description',
	'ACP_SEO_METADATA_META_DESCRIPTION_EXPLAIN' => 'Description metatag.',

	'ACP_SEO_METADATA_DESC_LENGTH' => 'Description length',
	'ACP_SEO_METADATA_DESC_LENGTH_EXPLAIN' => 'Maximum length for description that will be used in meta tags such as <samp>og:description</samp>. It has a hard limit of <samp>255</samp> characters.',
	'ACP_SEO_METADATA_DESC_STRATEGY' => 'Description strategy',
	'ACP_SEO_METADATA_DESC_STRATEGY_EXPLAIN' => '<samp>Cut</samp> truncates description at exact position if it exceeds maximum length.<br><samp>Ellipsis</samp> adds an elipsis (<code>…</code>) to the end of description if it exceeds maximum length.<br><samp>Break words</samp> fits as many words as possible without exceeding the maximum length.',
	'ACP_SEO_METADATA_DESC_CUT' => 'Cut',
	'ACP_SEO_METADATA_DESC_ELLIPSIS' => 'Ellipsis',
	'ACP_SEO_METADATA_DESC_BREAK_WORDS' => 'Break words',

	'ACP_SEO_METADATA_IMAGE_STRATEGY' => 'Image strategy',
	'ACP_SEO_METADATA_IMAGE_STRATEGY_EXPLAIN' => '<samp>First found</samp> selects the first image found that could be used within the post body.<br><samp>Image dimensions</samp> selects the image with greater dimensions (width, height) within the post body.',
	'ACP_SEO_METADATA_IMAGE_FIRST' => 'First found',
	'ACP_SEO_METADATA_IMAGE_DIMENSIONS' => 'Image dimensions',

	'ACP_SEO_METADATA_DEFAULT_IMAGE' => 'Default image',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_EXPLAIN' => 'Default image URL for meta tags such as <samp>og:image</samp>. It will only be used if an image cannot be found within the current page. It must be larger than <samp>200</samp>x<samp>200</samp>px and relative to <samp>%s</samp>',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_INVALID' => 'The value specified as default image <samp>%1$s</samp> generated an empty URL.<br>It could be due a non-existent image was specified or the file name was trying to go outside the <samp>/images/</samp> path.',

	'ACP_SEO_METADATA_DEFAULT_IMAGE_DIMENSIONS' => 'Default image dimensions',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_DIMENSIONS_EXPLAIN' => 'Width x height of default image. Set both to <samp>0</samp> to try to guess the image dimensions.',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_WIDTH' => 'Default image width',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_HEIGHT' => 'Default image height',

	'ACP_SEO_METADATA_DEFAULT_IMAGE_TYPE' => 'Default image type',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_TYPE_EXPLAIN' => 'The MIME type of default image. Leave it blank to try to guess the type, if you do not know this information or are unsure.',

	'ACP_SEO_METADATA_LOCAL_IMAGES' => 'Local images',
	'ACP_SEO_METADATA_LOCAL_IMAGES_EXPLAIN' => 'Only extract post images from your domain (<samp>%s</samp>).',

	'ACP_SEO_METADATA_ATTACHMENTS' => 'Include attachments',
	'ACP_SEO_METADATA_ATTACHMENTS_EXPLAIN' => 'Also include images from attachments. They will be chosen in the same order they were uploaded.',

	'ACP_SEO_METADATA_PREFER_ATTACHMENTS' => 'Prefer attachments',
	'ACP_SEO_METADATA_PREFER_ATTACHMENTS_EXPLAIN' => 'Attachment images will have higher priority over the ones that were extracted from the post.',

	'ACP_SEO_METADATA_DATA_EXPLAIN' => 'Metadata are dynamically generated from your board data.',

	'ACP_SEO_METADATA_GLOBAL_SETTINGS' => 'Global settings',

	'ACP_SEO_METADATA_OPEN_GRAPH_SETTINGS' => 'Open Graph settings',
	'ACP_SEO_METADATA_OPEN_GRAPH' => 'Enable Open Graph',

	'ACP_SEO_METADATA_FACEBOOK_APPLICATION' => 'Facebook application ID',
	'ACP_SEO_METADATA_FACEBOOK_APPLICATION_EXPLAIN' => 'Identifier of your Facebook application.',
	'ACP_SEO_METADATA_FACEBOOK_PUBLISHER' => 'Facebook publisher',
	'ACP_SEO_METADATA_FACEBOOK_PUBLISHER_EXPLAIN' => 'The URL of your Facebook page.',

	'ACP_SEO_METADATA_TWITTER_CARD_SETTINGS' => 'Twitter Cards settings',
	'ACP_SEO_METADATA_TWITTER_CARDS' => 'Enable Twitter Cards',
	'ACP_SEO_METADATA_TWITTER_PUBLISHER' => 'Twitter publisher',
	'ACP_SEO_METADATA_TWITTER_PUBLISHER_EXPLAIN' => 'The username of your website Twitter account.',

	'ACP_SEO_METADATA_JSON_LD_SETTINGS' => 'JSON-LD settings',
	'ACP_SEO_METADATA_JSON_LD' => 'Enable JSON-LD',

	'ACP_SEO_METADATA_VALIDATE_INVALID_FIELDS' => 'Invalid values for fields: %s',

	'LOG_SEO_METADATA_DATA' => '<strong>SEO Metadata data changed</strong><br>» %s'
]);
