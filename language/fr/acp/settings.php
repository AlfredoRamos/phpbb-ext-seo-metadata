<?php

/**
 * SEO Metadata extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@skiff.com>
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
	'ACP_SEO_METADATA_EXPLAIN' => '<p>Vous pouvez configurer, ici, le contenu des balises meta que vous voulez générer et afficher. Consultez la page <a href="https://www.phpbb.com/customise/db/extension/seo_metadata/faq" rel="external nofollow noreferrer noopener" target="_blank"><strong>FAQ</strong></a> pour obtenir plus d\'informations. Si vous avez besoin d\'aide, veuillez visiter la section <a href="https://www.phpbb.com/customise/db/extension/seo_metadata/support" rel="external nofollow noreferrer noopener" target="_blank"><strong>Support</strong></a> .</p>',
	'ACP_SEO_METADATA_INFO' => 'Les modifications des valeurs suivantes ne seront appliqués qu\'aux nouveaux sujets, si vous souhaitez qu\'ils soient également appliqués aux anciens sujets, vous devez purger le cache du forum.',

	'ACP_SEO_METADATA_META_DESCRIPTION' => 'Activer la description',
	'ACP_SEO_METADATA_META_DESCRIPTION_EXPLAIN' => 'Balise meta de description.',

	'ACP_SEO_METADATA_DESC_LENGTH' => 'Longueur de la description',
	'ACP_SEO_METADATA_DESC_LENGTH_EXPLAIN' => 'Longueur maximale de la description qui sera utilisée dans les balises meta de type <samp>og:description</samp>. La limite maximale est de <samp>255</samp> caractères.',
	'ACP_SEO_METADATA_DESC_STRATEGY' => 'Stratégie de la description',
	'ACP_SEO_METADATA_DESC_STRATEGY_EXPLAIN' => '<samp>Couper</samp> tronque la description à la position exacte si elle excède la longueur maximale.<br><samp>Points de suspension</samp> ajoute des points de suspension (<code>…</code>) à la fin de la description si elle excède la longueur maximale.<br><samp>Extraction de mots</samp> ajoute autant de mots que possible sans excéder la longueur maximale.',
	'ACP_SEO_METADATA_DESC_CUT' => 'Couper',
	'ACP_SEO_METADATA_DESC_ELLIPSIS' => 'Point de suspension',
	'ACP_SEO_METADATA_DESC_BREAK_WORDS' => 'Extraction de mots',

	'ACP_SEO_METADATA_IMAGE_STRATEGY' => 'Stratégie d\'image',
	'ACP_SEO_METADATA_IMAGE_STRATEGY_EXPLAIN' => '<samp>Première trouvée</samp> choisi la première image trouvée dans le corps du message.<br><samp>Dimensions de l\'image</samp> choisi l\'image ayant les plus grandes dimensions (largeur, hauteur) dans le corps du message.',
	'ACP_SEO_METADATA_IMAGE_FIRST' => 'Première trouvée',
	'ACP_SEO_METADATA_IMAGE_DIMENSIONS' => 'Dimensions de l\'image',

	'ACP_SEO_METADATA_DEFAULT_IMAGE' => 'Image par défaut',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_EXPLAIN' => 'URL de l\'image par défaut pour les balises meta de type <samp>og:image</samp>. Cette image ne sera utilisée que si aucune autre image ne peut être trouvée sur la page courante. Elle doit être plus grande que <samp>200</samp> x <samp>200</samp> pixels et être relative à <samp>%s</samp>',

	'ACP_SEO_METADATA_DEFAULT_IMAGE_DIMENSIONS' => 'Dimensions par défaut de l\'image',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_DIMENSIONS_EXPLAIN' => 'Largeur x hauteur de l\'image par défaut.',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_WIDTH' => 'Largeur par défaut de l\'image',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_HEIGHT' => 'Hauteur par défaut de l\'image',

	'ACP_SEO_METADATA_DEFAULT_IMAGE_TYPE' => 'Type d\'image par défaut',
	'ACP_SEO_METADATA_DEFAULT_IMAGE_TYPE_EXPLAIN' => 'Le type MIME de l\'image.',

	'ACP_SEO_METADATA_LOCAL_IMAGES' => 'Images locales',
	'ACP_SEO_METADATA_LOCAL_IMAGES_EXPLAIN' => 'Extraire seulement les images de votre domaine (<samp>%s</samp>).',

	'ACP_SEO_METADATA_ATTACHMENTS' => 'Inclure les pièces-jointes',
	'ACP_SEO_METADATA_ATTACHMENTS_EXPLAIN' => 'Inclus également les images issues des pièces-jointes. Elles seront choisies suivant l\'ordre de leur ajout.',

	'ACP_SEO_METADATA_PREFER_ATTACHMENTS' => 'Préférer les pièces-jointes',
	'ACP_SEO_METADATA_PREFER_ATTACHMENTS_EXPLAIN' => 'Les images extraites des pièces-jointes auront une priorité plus importante que celles extraites du message.',

	'ACP_SEO_METADATA_POST_METADATA' => 'Balises meta de message',
	'ACP_SEO_METADATA_POST_METADATA_EXPLAIN' => 'Générer également des métadonnées pour des URL de publication spécifiques.',

	'ACP_SEO_METADATA_DATA_EXPLAIN' => 'Les balises meta sont générées dynamiquement depuis les données de votre forum.',

	'ACP_SEO_METADATA_GLOBAL_SETTINGS' => 'Paramètres généraux',

	'ACP_SEO_METADATA_OPEN_GRAPH_SETTINGS' => 'Paramètres Open Graph',
	'ACP_SEO_METADATA_OPEN_GRAPH' => 'Activer Open Graph',

	'ACP_SEO_METADATA_FACEBOOK_APPLICATION' => 'ID d\'application Facebook',
	'ACP_SEO_METADATA_FACEBOOK_APPLICATION_EXPLAIN' => 'Identifiant de votre application Facebook.',
	'ACP_SEO_METADATA_FACEBOOK_PUBLISHER' => 'Editeur Facebook',
	'ACP_SEO_METADATA_FACEBOOK_PUBLISHER_EXPLAIN' => 'L\'URL de votre page Facebook.',

	'ACP_SEO_METADATA_TWITTER_CARD_SETTINGS' => 'Paramètres Twitter Cards',
	'ACP_SEO_METADATA_TWITTER_CARDS' => 'Activer Twitter Cards',
	'ACP_SEO_METADATA_TWITTER_PUBLISHER' => 'Editeur Twitter',
	'ACP_SEO_METADATA_TWITTER_PUBLISHER_EXPLAIN' => 'Le nom d\'utilisateur du compte Twitter de votre site.',

	'ACP_SEO_METADATA_JSON_LD_SETTINGS' => 'Paramètres JSON-LD',
	'ACP_SEO_METADATA_JSON_LD' => 'Activer JSON-LD',
	'ACP_SEO_METADATA_JSON_LD_LOGO' => 'Logo éditeur',
	'ACP_SEO_METADATA_JSON_LD_LOGO_EXPLAIN' => 'Un logo personnalisé utilisé par Google dans les résultats des recherches. Il doit mesurer un minimum de <samp>112</samp>x<samp>112</samp>pixels et être relatif à <samp>%s</samp>',
	'ACP_SEO_METADATA_JSON_LD_LOGO_DIMENSIONS' => 'Dimensions du logo éditeur',
	'ACP_SEO_METADATA_JSON_LD_LOGO_DIMENSIONS_EXPLAIN' => 'Largeur x hauteur du logo éditeur.',

	'ACP_SEO_METADATA_EXTRACTED_IMAGE_DATA' => 'Ces données seront extraites de l\'image.',

	'ACP_SEO_METADATA_VALIDATE_INVALID_FIELDS' => 'Valeurs incorrectes pour les champs: %s',
	'ACP_SEO_METADATA_VALIDATE_INVALID_IMAGE' => 'La valeur indiquée pour l\'image <samp>%1$s</samp> à générée une URL vide.<br>Ceci peut être provoqué par le fait que cette image est inexistante ou que le nom de fichier tente de sortir du chemin d\'accès <samp>/images/</samp>.',
	'ACP_SEO_METADATA_VALIDATE_SMALL_IMAGE' => 'Les dimensions de l\'image <samp>%1$s</samp> doivent être supérieures à <samp>%2$s</samp> x <samp>%3$s</samp> pixels',
	'ACP_SEO_METADATA_VALIDATE_INVALID_MIME_TYPE' => 'Le type MIME <samp>%2$s</samp> pour l\'image <samp>%1$s</samp> n\'est pas autorisé.'
]);
