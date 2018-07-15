### About

SEO Metadata Extension for phpBB 3.2.x

[![Build Status](https://api.travis-ci.com/AlfredoRamos/phpbb-ext-seo-metadata.svg?branch=master)](https://travis-ci.com/AlfredoRamos/phpbb-ext-seo-metadata) [![Latest Stable Version](https://img.shields.io/github/tag/AlfredoRamos/phpbb-ext-seo-metadata.svg?label=stable&maxAge=3600)](https://github.com/AlfredoRamos/phpbb-ext-seo-metadata/releases) [![License](https://img.shields.io/github/license/AlfredoRamos/phpbb-ext-seo-metadata.svg)](https://raw.githubusercontent.com/AlfredoRamos/phpbb-ext-seo-metadata/master/license.txt)

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

Open Grah:
```html
<meta property="og:locale" content="...">
<meta property="og:site_name" content="...">
<meta property="og:title" content="...">
<meta property="og:description" content="...">
<meta property="og:type" content="website">
<meta property="og:url" content="...">
<meta property="og:image" content="...">
```

JSON+LD:
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
- Go back to `Manage extensions` > `Imgur` > `Delete data` and confirm

### Upgrade

- Uninstall the extension
- Delete all the files inside `{PHPBB_ROOT}/ext/alfredoramos/seometadata/`
- Download the new version
- Install the extension
