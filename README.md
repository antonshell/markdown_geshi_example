# Markdown + Geshi example

There is example of usage markdown and geshi code highlighter for blog posts.

## Setup

1 . Clone repository

```
git clone ...
```

2 . Install dependencies 

```
composer install 
```

## Usage:

1 . Build posts

```
php build_posts.php
```

2 . See posts

```
http://127.0.0.1:8100/index.php?url=demo-markdown
http://127.0.0.1:8100/index.php?url=elastic-search-nginx-proxy
```

## How it works:

1 . You create post in markdown format. See ```posts``` folder

2 . Add code blocks with specific language. For example:

``` php
<?php

return [
    'vendor' => 'Company',
    'module_name' => 'Filters',
    'module_name_underscore' => 'DIY_Filters',
    'view_path' => 'filters',
    'entity' => 'Filter',
    'view_path_ucf' => 'Filters',
    'entity_lower' => 'filter',
    'setup_version' => '1.0.0',
    'table_name' => 'company_filter',
];
```

3 . Run console command.
 
```
php build_posts.php
``` 
 
Html files will be generated in ```build/posts``` folder

4 . Then you can show them in your blog pages

``` php
$urlPath = __DIR__ . '/build/posts/demo-markdown.html';
require $path;
```