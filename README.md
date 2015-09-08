# Transphporm Style Sheets


[![Scrutinizer-CI](https://scrutinizer-ci.com/g/Level-2/Transphporm/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/TomBZombie/CDS/)

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/TomBZombie/CDS?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)


Transphporm is fresh look at templating in PHP. Let's face it, [Templating in PHP sucks](http://www.workingsoftware.com.au/page/Your_templating_engine_sucks_and_everything_you_have_ever_written_is_spaghetti_code_yes_you) it involves code like this:


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

Why does this suck? It mixes the logic with the template. There are processing instructions mixed inside the template, in the case of the middle example, it's just barely abstracted PHP code. Whoever is writing the template is also in charge of writing the display logic and understanding the data structures that have been supplied.

Template systems like this still mix logic and markup, the one thing they're trying to avoid.

This is equivalent to `<h1 style="font-weight:bold">Title</h1>`, it mixes two very different concerns. 

## Transphporm is different

### Project Goals

1. To completely separate the markup from the processing logic (No if statements or loops in the template!)
2. To follow CSS concepts and grammar as closely as possible. This makes it incredibly easy to learn for anyone who already understands CSS.
3. To be a lightweight library (Currently it's less than 500 lines and a total cyclomatic complexity (a count of if statements, functions and loops) of less than 100 for the entire project)


With Transphporm, the designer just supplies some raw XML that contains some dummy data. (Designers much prefer lorem ipsum to seeing `{{description}}` in their designs!)

```php
<ul>
	<li>User name</li>
</ul>

```

It's pure HTML without any processing instructions. Transphporm then takes the XML and renders it with some data.

But where are the processing instructions? Transphporm follows CSS's lead and all this processing logic is stored externally in "Transformation Style Sheets", a completely separate file that contains entirely reusable processing instructions.


At it's most basic, Transphporm works by suppling a stylesheet and XML as strings.

The stylesheet can supply content to a targetted element. For example, this stylesheet:


```php

h1 {content: "My Title";}

```

Will set the content of any `H1` Tag to "My Title". Given the following code:


```php

$xml = '<h1>Original Title</h1>';

$tss = 'h1 {content: "Replaced Title"; }';

$template = new \Transphporm\Builder($xml, $tss)

echo $template->output();

```

The output will be:


```php
<h1>Replaced Title</h1>
```


### Data

It's not usually possible to specify the content in a static file like a stylesheet. The `tss` format also allows referencing external data. This data is supplied using the template builder and can be referened in the stylesheet using the `data()` function. This can be though of like the `url()` function in CSS, in that it references an external resource.

```php

$xml = '<h1>Original Title</h1>';

$data = 'My Title!'

$tss = 'h1 {content: data(); }';


$template = new \Transphporm\Builder($xml, $tss, $data)

echo $template->output();

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

$template = new \Transphporm\Builder($xml, $tss, $data)

echo $template->output();


```

Which will output:


```php

<h1>My Title!</h1>
<p>Description of the page....</p>

```

### Content

The content property can take multiple values, either a function call such as `data` or a quoted string as each value and will concatenate any supplied values:

```php
$xml = '<h1>Original Title</h1>';

$data = 'My Title!'

$tss = 'h1 {content: "Title: ", data(); }';


$template = new \Transphporm\Builder($xml, $tss, $data)

echo $template->output();

```

Output:


```html
<h1>Title: My Title!</h1>
```

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


$template = new \Transphporm\Builder($xml, $tss, $data);

echo $template->output();

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


$template = new \Transphporm\Builder($xml, $tss, $data)

echo $template->output();

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


# Hiding Blocks

Lifted straight from css grammar, Transphporm supports `display: none` which will actually remove the element from the document entirely:

```php

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


$template = new \Transphporm\Builder($xml, $tss, $data)

echo $template->output();


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

N.b. this is very useful with the iteration value pseudo element

# CSS Selectors

Transphporm supports the following CSS selectors:

### `.classname`

```php

$xml = '
	<main>
		<p>Paragraph one</p>
		<p class="middle">Paragraph two</p>
		<p>Paragraph 3</p>
	</main>
';

$tss = '
.middle {content: "Middle paragraph"; }
';

$template = new \Transphporm\Builder($xml, $tss)

echo $template->output();

```

Output:

```php
	<main>
		<p>Paragraph one</p>
		<p class="middle">Middle Paragraph</p>
		<p>Paragraph 3</p>
	</main>
```



### `tagname.classname`


```php

$xml = '
	<main>
		<p>Paragraph one</p>
		<p class="middle">Paragraph two</p>
		<p>Paragraph 3</p>
		<a class="middle">A link</a>
	</main>
';

$tss = '
p.middle {content: "Middle paragraph"; }
';

$template = new \Transphporm\Builder($xml, $tss)

echo $template->output();

```

Output:


```php
	<main>
		<p>Paragraph one</p>
		<p class="middle">Middle Paragraph</p>
		<p>Paragraph 3</p>
		<a class="middle">A link</a>
	</main>
```



### Direct decedent `foo > bar`



```php

$xml = '
	<ul>
		<li>One</li>
		<li>Two
			<span>Test</span>
		</li>
		<li>Three
			<div>
				<span>Test 2 </span>
			</div>
		</li>
	</ul>

';

$tss = '
li > span {content: "REPLACED";}
';

$template = new \Transphporm\Builder($xml, $tss)

echo $template->output();

```

Output:

```html
	<ul>
		<li>One</li>
		<li>Two
			<span>REPLACED</span>
		</li>
		<li>Three
			<div>
				<span>Test 2 </span>
			</div>
		</li>
	</ul>
```


### ID selector `#name`

```php

$xml = '
	<main>
		<p>Paragraph one</p>
		<p id="middle">Paragraph two</p>
		<p>Paragraph 3</p>
	</main>
';

$tss = '
#middle {content: "Middle paragraph"; }
';

$template = new \Transphporm\Builder($xml, $tss)

echo $template->output();

```

Output:

```php
	<main>
		<p>Paragraph one</p>
		<p id="middle">Middle Paragraph</p>
		<p>Paragraph 3</p>
	</main>
```

### Attribute selector

Like CSS, you can select elements that have a specific attribute:


```php

$xml = '
	<textarea name="One">
	</textarea>

	<textarea name="Two">

	</textarea>

	<textarea>

	</textarea>
';

$tss = '
textarea[name="Two"] {content: "TEST"; }
';

$template = new \Transphporm\Builder($xml, $tss)

echo $template->output();

```

Output:

```php
	<textarea name="One">
	</textarea>

	<textarea name="Two">
	TEST
	</textarea>

	<textarea>

	</textarea>

```

Or, any element that has a specific attribute:

```php

$xml = '
	<textarea name="One">
	</textarea>

	<textarea name="Two">

	</textarea>

	<textarea>

	</textarea>
';

$tss = '
textarea[name] {content: "TEST"; }
';

$template = new \Transphporm\Builder($xml, $tss)

echo $template->output();

```

Output:

```php
	<textarea name="One">
	TEST
	</textarea>

	<textarea name="Two">
	TEST
	</textarea>

	<textarea>

	</textarea>
```



### Combining selectors

Like CSS, all the selectors can be combined into a more complex expression:


```php
table tr.list td[colspan="2"] {}
```

Will match any td with a colspan of 2 that is in a tr with a class `list` and inside a `table` element


## Unsupoorted selectors

Currently the CSS selectors `~` and `+` are not supported.


# Pseudo Elements

Transphporm also supports several pseudo elements.

`:before` and `:after`  which allows appending/prepending content to what is already there rather than overwriting it:

### Before

```php
$data = new stdclass;

$data->title = 'My Title!';
$data->description = 'Description of the page...';


$xml = '
	<h1>Example Title</h1>
	';

$tss = '
	h1:before {content: "BEFORE ";}
';

$template = new \Transphporm\Builder($xml, $tss, $data)

echo $template->output();

```

Output:


```
<h1>BEFORE Example Title</h1>

```

### After


```php
$data = new stdclass;

$data->title = 'My Title!';
$data->description = 'Description of the page...';


$xml = '
	<h1>Example Title</h1>
	';

$tss = '
	h1:after {content: " AFTER";}
';

$template = new \Transphporm\Builder($xml, $tss, $data)

echo $template->output();

```

Output:


```
<h1>Example Title AFTER</h1>

```




## :nth-child();

Straight from CSS, Transphporm also supports `nth-child(NUM)`. As well as `nth-child(odd)` and `nth-child(even)`


```php
$xml = '
		<ul>
			<li>One</li>
			<li>Two</li>
			<li>Three</li>
			<li>Four</li>
		</ul>
';

$tss = 'ul li:nth-child(2) {content: "REPLACED"}';

$template = new \Transphporm\Builder($template, $cds);
```

Output: 


```php
		<ul>
			<li>One</li>
			<li>REPLACED</li>
			<li>Three</li>
			<li>Four</li>
		</ul>

```


### Even


```php
$xml = '
		<ul>
			<li>One</li>
			<li>Two</li>
			<li>Three</li>
			<li>Four</li>
		</ul>
';

$tss = 'ul li:nth-child(even) {content: "REPLACED"}';

$template = new \Transphporm\Builder($template, $cds);
```

Output: 


```php
		<ul>
			<li>One</li>
			<li>REPLACED</li>
			<li>Three</li>
			<li>REPLACED</li>
		</ul>

```


### Odd

```php
$xml = '
		<ul>
			<li>One</li>
			<li>Two</li>
			<li>Three</li>
			<li>Four</li>
		</ul>
';

$tss = 'ul li:nth-child(even) {content: "REPLACED"}';

$template = new \Transphporm\Builder($template, $cds);
```

Output: 


```php
		<ul>
			<li>REPLACED</li>
			<li>Two</li>
			<li>REPLACED</li>
			<li>Four</li>
		</ul>

```


## Iteration values

Transphporm can also inspect the iterated data for an element. This is particularly useful when you want to hide a specific block based on the content of an iterated value:


The format is:

```php
element:iteration[name=value] {}
```

Which will select any element who's iteration content's `name` attribute is equal to `value`

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


$template = new \Transphporm\Builder($xml, $tss, $data)

echo $template->output();


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
		<a href="mailto:email">email</span>
	</li>	
</ul>';


$tss = '
	ul li {repeat: data(users);}
	ul li a {content: iteration(email);}
	ul li a:attr(href) {content: "mailto:", iteration(email);}
';

$data = ['users' => $users];


$template = new \Transphporm\Builder($xml, $tss, $data);

echo $template->output();


```

Notice this uses multiple values for the `content` property to concatenate the full URL with mailto

Output:


```php
<ul>
	<li>
		<h3>Tom</h3>
		<a href="Tom@example.org">Tom@example.org</span>
	</li>
	<li>
		<h3>Scott</h3>
		<a href="scott@example.org">scott@example.org</span>
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

echo $template->output();
```

Output:


```php
<h1 class="foo">foo</h1>

```