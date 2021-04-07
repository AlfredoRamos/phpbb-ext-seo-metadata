### About

SEO Metadata extension for phpBB

[![Build Status](https://img.shields.io/github/workflow/status/AlfredoRamos/phpbb-ext-seo-metadata/CI?style=flat-square)](https://github.com/AlfredoRamos/phpbb-ext-seo-metadata/actions)
[![Latest Stable Version](https://img.shields.io/github/tag/AlfredoRamos/phpbb-ext-seo-metadata.svg?style=flat-square&label=stable)](https://github.com/AlfredoRamos/phpbb-ext-seo-metadata/releases)
[![Code Quality](https://img.shields.io/codacy/grade/5da9411a064c41c6931af2a398dfad37.svg?style=flat-square)](https://app.codacy.com/manual/AlfredoRamos/phpbb-ext-seo-metadata/dashboard)
[![Translation Progress](https://badges.crowdin.net/phpbb-ext-seo-metadata/localized.svg)](https://crowdin.com/project/phpbb-ext-seo-metadata)
[![License](https://img.shields.io/github/license/AlfredoRamos/phpbb-ext-seo-metadata.svg?style=flat-square)](https://raw.githubusercontent.com/AlfredoRamos/phpbb-ext-seo-metadata/master/license.txt)

Add dynamically generated meta tags and microdata (Open Graph, Twitter Cards and JSON-LD) of your forums and topics to improve SEO of your board and show correct information when you share it on social networks, including (but not limited to) Telegram, WhatsApp, Facebook, Twitter and Vkontakte.

If available, it will dynamically generate and include the following data inside the `<head>` tag:

#### Meta description

```html
<meta name="description" content="...">
```

#### Open Graph

```html
<meta property="fb:app_id" content="...">
<meta property="og:locale" content="...">
<meta property="og:site_name" content="...">
<meta property="og:url" content="...">
<meta property="og:type" content="article">
<meta property="og:title" content="...">
<meta property="og:description" content="...">
<meta property="og:image" content="...">
<meta property="og:image:type" content="...">
<meta property="og:image:width" content="...">
<meta property="og:image:height" content="...">
<meta property="article:published_time" content="...">
<meta property="article:section" content="...">
<meta property="article:publisher" content="...">
```

#### Twitter Cards

```html
<meta name="twitter:card" content="summary|summary_large_image">
<meta name="twitter:site" content="...">
<meta name="twitter:title" content="...">
<meta name="twitter:description" content="...">
<meta name="twitter:image" content="..">
```

#### JSON-LD

```html
<script type="application/ld+json">
{
	"@context": "https://schema.org",
	"@type": "DiscussionForumPosting",
	"url": "...",
	"headline": "...",
	"description": "...",
	"image": "...",
	"author": {
		"@type": "Person",
		"name": "..."
	},
	"datePublished": "...",
	"articleSection": "...",
	"publisher": {
		"@type": "Organization",
		"name": "...",
		"url": "...",
		"logo": {
			"@type": "ImageObject",
			"url": "...",
			"width": "...",
			"height": "..."
		}
	}
}
</script>
```

### Features

- Dynamically generated Open Graph, Twitter Cards meta tags and JSON-LD microdata from your board data and current page
- Dynamic description
- Set default image for Open Graph and JSON-LD
- Set how description will be generated
- ACP settings to enable/disable Open Graph, Twitter Cards and JSON-LD
- Support for attachments, for topic image
- Generate meta data for specific posts

### Requirements

- PHP 7.1.3 or greater
- phpBB 3.3 or greater

### Support

- [**Download page**](https://www.phpbb.com/customise/db/extension/seo_metadata/)
- [Support area](https://www.phpbb.com/customise/db/extension/seo_metadata/support)
- [GitHub issues](https://github.com/AlfredoRamos/phpbb-ext-seo-metadata/issues)
- [Crowdin translations](https://crowdin.com/project/phpbb-ext-seo-metadata)

### Donate

If you like or found my work useful and want to show some appreciation, you can consider supporting its development by giving a donation.

[![Donate with PayPal](https://alfredoramos.mx/images/donate.svg)](https://alfredoramos.mx/donate/)

[![Donate with PayPal](https://www.paypalobjects.com/webstatic/i/logo/rebrand/ppcom.svg)](https://alfredoramos.mx/donate/)

### Installation

- Download the [latest release](https://github.com/AlfredoRamos/phpbb-ext-seo-metadata/releases)
- Decompress the `*.zip` or `*.tar.gz` file
- Copy the files and directories inside `{PHPBB_ROOT}/ext/alfredoramos/seometadata/`
- Go to your `Administration Control Panel` > `Customize` > `Manage extensions`
- Click on `Enable` and confirm

### Preview

[![Global settings](https://i.imgur.com/8rg2fKIb.png)](https://i.imgur.com/8rg2fKI.png)
[![Open Graph, Twitter Cards and JSON-LD settings](https://i.imgur.com/042NB5Fb.png)](https://i.imgur.com/042NB5F.png)
[![Generated markup](https://i.imgur.com/xKswZUHb.png)](https://i.imgur.com/xKswZUH.png)

*(Click to view in full size)*

### Configuration

- Go to your `Administration Control Panel` > `Extensions` > `SEO Metadata settings`
- Change settings as needed
- Click on `Submit`

### Uninstallation

- Go to your `Administration Control Panel` > `Customize` > `Manage extensions`
- Click on `Disable` and confirm
- Go back to `Manage extensions` > `SEO Metadata` > `Delete data` and confirm

### Upgrade

- Uninstall the extension
- Delete all the files inside `{PHPBB_ROOT}/ext/alfredoramos/seometadata/`
- Download the new version
- Install the extension
