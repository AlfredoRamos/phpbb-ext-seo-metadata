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
	'ACP_SEO_METADATA_EXPLAIN' => '<p>Здесь вы можете настроить метаданные, которые вы хотите сгенерировать и отобразить. Ознакомьтесь с разделом <a href="https://www.phpbb.com/customise/db/extension/seo_metadata/faq" rel="external nofollow noreferrer noopener" target="_blank"><strong>FAQ</strong>< /a> для получения дополнительной информации. Если вам нужна помощь, посетите раздел <a href="https://www.phpbb.com/customise/db/extension/seo_metadata/support" rel="external nofollow noreferrer noopener" target="_blank"><strong>Поддержка</strong></a>.</p>',
	'ACP_SEO_METADATA_INFO' => 'Изменения следующих значений будут применены только к новым темам, если вы хотите, чтобы они были применены и к старым темам, вам нужно будет очистить кэш.',

	'ACP_SEO_METADATA_META_DESCRIPTION' => 'Включить описание',
	'ACP_SEO_METADATA_META_DESCRIPTION_EXPLAIN' => 'Метатег описания.',

	'ACP_SEO_METADATA_DESC_LENGTH' => 'Длина описания',
	'ACP_SEO_METADATA_DESC_LENGTH_EXPLAIN' => 'Максимальная длина описания, которая будет использоваться в метатегах, таких как <samp>og:description</samp>. н имеет жесткое ограничение в <samp>255</samp> символов.',
	'ACP_SEO_METADATA_DESC_STRATEGY' => 'Стратегия описания',
	'ACP_SEO_METADATA_DESC_STRATEGY_EXPLAIN' => '<samp>Обрезать</samp> обрезает описание в точном месте, если его длина превышает максимальную длину.<br><samp>Многоточие</samp> добавляет многоточие (<code>…</code>) в конец описания, если оно превышает максимальную длину.<br><samp>Разбить слова</samp> вмещает как можно больше слов, не превышая максимальную длину.',
	'ACP_SEO_METADATA_DESC_CUT' => 'Обрезать',
	'ACP_SEO_METADATA_DESC_ELLIPSIS' => 'Многоточие',
	'ACP_SEO_METADATA_DESC_BREAK_WORDS' => 'Разбить слова',

	'ACP_SEO_METADATA_IMAGE_STRATEGY' => 'Стратегия для изображения',
	'ACP_SEO_METADATA_IMAGE_STRATEGY_EXPLAIN' => '<samp>Первое найденное</samp> выбирает первое найденное изображение, которое можно использовать в теле поста.<br><samp>По размеру изображения</samp> sвыбирает изображение с большими размерами (ширина, высота) в теле поста.',
	'ACP_SEO_METADATA_IMAGE_FIRST' => 'Первое найденное',
	'ACP_SEO_METADATA_IMAGE_DIMENSIONS' => 'По размеру изображения',

	'ACP_SEO_METADATA_DEFAULT_IMAGE' => 'Изображение по умолчанию',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_EXPLAIN' => 'URL изображения по умолчанию для метатегов, таких как <samp>og:image</samp>. Он будет использоваться только в том случае, если изображение не может быть найдено на текущей странице. Он должен быть больше, чем <samp>%1$d</samp> x <samp>%1$d</samp> пикселей и относительно <samp>%2$s</samp>',

	'ACP_SEO_METADATA_DEFAULT_IMAGE_DIMENSIONS' => 'Размеры изображения по умолчанию',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_DIMENSIONS_EXPLAIN' => 'Ширина и высота изображения по умолчанию.',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_WIDTH' => 'Ширина изображения по умолчанию',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_HEIGHT' => 'Высота изображения по умолчанию',

	'ACP_SEO_METADATA_DEFAULT_IMAGE_TYPE' => 'Тип изображения по умолчанию',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_TYPE_EXPLAIN' => 'Тип MIME изображения по умолчанию.',

	'ACP_SEO_METADATA_LOCAL_IMAGES' => 'Локальные изображения',
	'ACP_SEO_METADATA_LOCAL_IMAGES_EXPLAIN' => 'Извлекать изображения постов только из вашего домена (<samp>%s</samp>).',

	'ACP_SEO_METADATA_ATTACHMENTS' => 'Включать вложения',
	'ACP_SEO_METADATA_ATTACHMENTS_EXPLAIN' => 'Также включать изображения из вложений. Они будут выбраны в том же порядке, в котором были загружены.',

	'ACP_SEO_METADATA_PREFER_ATTACHMENTS' => 'Предпочитать вложения',
	'ACP_SEO_METADATA_PREFER_ATTACHMENTS_EXPLAIN' => 'Изображения вложений будут иметь более высокий приоритет по сравнению с теми, которые были извлечены из поста.',

	'ACP_SEO_METADATA_POST_METADATA' => 'Метаданные поста',
	'ACP_SEO_METADATA_POST_METADATA_EXPLAIN' => 'Также генерировать метаданные для определенных URL-адресов поста.',

	'ACP_SEO_METADATA_MAX_IMAGES' => 'Количество изображений',
	'ACP_SEO_METADATA_MAX_IMAGES_EXPLAIN' => 'Максимальное количество изображений для извлечения на пост. Имейте в виду, что увеличение этого числа может негативно повлиять на время первой загрузки, если изображения ранее не извлекались для темы или поста. Оно имеет жесткое максимальное значение <samp>%d</samp>.',

	'ACP_SEO_METADATA_DATA_EXPLAIN' => 'Метаданные динамически генерируются из данных вашего форума.',

	'ACP_SEO_METADATA_GLOBAL_SETTINGS' => 'Глобальные настройки',

	'ACP_SEO_METADATA_OPEN_GRAPH_SETTINGS' => 'Настройки Open Graph',
	'ACP_SEO_METADATA_OPEN_GRAPH' => 'Включить Open Graph',

	'ACP_SEO_METADATA_FACEBOOK_APPLICATION' => 'ID приложения Facebook',
	'ACP_SEO_METADATA_FACEBOOK_APPLICATION_EXPLAIN' => 'Идентификатор вашего приложения Facebook.',
	'ACP_SEO_METADATA_FACEBOOK_PUBLISHER' => 'Издатель Facebook',
	'ACP_SEO_METADATA_FACEBOOK_PUBLISHER_EXPLAIN' => 'URL вашей страницы Facebook.',

	'ACP_SEO_METADATA_TWITTER_CARD_SETTINGS' => 'Настройки карт Twitter',
	'ACP_SEO_METADATA_TWITTER_CARDS' => 'Включить карты Twitter',
	'ACP_SEO_METADATA_TWITTER_PUBLISHER' => 'Издатель Twitter',
	'ACP_SEO_METADATA_TWITTER_PUBLISHER_EXPLAIN' => 'Имя пользователя вашего веб-сайта в Twitter.',

	'ACP_SEO_METADATA_JSON_LD_SETTINGS' => 'Настройки JSON-LDs',
	'ACP_SEO_METADATA_JSON_LD' => 'Включить JSON-LD',
	'ACP_SEO_METADATA_JSON_LD_LOGO' => 'Логотип издателя',
	'ACP_SEO_METADATA_JSON_LD_LOGO_EXPLAIN' => 'Пользовательский логотип, используемый Google в результатах поиска. Он должен быть больше <samp>112</samp> x <samp>112</samp> пикселей и относительно <samp>%s</samp>.',
	'ACP_SEO_METADATA_JSON_LD_LOGO_DIMENSIONS' => 'Размеры логотипа издателя',
	'ACP_SEO_METADATA_JSON_LD_LOGO_DIMENSIONS_EXPLAIN' => 'Ширина и высота логотипа издателя.',

	'ACP_SEO_METADATA_EXTRACTED_IMAGE_DATA' => 'Эти данные будут извлечены из изображения.',

	'ACP_SEO_METADATA_VALIDATE_INVALID_FIELDS' => 'Недопустимые значения полей: %s',
	'ACP_SEO_METADATA_VALIDATE_INVALID_IMAGE' => 'Значение, указанное для изображения <samp>%1$s</samp> сгенерировало пустой URL.<br>Возможно, было указано несуществующее изображение или файл находится за пределами <samp>/images/</samp> path.',
	'ACP_SEO_METADATA_VALIDATE_SMALL_IMAGE' => 'Размеры изображения <samp>%1$s</samp> должны быть больше, чем <samp>%2$s</samp> x <samp>%3$s</samp> пикселей.',
	'ACP_SEO_METADATA_VALIDATE_INVALID_MIME_TYPE' => 'Тип MIME <samp>%2$s</samp> для изображения <samp>%1$s</samp> не допустим.'
]);
