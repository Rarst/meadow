# Meadow — WordPress Templating DSL

Meadow is a theme templating solution, aiming to find a balance between native WordPress concepts and power of Twig dedicated templating language.

It is currently EXPERIMENTAL, treat it more like exploration and wishful thinking rather than hard promises.

## Installation

Require package in your theme project with Composer:

```bash
composer require rarst/meadow:dev-master --no-update
composer update --no-dev
```

Instantiate object some time during theme load:

```php
$meadow = new \Rarst\Meadow\Core;
$meadow->enable();
```

## Templating

Meadow follows conventions of WordPress template hierarchy:

 - for example `index.php` becomes `index.twig`.
 - `{{ get_header() }}` will look for `header.twig` (with fallback to `header.php`)
 - and so on.

Template Tags API (and PHP functions in general) are set up to work transparently from Twig templates:

```twig
{{ the_title() }}
```

WordPress filters set up to be available as Twig filters:

```twig
{{ 'This is the title'|the_title }}
```

Full range of Twig functionality is naturally available, including template inheritance:

```twig
{# single.twig #}
{% extends 'index.twig' %}

{% block entry_title %}
	<div class="page-header">{{ parent() }}</div>
{% endblock %}
```

## Domain Specific Language

Meadow attempts not just "map" WordPress to Twig, but also meaningfully extend both to improve historically clunky WP constructs.

For example Meadow's Loop (and in future more concepts) is implemented as custom tag:

```twig
{% loop %}
	<h2><a href="{{ the_permalink() }}">{{ the_title() }}</a></h2>
	{{ the_content() }}
{% endloop %}
```
