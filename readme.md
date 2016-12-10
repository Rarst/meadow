# Meadow — WordPress Templating DSL
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Rarst/meadow/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Rarst/meadow/?branch=master)

Meadow is a theme templating solution, aiming to find a balance between native WordPress concepts and power of [Twig](http://twig.sensiolabs.org/) dedicated templating language.

## Installation

Require package in your theme project with [Composer](https://getcomposer.org/):

```bash
composer require rarst/meadow
```

Instantiate object some time during theme load:

```php
$meadow = new \Rarst\Meadow\Core;
$meadow->enable();
```

## Templating

Meadow follows conventions of WordPress [template hierarchy](https://codex.wordpress.org/Template_Hierarchy#Visual_Overview):

 - for example `index.php` becomes `index.twig`.
 - `{{ get_header() }}` will look for `header.twig` (with fallback to `header.php`)
 - and so on.

### Template Tags

Template Tags API (and PHP functions in general) are set up to work transparently from Twig templates:

```twig
{{ the_title() }}
```

### Filters

WordPress filters set up to be available as Twig filters:

```twig
{{ 'This is the title'|the_title }}
```

### Template Inheritance

Full range of Twig functionality is naturally available, including [template inheritance](http://twig.sensiolabs.org/doc/templates.html#template-inheritance):

```twig
{# single.twig #}
{% extends 'index.twig' %}

{% block entry_title %}
	<div class="page-header">{{ parent() }}</div>
{% endblock %}
```

To inherit parent template in child theme prepend it with folder's name:

```twig
{# child-theme/index.twig #}
{% extends 'parent-theme/index.twig' %}
```

## Domain Specific Language

Meadow attempts not just "map" WordPress to Twig, but also meaningfully extend both to improve historically clunky WP constructs.

This is primarily achieved by implementing custom Twig tags, abstracting away complexities for specific tasks.

### Loop

```twig
{% loop %}
	<h2><a href="{{ the_permalink() }}">{{ the_title() }}</a></h2>
	{{ the_content() }}
{% endloop %}
```

### Secondary Loop

```twig
{% loop { 'post_type' : 'book', 'orderby' : 'title' } %} {# expression for arguments #}
	<h2><a href="{{ the_permalink() }}">{{ the_title() }}</a></h2>
	{{ the_content() }}
{% endloop %}
```

### Comments

```twig
<ul class="comment-list">
	{% comments %}
	<li>
		{{ comment_text() }}
	{# no </li> - self-closing #}
	{% endcomments %}
</ul>
```

## Template Examples

In [Hybrid Wing](https://github.com/Rarst/hybrid-wing) theme (work in progress):

 - [`index.twig`](https://github.com/Rarst/hybrid-wing/blob/master/index.twig)
  - [`single.twig`](https://github.com/Rarst/hybrid-wing/blob/master/single.twig)
   - [`single-post.twig`](https://github.com/Rarst/hybrid-wing/blob/master/single-post.twig)
  - [`comments.twig`](https://github.com/Rarst/hybrid-wing/blob/master/comments.twig)

## License

MIT