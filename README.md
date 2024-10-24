# Staticka

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]][link-license]
[![Build Status][ico-build]][link-build]
[![Coverage Status][ico-coverage]][link-coverage]
[![Total Downloads][ico-downloads]][link-downloads]

A yet-another PHP-based static site generator. This converts [Markdown](https://en.wikipedia.org/wiki/Markdown) content and PHP files into static HTML. It is inspired by popular static site generators like [Hugo](https://gohugo.io) and [Jekyll](https://jekyllrb.com).

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

// Creates a new page with the specified body -----
$page = new Page;

$page->setName('Hello world!');
$page->setBody("# {NAME}\nThis is a sample page.");
// ------------------------------------------------

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
$site->addPage(new Page($file));

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

The `Site` class can also empty a specified directory or copy a directory with its files. This is usable if the output directory needs CSS and JS files:

```
app/
├─ web/
│  ├─ index.html
styles/
├─ index.css
```

``` php
// ...

// Empty the "output" directory ---
$output = __DIR__ . '/app/web';
$site->emptyDir($output);
// --------------------------------

// Copy the "styles" directory to "output" ---
$styles = __DIR__ . '/styles';
$site->copyDir($styles, $output);
// -------------------------------------------

// ...
```

In specified scenarios, adding data that is applicable to all generated pages is possible in the same `Site` class:

``` php
// ...

$data = array('ga_code' => '12345678');
$data['website'] = 'https://roug.in';

$site->setData($data);

// ...
```

> [!WARNING]
> Adding data in the `Site` class that contains reserved property names for the `Page` class (e.g., `link`, `plate`, etc.) will override the data defined in a page.

### Adding template engines

Building HTML pages from Markdown files only returns the content itself. By adding a third-party template engine, it makes it easier to add partials (e.g., layouts) or provide additional styling to each page. To add a template engine, a `Render` class must be used inside the `Parser` class:

``` md
<!-- app/pages/hello-world.md -->

---
name: Hello world!
link: hello-world
plate: main.php
---

# This is a hello world!

The link is **{LINK}**. And this is to get started...
```

> [!NOTE]
> The `plate` property specifies the layout file to be used when parsing the page. In this example, the `main.php` is the layout to be used in the `hello-world.md` file.

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
> The `$html` variable is a predefined variable for returning the content from the page that is parsed. The predefined variables are also based on the properties of a page with its data using the `$page->getData()` method.

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

// Render may be added to Parser after ---
$parser->setRender($render);
// ---------------------------------------

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

To implement a custom template engine to `Staticka`, implement the said engine to the `RenderInterface`:

``` php
namespace Rougin\Staticka\Render;

interface RenderInterface
{
    /**
     * Renders a file from a specified template.
     *
     * @param string               $name
     * @param array<string, mixed> $data
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function render($name, $data = array());
}

```

### Setting layouts

In `Staticka`, a `Layout` class allows a page to use filters and helpers. It can also be passed as a `class-string` in the `.md` file:

``` php
// index.php

use Rougin\Staticka\Layout;

// ...

$pages = __DIR__ . '/app/pages';

// Define the layout with the name "main.php" ---
$layout = new Layout;

$layout->setName('main.php');
// ----------------------------------------------

// ...

// Set the layout into the page -------------
$page = new Page($pages . '/hello-world.md');

$site->addPage($page->setLayout($layout));
// ------------------------------------------
```

> [!NOTE]
> Using this approach, there is no need to specify the `plate` property from the specified `.md` file.

It is also possible to specify a class extended to `Layout` in the Front Matter details:

``` php
namespace App\Layouts;

use Rougin\Staticka\Layout;

class HomeLayout extends Layout
{
    /**
     * Specifies the plate to be used as the layout.
     *
     * @var string
     */
    protected $name = 'home.php';
}
```

> [!NOTE]
> The directory of the specified plate must be specified in the `Render` instance (e.g., `app/plates`).

``` md
<!-- app/pages/hello-world.md -->

---
name: Hello world!
link: hello-world
plate: App\Layouts\HomeLayout
---

# This is a hello world!

The link is **{LINK}**. And this is to get started...
```

## Extending and customization

### Modifying with filters

A `Filter` allows a page to be modified after being parsed:

``` php
// index.php

use Rougin\Staticka\Filter\HtmlMinifier;

// ...

// Set the layout class for "main.php" ---
$layout = new Layout;

$layout->setName('main.php');
// ---------------------------------------

// Minifies the HTML after parsing the page ---
$layout->addFilter(new HtmlMinifier)
// --------------------------------------------

// ...
```

``` bash
$ php index.php
```

``` html
<!-- app/web/hello-world/index.html -->

<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Hello world!</title></head><body><h1>This is a hello world!</h1><p>The link is <strong>hello-world</strong>. And this is to get started...</p></body></html>
```

To create a custom filter, implement it using the `FilterInterface`:

``` php
namespace Rougin\Staticka\Filter;

interface FilterInterface
{
    /**
     * Filters the specified code.
     *
     * @param string $code
     *
     * @return string
     */
    public function filter($code);
}

```

### Custom methods using helpers

A `Helper` provides additional methods inside template files:

``` php
// index.php

use Rougin\Staticka\Helper\LinkHelper;

// ...

// Set the layout class for "main.php" ---
$layout = new Layout;

$layout->setName('main.php');
// ---------------------------------------

// Add a "$url" variable in templates ---
$url = new LinkHelper('https://roug.in');

$layout->addHelper($url);
// --------------------------------------

// ...
```

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
  <a href="<?php echo $url->set($link); ?>"><?php echo $name; ?></a>
</body>
</html>
```

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
  <p>The link is <strong>hello-world</strong>. And this is to get started...</p>
  <a href="https://roug.in/hello-world">Hello world!</a>
</body>
</html>
```

To create a template helper, implement the said code in `HelperInterface`:

``` php
namespace Rougin\Staticka\Helper;

interface HelperInterface
{
    /**
     * Returns the name of the helper.
     *
     * @return string
     */
    public function name();
}
```

### Adding filters to Parser

By design, the filters under `FilterInterface` should be executed after parsing is completed by Parser. However, there may be scenarios that the body of a page must undergo a filter prior to its parsing process:

``` php
namespace App\Filters;

use Rougin\Staticka\Filter\FilterInterface;

class WorldFilter implements FilterInterface
{
    public function filter($code)
    {
        return str_replace('Hello', 'World', $code);
    }
}
```

``` php
// index.php

use App\Filters\WorldFilter;

// ...

// Replaces "Hello" string to "World" ---
$parser->addFilter(new WorldFilter);
// --------------------------------------

// ...
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