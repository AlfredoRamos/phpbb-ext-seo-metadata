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
	'ACP_SEO_METADATA' => 'Metadatos SEO',
	'ACP_SEO_METADATA_EXPLAIN' => 'Los cambios en los siguientes valores sólo serán aplicados a los temas nuevos, si desea que tambien sean aplicados a los temas antiguos, necesitará limpiar la caché.',

	'ACP_SEO_METADATA_META_DESCRIPTION' => 'Habilitar descripción',
	'ACP_SEO_METADATA_META_DESCRIPTION_EXPLAIN' => 'Metatag de descripción.',

	'ACP_SEO_METADATA_DESC_LENGTH' => 'Longitud de la descripción',
	'ACP_SEO_METADATA_DESC_LENGTH_EXPLAIN' => 'Longitud máxima para la descripción que será utilizada en metaetiquetas como <samp>og:description</samp>. Tiene un límite máximo de <samp>255</samp> caracteres.',
	'ACP_SEO_METADATA_DESC_STRATEGY' => 'Estrategia para la descripción',
	'ACP_SEO_METADATA_DESC_STRATEGY_EXPLAIN' => '<samp>Cortar</samp> trunca la descripción en la posición exacta si ésta excede la longitud máxima.<br><samp>Puntos suspensivos</samp> añade puntos suspensivos (<code>…</code>) al final de la descripción si ésta excede la longitud máxima.<br><samp>Dividir palabras</samp> añade tantas palabras como sea posible sin exceder la longitud máxima.',
	'ACP_SEO_METADATA_DESC_CUT' => 'Cortar',
	'ACP_SEO_METADATA_DESC_ELLIPSIS' => 'Puntos suspensivos',
	'ACP_SEO_METADATA_DESC_BREAK_WORDS' => 'Dividir palabras',

	'ACP_SEO_METADATA_IMAGE_STRATEGY' => 'Estrategia para la imagen',
	'ACP_SEO_METADATA_IMAGE_STRATEGY_EXPLAIN' => '<samp>Primera encontrada</samp> selecciona la primer imagen encontrada que pueda ser usada dentro del cuerpo del mensaje.<br><samp>Dimensiones de imagen</samp> selecciona la imagen con mayor dimensiones (ancho, alto) dentro del cuerpo del mensaje.',
	'ACP_SEO_METADATA_IMAGE_FIRST' => 'Primera encontrada',
	'ACP_SEO_METADATA_IMAGE_DIMENSIONS' => 'Dimensiones de imagen',

	'ACP_SEO_METADATA_DEFAULT_IMAGE' => 'Imagen por defecto',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_EXPLAIN' => 'URL de la imagen por defecto que será usada en metaetiquetas como <samp>og:image</samp>. Solo será usada si no se puede encontrar una imagen en la página actual. La imagen debe ser mayor a <samp>200</samp>x<samp>200</samp>px y su ruta debe ser relativa a <samp>%s</samp>',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_INVALID' => 'El valor especificado como imagen por defecto <samp>%1$s</samp> generó una URL vacía.<br>Pudo ser debido a que la imágen no existe o que el nombre de archivo intentó salir de la ruta <samp>/images/</samp>',

	'ACP_SEO_METADATA_DEFAULT_IMAGE_DIMENSIONS' => 'Dimensiones de la imagen por defecto',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_DIMENSIONS_EXPLAIN' => 'Ancho x alto de la imagen por defecto. Coloque <samp>0</samp> en ambos para intentar estimar sus dimensiones.',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_WIDTH' => 'Ancho de imagen por defecto',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_HEIGHT' => 'Alto de imagen por defecto',

	'ACP_SEO_METADATA_DEFAULT_IMAGE_TYPE' => 'Tipo de la imagen por defecto',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_TYPE_EXPLAIN' => 'El tipo de MIME de la imagen por defecto. Déjelo en blanco para intentar estimar el tipo, si no conoce esta información o no esta seguro.',

	'ACP_SEO_METADATA_ATTACHMENTS' => 'Incluir adjuntos',
	'ACP_SEO_METADATA_ATTACHMENTS_EXPLAIN' => 'También se incluirán imágenes adjuntas. Serán elegidas en el mismo orden en el que fueron subidas.',

	'ACP_SEO_METADATA_PREFER_ATTACHMENTS' => 'Preferir adjuntos',
	'ACP_SEO_METADATA_PREFER_ATTACHMENTS_EXPLAIN' => 'Las imágenes adjuntas tendrán mayor prioridad sobre las que han sido extraídas del mensaje.',

	'ACP_SEO_METADATA_LOCAL_IMAGES' => 'Imágenes locales',
	'ACP_SEO_METADATA_LOCAL_IMAGES_EXPLAIN' => 'Extrae imágenes del cuerpo del mensaje únicamente de su dominio (<samp>%s</samp>).',

	'ACP_SEO_METADATA_DATA_EXPLAIN' => 'Los metadatos son generados de manera dinámica usando los datos de su foro.',

	'ACP_SEO_METADATA_GLOBAL_SETTINGS' => 'Ajustes globales',

	'ACP_SEO_METADATA_OPEN_GRAPH_SETTINGS' => 'Ajustes de Open Graph',
	'ACP_SEO_METADATA_OPEN_GRAPH' => 'Habilitar Open Graph',

	'ACP_SEO_METADATA_FACEBOOK_APPLICATION' => 'ID de la applicación de Facebook',
	'ACP_SEO_METADATA_FACEBOOK_APPLICATION_EXPLAIN' => 'Identificador de su applicación de Facebook.',
	'ACP_SEO_METADATA_FACEBOOK_PUBLISHER' => 'Editor de Facebook',
	'ACP_SEO_METADATA_FACEBOOK_PUBLISHER_EXPLAIN' => 'La URL de su página de Facebook.',

	'ACP_SEO_METADATA_TWITTER_CARD_SETTINGS' => 'Ajustes de Twitter Cards',
	'ACP_SEO_METADATA_TWITTER_CARDS' => 'Habilitar Twitter Cards',
	'ACP_SEO_METADATA_TWITTER_PUBLISHER' => 'Editor de Twitter',
	'ACP_SEO_METADATA_TWITTER_PUBLISHER_EXPLAIN' => 'El nombre de usuario de la cuenta de Twitter de su sitio web.',

	'ACP_SEO_METADATA_JSON_LD_SETTINGS' => 'Ajustes de JSON-LD',
	'ACP_SEO_METADATA_JSON_LD' => 'Habilitar JSON-LD',

	'ACP_SEO_METADATA_VALIDATE_INVALID_FIELDS' => 'Valores inválidos para los campos: %s',

	'LOG_SEO_METADATA_DATA' => '<strong>Datos de Metadatos SEO modificados</strong><br>» %s'
]);
