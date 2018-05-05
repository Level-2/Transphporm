# Transphporm Style Sheets


[![Scrutinizer-CI](https://scrutinizer-ci.com/g/Level-2/Transphporm/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Level-2/Transphporm/)

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/TomBZombie/CDS?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)


Transphporm is a fresh approach to templating in PHP. Let's face it, [templating in PHP sucks](http://www.workingsoftware.com.au/page/Your_templating_engine_sucks_and_everything_you_have_ever_written_is_spaghetti_code_yes_you) because it involves code like this:


```php
<ul>
<?php foreach ($users as $user) : ?>
	<li><?= $user->name; ?></li>
<?php endforeach; ?>
</ul>
```


Or some variation of this mess:

```php

<ul>
{% for user in users %}
	<li>{{ user.username|e }}</li>
{% endfor %}
</ul>

```
or

```php

<ul>
	{users}
		<li>{user.name}</li>
	{/users}
</ul>

```

Why does this suck? It mixes the logic with the template. There are processing instructions mixed inside the template. In the case of the middle example, it's just barely abstracted PHP code. Whoever is writing the template is also in charge of writing the display logic and understanding the data structures that have been supplied.

Template systems like this still mix logic and markup, the one thing they're trying to avoid.

This is equivalent to `<h1 style="font-weight:bold">Title</h1>`,  as it mixes two very different concerns.

## Transphporm is different

### Project Goals

1. To completely separate the markup from the processing logic. (No `if` statements or loops in the template!)
2. To follow CSS concepts and grammar as closely as possible. (This makes it incredibly easy to learn for anyone who already understands CSS.)


With Transphporm, the designer just supplies some raw HTML or XML that contains some dummy data. (Designers much prefer lorem ipsum to seeing `{{description}}` in their designs!)

```php
<ul>
	<li>User name</li>
</ul>

```

It's pure markup without any processing instructions. Transphporm then takes the markup and replaces the dummy data with the real data you want.

But where are the processing instructions? Transphporm follows CSS's lead.  All of the processing logic is stored externally in "Transformation Style Sheets", a completely separate file that contains entirely reusable processing instructions.


At its most basic, Transphporm works by supplying a stylesheet and HTML/XML as strings.

Transphporm allows you to insert content into any element on a page. Traditional template engines force you to place markers in the markup which will then be replaced (essentially using `str_replace`) within the content.

Transphporm takes a different approach and allows you to insert content using a CSS-like syntax. You don't need to provide special markers in the template; the template is plain old HTML without any special syntax. The elements on the page can then be targeted using CSS-style selectors.


 For example, this stylesheet:


```php

h1 {content: "My Title";}

```

will set the content of any `<h1>` Tag to "My Title". Given the following code:


```php

$xml = '<h1>Original Title</h1>';

$tss = 'h1 {content: "Replaced Title"; }';

$template = new \Transphporm\Builder($xml, $tss);

echo $template->output()->body;

```

The output will be:


```php
<h1>Replaced Title</h1>
```

The arguments for Transphporm\Builder can either be HTML/XML and *TSS* strings, or file names to load.

```php
//Load files instead of strings, the base path is the current working directory (getcwd())
$template = new \Transphporm\Builder('template.xml', 'stylesheet.tss');


```

This allows an unprecedented level of flexibility. Rather than having to consider which parts of the content may be dynamic and adding things like `{{user.name}}` in the template at the correct position, these concerns can be ignored when designing the template and some static content inserted in its place. Transphporm can then replace any content on the page. This allows you to-reuse a template. Sometimes you might replace some content, other times you might use the default from the template!

# 5 Reasons to use Transphporm

1. **[Write content to any element](https://github.com/Level-2/Transphporm/wiki/Basic-Usage:-Inserting-content)**. With traditional template engines the designer needs to place markers in the template, e.g. `{{name}}`, everywhere that content needs to be injected into the template. With Transphporm, the designer doesn't need to worry about whether specific content will be replaced (effectively with `str_replace`). Instead Transphporm allows the developer to write content to any HTML element on the page, and the designer to focus on design rather than worrying about what content might be added.

2. **[Anything can be a partial](https://github.com/Level-2/Transphporm/wiki/Template-Partials)**. Traditional template engines force you to put each partial in its own file. This is bad for the designer because they cannot quickly an easily see how the partial looks inside the complete layout. With Transphporm, the designer can work with complete HTML files and the developer can extract any element from any file as a partial.

3. **Resuable display logic**. Because display logic is placed in its own external file, you can use the same display logic with as many XML files as you like. This is the difference between external CSS files and `style=` attributes inside your HTML.

4. **Render your template on the client or the server**. The TSS format is not barely-abstracted PHP code like traditional template engines; it's a custom format with no reliance on PHP. Because of this, you can take an XML file and a TSS file and render it on the server using [Transphporm](https://github.com/Level-2/Transphporm) or inside the browser using the JavaScript implementation [Tranjsform](https://github.com/Level-2/Tranjsform).

5. **If you've used CSS, Transphporm is easy to learn**. Transphporm closely follows CSS syntax and uses some of the same vocabulary. If you have even a basic understanding of CSS, you'll be able to learn to use Transphporm with ease!

Transphporm gives both designers and developers an unprecedented level of flexibility that just isn't possible with traditional template engines.

# Installation

The preferred method of installing Transphporm is via Composer. Transphporm is available from Packagist as:

	level-2/transphporm

However, if you don't want to use Composer you can manually install Transphporm:

1. Download and extract Transphporm into your project
2. Use a PSR-0 compliant autoloader such as [Axel](https://github.com/Level-2/Axel)
3. Register Transphporm's `src` directory with the autoloader. Using Axel this is done via:

```php
require_once 'axel/axel.php';
$axel = new \Axel\Axel;
$axel->addModule(new \Axel\Module\PSR0('./path/to/Transphporm/src', '\\Transphporm'));
```



### Data

It's not usually possible to specify the content in a static file like a stylesheet. The `tss` format also allows referencing external data. This data is supplied to the template builder's `output` method and can be referenced in the stylesheet using the `data()` function. This can be thought of like the `url()` function in CSS, in that it references an external resource.

```php

$xml = '<h1>Original Title</h1>';

$data = 'My Title!';

$tss = 'h1 {content: data(); }';


$template = new \Transphporm\Builder($xml, $tss);

echo $template->output($data)->body;

```

Output:


```php
<h1>My Title!</h1>
```

Most of the time, you will need to work with much more complex data structures. Transphporm allows for reading data from within data structures using the inbuilt data function:


```php
$data = new stdclass;

$data->title = 'My Title!';
$data->description = 'Description of the page...';


$xml = '
	<h1>Example Title</h1>
	<p>Example Description</p>
	';

$tss = '
	h1 {content: data(title);}
	p {content: data(description);}
';

$template = new \Transphporm\Builder($xml, $tss);

echo $template->output($data)->body;


```

Which will output:


```php

<h1>My Title!</h1>
<p>Description of the page....</p>

```

### Content

The content property can take multiple values, either a function call such as `data` or a quoted string as each value, and will concatenate any supplied values:

```php
$xml = '<h1>Original Title</h1>';

$data = 'My Title!'

$tss = 'h1 {content: "Title: ", data(); }';


$template = new \Transphporm\Builder($xml, $tss);

echo $template->output($data)->body;

```

Output:


```html
<h1>Title: My Title!</h1>
```

For more information on inserting content see the wiki pages [Basic usage: Inserting Content](https://github.com/Level-2/Transphporm/wiki/Basic-Usage:-Inserting-content) and [Basic Usage: External Data](https://github.com/Level-2/Transphporm/wiki/Basic-Usage:-Working-with-external-data)

### Loops

Going back to the user list example, consider the following data structure:

```php
$users = [];

$user = new stdclass;
$user->name = 'Tom';
$user->email = 'tom@example.org';

$users[] = $user;


$user = new stdclass;
$user->name = 'Scott';
$user->email = 'scott@example.org';

$users[] = $user;

```

Using Transphporm, the user list can be generated like this:


```php

$xml = '<ul>
	<li>Name</li>
</ul>';


$tss = '
	ul li {repeat: data(users); content: iteration(name);}
';

$data = ['users' => $users];


$template = new \Transphporm\Builder($xml, $tss);

echo $template->output($data)->body;

```


`repeat` tells Transphporm to repeat the selected element for each of the supplied array.

`data(users)` reads `$data['users']` as supplied in PHP.

`iteration(name)` points at the value for the current iteration and reads the `name` property. This code outputs:


```php
<ul>
	<li>Tom</li>
	<li>Scott</li>
</ul>
```


Similarly, `iteration` can read specific values and be used in nested nodes:

```php
$xml = '<ul>
	<li>
		<h3>Name</h3>
		<span>email</span>
	</li>
</ul>';


$tss = '
	ul li {repeat: data(users);}
	ul li h3 {content: iteration(name);}
	ul li span {content: iteration(email);}
';

$data = ['users' => $users];


$template = new \Transphporm\Builder($xml, $tss);

echo $template->output($data)->body;

```

Which will output:


```php
<ul>
	<li>
		<h3>Tom</h3>
		<span>tom@example.org</span>
	</li>
	<li>
		<h3>Scott</h3>
		<span>scott@example.org</span>
	</li>
</ul>

```

For more information on loops see the [Wiki page Basic usage: Loops](https://github.com/Level-2/Transphporm/wiki/Basic-Usage:-Loops).

# Removing Blocks

Lifted straight from CSS grammar, Transphporm supports `display: none` which will actually remove the element from the document entirely:


```php

$xml = '<ul>
	<li>
		<h3>Name</h3>
		<span>email</span>
	</li>
</ul>';


$tss = '
	ul li {repeat: data(users);}
	ul li h3 {content: iteration(name);}
	ul li span {display: none}
';

$data = ['users' => $users];


$template = new \Transphporm\Builder($xml, $tss);

echo $template->output($data)->body;


```

Output:


```php

<ul>
	<li>
		<h3>Tom</h3>
	</li>
	<li>
		<h3>Scott</h3>
	</li>
</ul>

```

N.b. this is very useful with the iteration value pseudo element. For more examples of conditonal logic see the [Wiki page on Basic usage: Conditional logic](https://github.com/Level-2/Transphporm/wiki/Basic-Usage:-Conditional-Logic).

# CSS Selectors

Transphporm supports the following CSS selectors:

`tagName`
`#id`
`.className`
`tagName.className`
`direct > descendant`
`[attribute]`
`[attribute=value]`
`[attribute!=value]`

And any of these can be chained:

`main .post > .author[data-admin=true]` will match  any element with the class name `author` which has the `data-admin` attribute set to true and is directly inside an element with the class name `post` that is inside the `<main>` element.

For a full list of supported selectors and example of each one, see the [Wiki page on Basic Usage: CSS Selectors](https://github.com/Level-2/Transphporm/wiki/Basic-Usage:-CSS-Selectors).


# Pseudo Elements

Transphporm also supports several pseudo elements:

`:before` and `:after` which allows writing content to the beginning or end of a node.

`:nth-child(n)`
`:nth-child(odd)`
`:nth-child(even)`

For examples of each of these, please see the [Wiki page Basic Usage: Pseudo Elements](https://github.com/Level-2/Transphporm/wiki/Basic-Usage:-Pseudo-elements).

## Iteration values

Transphporm can also inspect the iterated data for an element. This is particularly useful when you want to hide a specific block based on the content of an iterated value:


The format is:

```php
element:iteration[name=value] {}
```

This will select any element whose iteration content's `name` attribute is equal to `value`.

The following code will hide any user whose type is 'Admin'.

```php
$users = [];

$user = new stdclass;
$user->name = 'Tom';
$user->email = 'tom@example.org';
$user->type = 'Admin';
$users[] = $user;


$user = new stdclass;
$user->name = 'Scott';
$user->email = 'scott@example.org';
$user->type = 'Standard';
$users[] = $user;

$user = new stdclass;
$user->name = 'Jo';
$user->email = 'jo@example.org';
$user->type = 'Standard';
$users[] = $user;



$xml = '
<ul>
	<li>
		<h3>Name</h3>
		<span>email</span>
	</li>
</ul>';


$tss = '
	ul li {repeat: data(users);}
	ul li:iteration[type='Admin'] {display: none;}
	ul li h3 {content: iteration(name);}
	ul li span {content: iteration(email);}
';

$data = ['users' => $users];


$template = new \Transphporm\Builder($xml, $tss);

echo $template->output($data)->body;


```

Output:


```php
<ul>
	<li>
		<h3>Scott</h3>
		<span>scott@example.org</span>
	</li>
	<li>
		<h3>Jo</h3>
		<span>jo@example.org</span>
	</li>
</ul>
```


## Writing to Attributes

Unlike CSS, Transphporm selectors allow direct selection of individual attributes to set their value. This is done using the pseudo element `:attr(name)` which selects the attribute on the matched elements.

```
element:attr(id)
```

Will select the element's ID attribute.

Working example:


```php
$users = [];

$user = new stdclass;
$user->name = 'Tom';
$user->email = 'tom@example.org';
$users[] = $user;


$user = new stdclass;
$user->name = 'Scott';
$user->email = 'scott@example.org';
$users[] = $user;



$xml = '
<ul>
	<li>
		<h3>Name</h3>
		<a href="mailto:email">email</a>
	</li>
</ul>';


$tss = '
	ul li {repeat: data(users);}
	ul li a {content: iteration(email);}
	ul li a:attr(href) {content: "mailto:", iteration(email);}
';

$data = ['users' => $users];


$template = new \Transphporm\Builder($xml, $tss);

echo $template->output($data)->body;



```

Notice this uses multiple values for the `content` property to concatenate the full URL with `mailto`.

Output:


```php
<ul>
	<li>
		<h3>Tom</h3>
		<a href="mailto:Tom@example.org">Tom@example.org</a>
	</li>
	<li>
		<h3>Scott</h3>
		<a href="mailto:scott@example.org">scott@example.org</a>
	</li>
</ul>
```




## Reading from attributes

It's also possible to read from attributes using `attr(name)` inside the content property.


```php
$xml = '
<h1 class="foo">bar</h1>
';

$tss = 'h1 {content: attr(class);}';

$template = new \Transphporm\Builder($xml, $tss);

echo $template->output()->body;
```

Output:


```php
<h1 class="foo">foo</h1>

```


## HTTP Headers

Transphporm supports setting HTTP Headers. You must target an element on the page such as HTML and you can use the `:header` pseudo element to set a HTTP header. For example a redirect can be done like this:

```css

html:header[location] {content: "/redirect-url"; }

```

Transphporm does not directly write HTTP headers. The return value of the `output()` function is an array consisting of a `body` and `headers`. `body` is the rendered HTML code and `headers` contains any HTTP headers which have been set.

```php

$xml = '<html><div>Foo</div></html>';
$tss = 'html:header[location] {content: "/redirect-url"; }';

$template = new \Transphporm\Builder($xml, $tss);

print_r($template->output());
```

Will print:

```php
Array (
	'body' => '<html><div>foo</div></html>',
	'headers' => Array (
		Array (
			[0] => 'location',
			[1] => '/redirect-url'
		)
	)
)
```

To actually send the headers to the browser you need to manually call the header command:


```php

foreach ($template->output()->headers as $header) {
	header($header[0] . ': ' . $header[1]);
}

```


### Conditionally applying HTTP headers

In most cases, you will want to conditionally display a header. For example:

- Redirect on success.
- Send a 404 header when a record could not be found.


To do this, you can use conditional data lookups:


```php

class Model {
	public function getProduct() {
		return false;
	}
}



$tss = 'html:data[getProduct='']:header[status] {content: '404'}

$xml = '<html></html>';

$data = new Model;

$template = new \Transphporm\Builder($xml, $tss);

$output = $template->output($data);

print_r($output->headers)

```

Prints:

```php
Array (
	[0] => 'status',
	[1] => '404'
)

```

To use this, you should then call the inbuilt php `http_response_code` function with this status:


```php
foreach ($template->output()->headers as $header) {
	if ($header[0] === 'status') http_response_code($header[1]);
	else header($header[0] . ': ' . $header[1]);
}
```


### Transphporm does not send any headers

Transphporm does not send any output to the browser by default. This is for maximum flexibility. You still must manually send the headers and echo the body.



## Formatting data

Transphporm supports formatting of data as its output. The syntax for formatting is this:


```css

h1 {content: "content of element"; format: [NAME-OF-FORMAT] [OPTIONAL ARGUMENT OF FORMAT];}
```

### String formatting

Transphporm currently supports the following formats for strings:

- uppercase
- lowercase
- titlecase

Examples:


### String format: uppercase

```php
$xml = '
<h1> </h1>
';

$tss = 'h1 {content: "TeSt"; format: uppercase}';

$template = new \Transphporm\Builder($xml, $tss);

echo $template->output()->body;
```

Prints:


```html
<h1>TEST</h1>

```



### String format: lowercase

```php
$xml = '
<h1> </h1>
';

$tss = 'h1 {content: "TeSt"; format: lowercase}';

$template = new \Transphporm\Builder($xml, $tss);

echo $template->output()->body;
```

Prints:


```html
<h1>test</h1>

```



### String format: titlecase

```php
$xml = '
<h1> </h1>
';

$tss = 'h1 {content: "test"; format: titlecase}';

$template = new \Transphporm\Builder($xml, $tss);

echo $template->output()->body;
```

Prints:


```html
<h1>Test</h1>

```


## Number formats

Transphporm supports formatting numbers to a number of decimal places using the `decimal` format. You can specify the number of decimal places:


### Number format: decimal


```php
$xml = '
<h1> </h1>
';

$tss = 'h1 {content: "11.234567"; format: decimal 2}';

$template = new \Transphporm\Builder($xml, $tss);

echo $template->output()->body;
```

Prints:


```html
<h1>1.23</h1>

```


## Locales


For date, time and currency formatting, Transphporm supports locales. Currently only enGB is supplied but you can write your own.

To set a locale, use the `builder::setLocale` method. This takes either a locale name, for a locale inside `Formatter/Locale/{name}.json` e.g.

```php

$template = new \Transphporm\Builder($xml, $tss);
$template->setLocale('enGB');

```

Currently only enGB is supported. Alternatively, you can provide an array which matches the format used in `Formatter/Locale/enGB.json`.


### Date formats

Transphporm supports formatting dates. Either you can reference a `\DateTime` object or a string. Strings will be converted to dates automatically, if possible:

```php
$xml = '
<div> </div>
';

$tss = 'div {content: "2015-12-22"; format: date}';

$template = new \Transphporm\Builder($xml, $tss);

echo $template->output()->body;
```


This will format the date using the date format specified in the locale. For enGB this is `d/m/Y`

```html
<div>22/12/2015</div>
```

Alternatively you can specify a format as the second parameter of the formatter:


```php
$xml = '
<div> </div>
';

$tss = 'div {content: "2015-12-22"; format: date "jS M Y"}';

$template = new \Transphporm\Builder($xml, $tss);

echo $template->output()->body;
```


```html
<div>22nd Dec 2015</div>
```


You can also format using `time` which defaults to `H:i` in the locale:


```php
$xml = '
<div> </div>
';

$tss = 'div {content: "2015-12-22 14:34"; format: time}';

$template = new \Transphporm\Builder($xml, $tss);

echo $template->output()->body;
```


```html
<div>14:34</div>
```

## Relative times

You can supply the `relative` formatter to a date, which will display things like:

- "Tomorrow"
- "Yesterday"
- "Two hours ago"
- "3 weeks ago"
- "In 3 months"
- "In 10 years"

The strings are specified in the locale.


## Importing other files

Like CSS, Transphporm supports `@import` for importing other TSS files:


`imported.tss`

```css
h1 {content: "From imported tss"}
```


```php
$xml = '
<h1> </h1>
<div> </div>
';

$tss = "
	@import 'imported.tss';
	div {content: 'From main tss'}
";

$template = new \Transphporm\Builder($xml, $tss);

echo $template->output()->body;
```

Output:

```html
<h1>From imported tss</h1>
<div>From main tss</div>

```

# Caching

Transphporm has two types of caching, both of which need to be enabled:

1. Caching TSS and XML files. This prevents them from being parsed each time the template is rendered. It is worthwhile to enable this even if you do not intend on using `update-frequency` (see below).

2. `update-frequency` This is a property which allows you to update an element at a specified interval.

To enable caching, you must create (or use) a caching class that implements PHP's inbuilt `\ArrayAccess` interface, for example [Level-2/SimpleCache](https://github.com/Level-2/SimpleCache).  Then assign an instance to the builder:

```php
$cache = new \SimpleCache\SimpleCache('/tmp');

$template = new \Transphporm\Builder($xml, $tss);
$template->setCache($cache);

echo $template->output($data)->body;
```

Doing this will automatically enable file-caching. Once a cache has been assigned, TSS files will only be parsed whenever they are updated. This saves parsing the TSS file each time your page loads and is worthwhile even if you are not using `update-frequency`.


### update-frequency

`update-frequency` is a TSS directive that describes how frequently a given TSS rule should run. Behind the scenes, Transphporm will save the final output each time a template is rendered and make changes to it based on `update-frequency`. For example:

```tss
ul li {repeat: data(users); update-frequency: 10m}
```

This will only run the TSS rule every 10 minutes. The way this works behind the scenes is:

- The rendered template is stored in the cache.
- Next time the page loads, the previously rendered template is loaded.
- If the timer has expired, the repeat/content/etc. directives are run again on the cached version of the template and the template is updated.

This allows different parts of the page to be updated at different frequencies.

## Caching in MVC

If you are using real MVC ([not PAC, which most frameworks actually use](http://r.je/views-are-not-templates.html)) and you are passing your model into your view, if your model is passed in as the `data` argument and has a `getUsers` function, Transphporm can call this and only execute the query when the template is updated.

```tss
ul li {repeat: data(getUsers); update-frequency: 10m}
```

Most frameworks do not pass models into views, however for those that do this allows a two-level cache. The query is only run when the view is updated based on the view's timeout.


# Building a whole page

Transphporm uses a top-down approach to construct pages. Most frameworks require writing a layout template and then pulling content into it. This makes it very difficult to make changes to the layout on a per-page basis. (At minimum you'd need to add some code to the layout HTML). Transphporm uses a top-down approach rather than the popular bottom-up approach where the child template is inserted into the layout at a specific point.

You still have two files, one for the layout and one for the content, but the TSS is applied to the *layout* which means the TSS can change anything in the layout you want (adding script tags, adding CSS, changing the page title and meta tags, etc).

`layout.xml`

```html
<!DOCTYPE HTML>
<html>
	<head>
		<title>My Website</title>
	</head>
	<body>
		<header>
			<img src="logo.png" />
		</header>

		<nav>
			<ul>
				<li><a href="/">Home</a></li>
				<li><a href="about.html">About</a></li>
				<li><a href="contact.html">Contact</a></li>
			</ul>
		</nav>

		<main>
			Main content

		</main>

		<footer>
			Copyright <span>year</span>
		</footer>
	</body>
</html>
```

And then `home.xml`:

```html
<?xml version="1.0"?>
<template>
	<p>Welcome to my website</p>
</template>

```

The TSS file can then be used to include one inside another:

`home.tss`

```css

title {content: "My Site"}

main {content: template("home.xml")}

footer span {content: "now"; format: date "Y"}

```

Which will then set the content of the `<main>` element to the content of the template stored in `home.xml` using the following code:


```php
$template = new \Transphporm\Builder('layout.xml', 'home.tss');

echo $template->output()->body;

```

Obviously you could then add an about page by adding the relevant `about.xml` and then a TSS:

```css

title {content: "About me"}

main {content: template("about.xml")}

footer span {content: "now"; format: date "Y"}

```


```php
$template = new \Transphporm\Builder('layout.xml', 'about.tss');

echo $template->output()->body;

```

There's a little repetition here which can be solved in two ways.

### 1) Put the layout rules in their own file, e.g. base.tss:

```css

footer span {content: "now"; format: date "Y"}


```


And then import it in `about.tss` and `home.tss` e.g.

```css

@import "base.tss";

title {content: "About me"}

main {content: template("about.xml")}

```


### 2) Use data to describe the relevant parts externally:

`page.tss`

```css
title {content: data(title);}

main {content: template(data(page));}

footer span {content: "now"; format: date "Y"}

```


```php

//Home template:

$template = new \Transphporm\Builder('layout.xml', 'page.tss');

$template->output(['title' => 'My Website', 'page' => 'home.xml'])->body;



//About template:
$template = new \Transphporm\Builder('layout.xml', 'page.tss');

$template->output(['title' => 'About Me', 'page' => 'about.xml'])->body;

```


This allows a top down approach. Most frameworks work on a bottom up approach where you build the layout, then build the content and put the output of one in the other. This presents a problem: How do you set the title per page? Or perhaps include a different sidebar on each page? Frameworks tend to do this using what are essentially global variables to store the page title and any layout options. TSS builds the entire page in one go, so any page can alter any part of the layout.


# Credits

Transphporm was originally developed by Tom Butler ( @TomBZombie ) with additional feature implementation, bugfixes and suggestions by Richard Sollee ( @solleer )


