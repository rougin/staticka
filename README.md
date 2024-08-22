# Staticka

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]][link-license]
[![Build Status][ico-build]][link-build]
[![Coverage Status][ico-coverage]][link-coverage]
[![Total Downloads][ico-downloads]][link-downloads]

A yet-another PHP-based static site generator. This converts [Markdown](https://en.wikipedia.org/wiki/Markdown) content and PHP template files into static HTML. It is inspired by popular static site generators like [Hugo](https://gohugo.io) and [Jekyll](https://jekyllrb.com).

## Installation

Install `Staticka` via [Composer](https://getcomposer.org/):

``` bash
$ composer require rougin/staticka
```

## Basic Usage

### Simple HTML from string

`Staticka` can convert simple Markdown content from string into HTML:

``` php
// index.php

use Rougin\Staticka\Page;
use Rougin\Staticka\Parser;

// Creates a new page with the specified body ---------
$page = new Page;

$page->setName('Hello world!');
$page->setBody("# {NAME}\nThis is a sample template.");
// ----------------------------------------------------

// Converts the page into an HTML ---
$parser = new Parser;

echo $parser->parsePage($page);
// ----------------------------------
```

``` bash
$ php index.php

<h1>Hello world!</h1>
<p>This is a sample template.</p>
```

> [!NOTE]
> The `{NAME}` is just a placeholder to insert the `name` value from the `Page` class to the body.

### Using `.md` files

`Staticka` also supports converting the Markdown-based files (`.md`files) by adding the path of the specified `.md` file to the `Page` class:

``` md
<!-- app/pages/hello-world.md -->

# Hello World!

This is a sample **Markdown** file!
```

``` php
// index.php

// ...

// Specify the path of the Markdown file -----
$file = __DIR__ . '/app/pages/hello-world.md';

$page = new Page($file);
// -------------------------------------------

// ...
```

``` bash
$ php index.php

<h1>Hello World!</h1>

<p>This is a sample <strong>Markdown</strong> file!</p>
```

### Adding Front Matter, additional data

`Staticka` supports [Front Matter](https://jekyllrb.com/docs/frontmatter) in which can add predefined variables in a specific content. Special variables like `path`, `link`, and `name` are also generated by default but can be overwritten inside the content.

``` md
<!-- app/pages/hello-world.md -->

---
link: hello-world
---

# Hello World!

The link is **{LINK}**.
```

``` bash
$ php index.php

<h1>Hello World!</h1>

<p>The link is <strong>hello-world</strong>.</p>
```

### Building HTML pages

Multiple `Page` instances can be converted into `.html` files with their respective directory names using the `Site` class:

``` php
// index.pp

// ...

use Rougin\Staticka\Site;

// ...

// Builds the site with its pages ------------
$site = new Site($parser);

$file = __DIR__ . '/app/pages/hello-world.md';
$site->addPage(new Page((string) $file));

$site->build(__DIR__ . '/app/web');
// -------------------------------------------
```

``` bash
$ php index.php
```

``` html
<!-- app/web/hello-world/index.html -->

<h1>Hello World!</h1>
<p>The link is <strong>hello-world</strong>.</p>
```

### Adding template engines

Building HTML pages from Markdown files only returns the content itself. By adding a third-party template engine, it makes it easier to add partials (e.g., layouts) or provide additional styling to each page. To add a template engine, a `Render` class must be used inside the `Parser` class:

``` md
<!-- app/pages/hello-world.md -->

---
name: Hello world!
link: hello-world
layout: main.php
---

# This is a hello world!

The link is **{LINK}**. And this is to get started...
```

> [!NOTE]
> The `layout` property specifies the layout file to be used when parsing the page. In this example, the `main.php` is the layout to be used in the `hello-world.md` file.

``` html
<!-- app/plates/main.php -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo $name; ?></title>
</head>
<body>
  <?php echo $html; ?>
</body>
</html>
```

> [!NOTE]
> The `$html` variable is a reserved variable for returning the content from the page that is parsed. The reserved variables is also based on the properties of a page with its data using the `$page->getData()` method.

``` php
// index.php

// ...

use Rougin\Staticka\Parser;
use Rougin\Staticka\Render;

// ...

// Sets the Render and Parser ----------
$paths = array(__DIR__ . '/app/plates');

$render = new Render($paths);

$parser = new Parser($render);
// -------------------------------------

// ...
```

> [!NOTE]
> To find the `main.php` file specified from the previous example, the `Render` class must be included the paths to be searched on.

``` bash
$ php index.php
```

``` html
<!-- app/web/hello-world/index.html -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Hello world!</title>
</head>
<body>
  <h1>This is a hello world!</h1>
<p>The link is <strong>hello-world</strong>. And this is to get started...</p></body>
</html>
```

## Changelog

Please see [CHANGELOG][link-changelog] for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Credits

- [All contributors][link-contributors]

## License

The MIT License (MIT). Please see [LICENSE][link-license] for more information.

[ico-build]: https://img.shields.io/github/actions/workflow/status/rougin/staticka/build.yml?style=flat-square
[ico-coverage]: https://img.shields.io/codecov/c/github/rougin/staticka?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/rougin/staticka.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-version]: https://img.shields.io/packagist/v/rougin/staticka.svg?style=flat-square

[link-build]: https://github.com/rougin/staticka/actions
[link-changelog]: https://github.com/rougin/staticka/blob/master/CHANGELOG.md
[link-contributors]: https://github.com/rougin/staticka/contributors
[link-coverage]: https://app.codecov.io/gh/rougin/staticka
[link-downloads]: https://packagist.org/packages/rougin/staticka
[link-license]: https://github.com/rougin/staticka/blob/master/LICENSE.md
[link-packagist]: https://packagist.org/packages/rougin/staticka