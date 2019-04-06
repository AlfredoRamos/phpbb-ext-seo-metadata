### About

SEO Metadata extension for phpBB

[![Build Status](https://img.shields.io/travis/com/AlfredoRamos/phpbb-ext-seo-metadata.svg?style=flat-square)](https://travis-ci.com/AlfredoRamos/phpbb-ext-seo-metadata)
[![Latest Stable Version](https://img.shields.io/github/tag/AlfredoRamos/phpbb-ext-seo-metadata.svg?style=flat-square&label=stable)](https://github.com/AlfredoRamos/phpbb-ext-seo-metadata/releases)
[![Code Quality](https://img.shields.io/codacy/grade/fb43678c76ca48acad229d1631169aa7.svg?style=flat-square)](https://app.codacy.com/app/AlfredoRamos/phpbb-ext-seo-metadata)
[![License](https://img.shields.io/github/license/AlfredoRamos/phpbb-ext-seo-metadata.svg?style=flat-square)](https://raw.githubusercontent.com/AlfredoRamos/phpbb-ext-seo-metadata/master/license.txt)

### Dependencies

- PHP 5.6 or greater
- phpBB 3.2 or greater

### Installation

- Download the [latest release](https://github.com/AlfredoRamos/phpbb-ext-seo-metadata/releases)
- Decompress the `*.zip` or `*.tar.gz` file
- Copy the files and directories inside `{PHPBB_ROOT}/ext/alfredoramos/seometadata/`
- Go to your `Administration Control Panel` > `Customize` > `Manage extensions`
- Click on `Enable` and confirm

### Usage

If available, it will dynamically generate and include the following data inside the `<head>` tag:

Open Graph:
```html
<meta property="fb:app_id" content="...">
<meta property="og:locale" content="...">
<meta property="og:site_name" content="...">
<meta property="og:url" content="...">
<meta property="og:type" content="article">
<meta property="og:title" content="...">
<meta property="og:description" content="...">
<meta property="og:image" content="...">
<meta property="article:published_time" content="...">
<meta property="article:section" content="...">
<meta property="article:publisher" content="...">
```

Twitter Cards:
```html
<meta name="twitter:card" content="summary">
<meta name="twitter:site" content="...">
<meta name="twitter:title" content="...">
<meta name="twitter:description" content="...">
<meta name="twitter:image" content="..">
```

JSON-LD:
```html
<script type="application/ld+json">
{
	"@context": "http://schema.org",
	"@type": "DiscussionForumPosting",
	"@id": "...",
	"headline": "...",
	"description": "...",
	"image": "..."
}
</script>
```

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
