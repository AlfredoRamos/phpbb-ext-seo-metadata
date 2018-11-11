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
	'ACP_SEO_METADATA' => 'SEO Metadata',
	'ACP_SEO_METADATA_EXPLAIN' => 'Nadat u deze waarden hebt gewijzigd, moet u mogelijk de cache opschonen.',

	'ACP_SEO_METADATA_DESC_LENGTH' => 'Beschrijving lengte',
	'ACP_SEO_METADATA_DESC_LENGTH_EXPLAIN' => 'Maximum lengte voor een beschrijving die wordt gebruikt in metatags, zoals <samp>og:description</samp>. Het heeft een vaste limiet van <samp>og:description</samp> karakters.',
	'ACP_SEO_METADATA_DESC_STRATEGY' => 'Beschrijving strategie',
	'ACP_SEO_METADATA_DESC_STRATEGY_EXPLAIN' => '<samp>Knippen</samp> knipt de beschrijving op de exacte positie als deze de maximale lengte overschrijdt.<br><samp>Beletselteken</samp> voegt een beletselteken toe (<code>…</code>) aan het einde van de beschrijving als deze de maximale lengte overschrijdt.<br><samp>Breek woorden</samp> past zoveel mogelijk woorden toe zonder de maximale lengte te overschrijden.',
	'ACP_SEO_METADATA_DESC_CUT' => 'Knippen',
	'ACP_SEO_METADATA_DESC_ELLIPSIS' => 'Beletselteken',
	'ACP_SEO_METADATA_DESC_BREAK_WORDS' => 'Breek woorden',

	'ACP_SEO_METADATA_IMAGE_STRATEGY' => 'Beeldstrategie',
	'ACP_SEO_METADATA_IMAGE_STRATEGY_EXPLAIN' => '<samp>Eerst gevonden</samp> selecteert de eerste gevonden afbeelding die kan worden gebruikt in de berichttekst.<br><samp>Afbeelding afmetingen</samp> selecteert de afbeelding met de grootste afmetingen (breedte, hoogte) binnen het bericht.',
	'ACP_SEO_METADATA_IMAGE_FIRST' => 'Eerst gevonden',
	'ACP_SEO_METADATA_IMAGE_DIMENSIONS' => 'Afbeelding afmetingen',

	'ACP_SEO_METADATA_DEFAULT_IMAGE' => 'Standaard afbeelding',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_EXPLAIN' => 'Standaard afbeelding URL voor metatags zoals <samp>og:image</samp>. Het wordt alleen gebruikt als er geen afbeelding kan worden gevonden binnen de huidige pagina. Het moet relatief zijn ten opzichte van <samp>%s</samp>',

	'ACP_SEO_METADATA_LOCAL_IMAGES' => 'Locale afbeeldingen',
	'ACP_SEO_METADATA_LOCAL_IMAGES_EXPLAIN' => 'Haal alleen afbeeldingen op uit uw domein (<samp>%s</samp>). Deze instelling heeft ook invloed op de standaardafbeelding.',

	'ACP_SEO_METADATA_SETTINGS_SAVED' => 'SEO Metadata instellingen zijn succesvol opgeslagen.',

	'ACP_SEO_METADATA_DATA_EXPLAIN' => 'Metadata worden dynamisch gegenereerd op basis van uw forum gegevens.',

	'ACP_GLOBAL_SETTINGS' => 'Algemene instellingen',

	'ACP_OPEN_GRAPH_SETTINGS' => 'Open Graph instellingen',
	'ACP_OPEN_GRAPH' => 'Schakel Open Graph in',

	'ACP_FACEBOOK_APPLICATION' => 'Facebook applicatie ID',
	'ACP_FACEBOOK_APPLICATION_EXPLAIN' => 'Identificatie van uw Facebook applicatie.',
	'ACP_FACEBOOK_PUBLISHER' => 'Facebook uitgever',
	'ACP_FACEBOOK_PUBLISHER_EXPLAIN' => 'De URL van uw Facebook pagina.',

	'ACP_TWITTER_CARD_SETTINGS' => 'Instellingen voor Twitter kaarten',
	'ACP_TWITTER_CARDS' => 'Twitter kaarten inschakelen',
	'ACP_TWITTER_PUBLISHER' => 'Twitter uitgever',
	'ACP_TWITTER_PUBLISHER_EXPLAIN' => 'De gebruikersnaam van uw website Twitter account.',

	'ACP_JSON_LD_SETTINGS' => 'JSON-LD instellingen',
	'ACP_JSON_LD' => 'Schakel JSON-LD in',

	'LOG_SEO_METADATA_DATA' => '<strong>SEO Metadata gegevens gewijzigd</strong><br>» %s'
]);
