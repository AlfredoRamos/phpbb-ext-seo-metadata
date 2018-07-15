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
	'ACP_SEO_METADATA' => 'Ajuses de Metadatos SEO',
	'ACP_SEO_METADATA_EXPLAIN' => 'Después de cambiar éstos valores, tal vez necesite limpiar la caché.',

	'ACP_SEO_METADATA_DESC_LENGTH' => 'Longitud de la descripción',
	'ACP_SEO_METADATA_DESC_LENGTH_EXPLAIN' => 'Longitud máxima para la descripción que será utilizada en metaetiquetas como <samp>og:description</samp>. Tiene un límite máximo de <samp>255</samp> caracteres.',
	'ACP_SEO_METADATA_DESC_STRATEGY' => 'Estrategia para la descripción',
	'ACP_SEO_METADATA_DESC_STRATEGY_EXPLAIN' => '<samp>Cortar</samp> trunca la descripción en la posición exacta si ésta excede la longitud máxima.<br /><samp>Puntos suspensivos</samp> añade puntos suspensivos (<code>…</code>) al final de la descripción si ésta excede la longitud máxima.<br /><samp>Dividir palabras</samp> añade tantas palabras como sea posible sin exceder la longitud máxima.',
	'ACP_SEO_METADATA_DESC_CUT' => 'Cortar',
	'ACP_SEO_METADATA_DESC_ELLIPSIS' => 'Puntos suspensivos',
	'ACP_SEO_METADATA_DESC_BREAK_WORDS' => 'Dividir palabras',

	'ACP_SEO_METADATA_DEFAULT_IMAGE' => 'Imagen por defecto',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_EXPLAIN' => 'URL de la imagen por defecto que será usada en metaetiquetas como <samp>og:image</samp>. Solo será usada si no se puede encontrar una imagen en la página actual. La ruta debe ser relativa a <samp>%s</samp>',

	'ACP_SEO_METADATA_SETTINGS_SAVED' => 'Los ajustes de Metadatos SEO Metadata han sido guardados satisfactoriamente.',

	'ACP_SEO_METADATA_DATA_EXPLAIN' => 'Los metadatos son generados de manera dinámica usando los datos de su foro.',

	'ACP_GLOBAL_SETTINGS' => 'Ajustes globales',

	'ACP_OPEN_GRAPH_SETTINGS' => 'Ajustes de Open Graph',
	'ACP_OPEN_GRAPH' => 'Habilitar Open Graph',

	'ACP_JSON_LD_SETTINGS' => 'Ajustes de JSON-LD',
	'ACP_JSON_LD' => 'Habilitar JSON-LD',

	'LOG_SEO_METADATA_DATA' => '<strong>Datos de Metadatos SEO modificados</strong><br />» %s'
]);
