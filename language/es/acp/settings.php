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
	'ACP_SEO_METADATA_EXPLAIN' => '<p>Aquí puede configurar los meta datos que desee generar y mostrar. Consulte las <a href="https://www.phpbb.com/customise/db/extension/seo_metadata/faq" rel="external nofollow noreferrer noopener" target="_blank"><strong>Preguntas Frecuentes</strong></a> para obtener más información. Si requiere de ayuda, por favor visite la sección de <a href="https://www.phpbb.com/customise/db/extension/seo_metadata/support" rel="external nofollow noreferrer noopener" target="_blank"><strong>Soporte</strong></a>.</p>',
	'ACP_SEO_METADATA_INFO' => 'Los cambios en los siguientes valores sólo serán aplicados a los temas nuevos, si desea que también sean aplicados a los temas antiguos, necesitará limpiar la caché.',

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

	'ACP_SEO_METADATA_DEFAULT_IMAGE_DIMENSIONS' => 'Dimensiones de la imagen por defecto',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_DIMENSIONS_EXPLAIN' => 'Ancho x alto de la imagen por defecto.',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_WIDTH' => 'Ancho de imagen por defecto',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_HEIGHT' => 'Alto de imagen por defecto',

	'ACP_SEO_METADATA_DEFAULT_IMAGE_TYPE' => 'Tipo de la imagen por defecto',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_TYPE_EXPLAIN' => 'El tipo de MIME de la imagen por defecto.',

	'ACP_SEO_METADATA_LOCAL_IMAGES' => 'Imágenes locales',
	'ACP_SEO_METADATA_LOCAL_IMAGES_EXPLAIN' => 'Extrae imágenes del cuerpo del mensaje únicamente de su dominio (<samp>%s</samp>).',

	'ACP_SEO_METADATA_ATTACHMENTS' => 'Incluir adjuntos',
	'ACP_SEO_METADATA_ATTACHMENTS_EXPLAIN' => 'También se incluirán imágenes adjuntas. Serán elegidas en el mismo orden en el que fueron subidas.',

	'ACP_SEO_METADATA_PREFER_ATTACHMENTS' => 'Preferir adjuntos',
	'ACP_SEO_METADATA_PREFER_ATTACHMENTS_EXPLAIN' => 'Las imágenes adjuntas tendrán mayor prioridad sobre las que han sido extraídas del mensaje.',

	'ACP_SEO_METADATA_POST_METADATA' => 'Metadatos de mensajes',
	'ACP_SEO_METADATA_POST_METADATA_EXPLAIN' => 'También generará metadatos para URLs de mensajes específicos.',

	'ACP_SEO_METADATA_DATA_EXPLAIN' => 'Los metadatos son generados de manera dinámica usando los datos de su foro.',

	'ACP_SEO_METADATA_GLOBAL_SETTINGS' => 'Ajustes globales',

	'ACP_SEO_METADATA_OPEN_GRAPH_SETTINGS' => 'Ajustes de Open Graph',
	'ACP_SEO_METADATA_OPEN_GRAPH' => 'Habilitar Open Graph',

	'ACP_SEO_METADATA_FACEBOOK_APPLICATION' => 'ID de aplicación de Facebook',
	'ACP_SEO_METADATA_FACEBOOK_APPLICATION_EXPLAIN' => 'Identificador de su aplicación de Facebook.',
	'ACP_SEO_METADATA_FACEBOOK_PUBLISHER' => 'Editor de Facebook',
	'ACP_SEO_METADATA_FACEBOOK_PUBLISHER_EXPLAIN' => 'La URL de su página de Facebook.',

	'ACP_SEO_METADATA_TWITTER_CARD_SETTINGS' => 'Ajustes de Twitter Cards',
	'ACP_SEO_METADATA_TWITTER_CARDS' => 'Habilitar Twitter Cards',
	'ACP_SEO_METADATA_TWITTER_PUBLISHER' => 'Editor de Twitter',
	'ACP_SEO_METADATA_TWITTER_PUBLISHER_EXPLAIN' => 'El nombre de usuario de la cuenta de Twitter de su sitio web.',

	'ACP_SEO_METADATA_JSON_LD_SETTINGS' => 'Ajustes de JSON-LD',
	'ACP_SEO_METADATA_JSON_LD' => 'Habilitar JSON-LD',
	'ACP_SEO_METADATA_JSON_LD_LOGO' => 'Logotipo del editor',
	'ACP_SEO_METADATA_JSON_LD_LOGO_EXPLAIN' => 'Un logotipo personalizado usado por Google en los resultados de búsqueda. Debe ser mayor a <samp>112</samp>x<samp>112</samp>px y su ruta debe ser relativa a <samp>%s</samp>',
	'ACP_SEO_METADATA_JSON_LD_LOGO_DIMENSIONS' => 'Dimensiones del logotipo del editor',
	'ACP_SEO_METADATA_JSON_LD_LOGO_DIMENSIONS_EXPLAIN' => 'Ancho x alto del logotipo del editor.',

	'ACP_SEO_METADATA_EXTRACTED_IMAGE_DATA' => 'Este dato se extraerá de la imagen.',

	'ACP_SEO_METADATA_VALIDATE_INVALID_FIELDS' => 'Valores inválidos para los campos: %s',
	'ACP_SEO_METADATA_VALIDATE_INVALID_IMAGE' => 'El valor especificado como imagen <samp>%1$s</samp> generó una URL vacía.<br>Pudo ser debido a que la imagen no existe o que el nombre de archivo intentó salir de la ruta <samp>/images/</samp>',
	'ACP_SEO_METADATA_VALIDATE_SMALL_IMAGE' => 'Las dimensiones de la imagen <samp>%1$s</samp> deben ser mayor a <samp>%2$s</samp>x<samp>%3$s</samp>px',
	'ACP_SEO_METADATA_VALIDATE_INVALID_MIME_TYPE' => 'El tipo de medios <samp>%2$s</samp> de la imagen <samp>%1$s</samp> no esta permitido.'
]);
