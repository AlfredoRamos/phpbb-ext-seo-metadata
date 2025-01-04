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
	'ACP_SEO_METADATA_EXPLAIN' => '<p>Zde můžete nastavit metadata, která se mají generovat a zobrazovat. Podrobnosti najdete v <a href="https://www.phpbb.com/customise/db/extension/seo_metadata/faq" rel="external nofollow noreferrer noopener" target="_blank"><strong>často kladených otázkách</strong></a>. Pokud potřebujete pomoci, navštivte prosím sekci <a href="https://www.phpbb.com/customise/db/extension/seo_metadata/support" rel="external nofollow noreferrer noopener" target="_blank"><strong>Support</strong></a>.</p>',
	'ACP_SEO_METADATA_INFO' => 'Změny v nastavení se projeví pouze pro nová témata. Pokud chcete změny aplikovat i pro starší témata, spusťte pročištění mezipaměti fóra.',

	'ACP_SEO_METADATA_META_DESCRIPTION' => 'Povolit popis',
	'ACP_SEO_METADATA_META_DESCRIPTION_EXPLAIN' => 'Přidat metatag s popisem.',

	'ACP_SEO_METADATA_DESC_LENGTH' => 'Délka popisu',
	'ACP_SEO_METADATA_DESC_LENGTH_EXPLAIN' => 'Maximální délka popisu v metatagu (např. <samp>og:description</samp>). Nejvyšší možná hodnota je <samp>255</samp> znaků.',
	'ACP_SEO_METADATA_DESC_STRATEGY' => 'Strategie vytvoření popisu',
	'ACP_SEO_METADATA_DESC_STRATEGY_EXPLAIN' => 'Pokud se popis nevejde do určené maximální délky, bude zkrácen tak, že:<br><samp>Ořez</samp> vloží do popisu přesný počet znaků podle nastavení.<br><samp>Výpustka</samp> přidá na konec zkráceného popisu trojtečku (<code>…</code>).<br><samp>Konec slova</samp> popis ukončí na konci posledního slova tak, aby ještě nepřekročil maximální délku.',
	'ACP_SEO_METADATA_DESC_CUT' => 'Ořez',
	'ACP_SEO_METADATA_DESC_ELLIPSIS' => 'Výpustka',
	'ACP_SEO_METADATA_DESC_BREAK_WORDS' => 'Konec slova',

	'ACP_SEO_METADATA_IMAGE_STRATEGY' => 'Strategie výběru obrázku',
	'ACP_SEO_METADATA_IMAGE_STRATEGY_EXPLAIN' => '<samp>První nalezený</samp> použije první obrázek z těla příspevku.<br><samp>Podle rozměru</samp> vybere z těla příspěvku co největší obrázek podle jeho šířky a výšky.',
	'ACP_SEO_METADATA_IMAGE_FIRST' => 'První nalezený',
	'ACP_SEO_METADATA_IMAGE_DIMENSIONS' => 'Podle rozměru',

	'ACP_SEO_METADATA_DEFAULT_IMAGE' => 'Výchozí obrázek',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_EXPLAIN' => 'URL adresa výchozího obrázku pro metatagy (např. <samp>og:image</samp>). Tento obrázek bude použit jen v případě, že v nebude na stránce nalezený žádný jiný. Jeho velikost musí být větší než <samp>%1$d</samp>x<samp>%1$d</samp>px a jeho URL relativní k <samp>%1$s</samp>',

	'ACP_SEO_METADATA_DEFAULT_IMAGE_DIMENSIONS' => 'Výchozí rozměry obrázku',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_DIMENSIONS_EXPLAIN' => 'Výška a šířka výchozího obrázku.',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_WIDTH' => 'Šířka výchozího obrázku',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_HEIGHT' => 'Výška výchozího obrázku',

	'ACP_SEO_METADATA_DEFAULT_IMAGE_TYPE' => 'Typ výchozího obrázku',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_TYPE_EXPLAIN' => 'MIME type výchozího obrázku.',

	'ACP_SEO_METADATA_LOCAL_IMAGES' => 'Místní obrázky',
	'ACP_SEO_METADATA_LOCAL_IMAGES_EXPLAIN' => 'Používat pouze obrázky z tohoto serveru (<samp>%s</samp>).',

	'ACP_SEO_METADATA_ATTACHMENTS' => 'Zahrnout přílohy',
	'ACP_SEO_METADATA_ATTACHMENTS_EXPLAIN' => 'Zahrnout také obrázky z příloh. Jejich výběr proběhne ve stejném pořadí, v jakém byly nahrány.',

	'ACP_SEO_METADATA_PREFER_ATTACHMENTS' => 'Upřednostňovat přílohy',
	'ACP_SEO_METADATA_PREFER_ATTACHMENTS_EXPLAIN' => 'Obrázky nahrané jako příloha budou vybrány přednostně před těmi v obsahu příspěvku.',

	'ACP_SEO_METADATA_POST_METADATA' => 'Metadata příspěvku',
	'ACP_SEO_METADATA_POST_METADATA_EXPLAIN' => 'Generovat metadata také pro URL adresy konkrétních příspěvků.',

	'ACP_SEO_METADATA_MAX_IMAGES' => 'Počet obrázků',
	'ACP_SEO_METADATA_MAX_IMAGES_EXPLAIN' => 'Maximální počet obrázků k extrakci na příspěvek. Uvědomte si, že zvýšení tohoto čísla může negativně ovlivnit dobu prvního načítání, pokud obrázky nebyly dříve extrahovány pro téma nebo příspěvek. Má tvrdou maximální hodnotu <samp>%d</samp>.',

	'ACP_SEO_METADATA_DATA_EXPLAIN' => 'Metadata jsou generována dynamicky z obsahu vašeho fóra.',

	'ACP_SEO_METADATA_GLOBAL_SETTINGS' => 'Globální nastavení',

	'ACP_SEO_METADATA_OPEN_GRAPH_SETTINGS' => 'Open Graph',
	'ACP_SEO_METADATA_OPEN_GRAPH' => 'Povolit Open Graph',

	'ACP_SEO_METADATA_FACEBOOK_APPLICATION' => 'ID aplikace Facebooku',
	'ACP_SEO_METADATA_FACEBOOK_APPLICATION_EXPLAIN' => 'Identifikátor vaší aplikace na Facebooku.',
	'ACP_SEO_METADATA_FACEBOOK_PUBLISHER' => 'Vydavatel',
	'ACP_SEO_METADATA_FACEBOOK_PUBLISHER_EXPLAIN' => 'URL stránky na Facebooku.',

	'ACP_SEO_METADATA_TWITTER_CARD_SETTINGS' => 'Twitter Cards',
	'ACP_SEO_METADATA_TWITTER_CARDS' => 'Povolit Twitter Cards',
	'ACP_SEO_METADATA_TWITTER_PUBLISHER' => 'Vydavatel',
	'ACP_SEO_METADATA_TWITTER_PUBLISHER_EXPLAIN' => 'Uživatelské jméno učtu vaší stránky na Twitteru.',

	'ACP_SEO_METADATA_JSON_LD_SETTINGS' => 'JSON-LD',
	'ACP_SEO_METADATA_JSON_LD' => 'Povolit JSON-LD',
	'ACP_SEO_METADATA_JSON_LD_LOGO' => 'Logo vydavatele',
	'ACP_SEO_METADATA_JSON_LD_LOGO_EXPLAIN' => 'Vlastní logo použité ve výsledcích vyhledávání na Googlu. Jeho velikost musí být větší než <samp>112</samp> x <samp>112</samp> px a jeho URL relativní k <samp>%s</samp>.',
	'ACP_SEO_METADATA_JSON_LD_LOGO_DIMENSIONS' => 'Rozměry loga vydavatele',
	'ACP_SEO_METADATA_JSON_LD_LOGO_DIMENSIONS_EXPLAIN' => 'Šířka a výška loga vydavatele.',

	'ACP_SEO_METADATA_EXTRACTED_IMAGE_DATA' => 'Tyto údaje budou získány z obrázku.',

	'ACP_SEO_METADATA_VALIDATE_INVALID_FIELDS' => 'Neplatné hodnoty pro pole: %s',
	'ACP_SEO_METADATA_VALIDATE_INVALID_IMAGE' => 'Hodnota zadaná pro obrázek <samp>%1$s</samp> vygenerovala prázdnou URL.<br>To může být proto, že obrázek neexistuje, nebo jeho cesta směřovala mimo adresář <samp>/images/</samp>.',
	'ACP_SEO_METADATA_VALIDATE_SMALL_IMAGE' => 'Rozměry obrázku <samp>%1$s</samp> musí být větší než <samp>%2$s</samp> x <samp>%3$s</samp> px.',
	'ACP_SEO_METADATA_VALIDATE_INVALID_MIME_TYPE' => 'MIME typ <samp>%2$s</samp> obrázku <samp>%1$s</samp> není povolený.'
]);
