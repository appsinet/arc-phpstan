Based on [material-foundation/arc-tslint](https://github.com/material-foundation/arc-tslint) and some improvements made in [sascha-egerer/arc-phpstan](https://github.com/sascha-egerer/arc-phpstan). Provides basic support for `arc lint` to execute `phpstan`.

# arc-phpstan

Use [phpstan](https://github.com/phpstan/phpstan) to lint your PHP source code with
[Phabricator](http://phabricator.org)'s `arc` command line tool.

## Features

phpstan generates warning messages.

Example output:
```
>>> Lint for src/AppBundle/Foo.php:


Error  () phpstan violation
Error: AppBundle\Foo::__construct() does not
call parent constructor from AppBundle\Bar.

          33      * constructor
          34      */
>>>       35     public function __construct()
          36     {
          37         Bar::__construct();
          38         $this->property = 0;
```
## Installation

phpstan is required. You can follow the [official instructions](https://github.com/phpstan/phpstan#installation) to install and put it on your $PATH, or you can run composer `install` and point the `bin` option to `vendor/bin/phpstan`, as in the example below.

### Project-specific installation

You can add this repository as a git submodule. Add a path to the submodule in your `.arcconfig`
like so:

```json
{
  "load": ["path/to/arc-phpstan"]
}
```

### Global installation

`arcanist` can load modules from an absolute path. But it also searches for modules in a directory
up one level from itself.

You can clone this repository to the same directory where `arcanist` and `libphutil` are located.
In the end it will look like this:

```sh
arcanist/
arc-phpstan/
libphutil/
```

Your `.arcconfig` would look like

```json
{
  "load": ["arc-phpstan"]
}
```

## Setup

To use the linter you must register it in your `.arclint` file, as in this example

```json
{
  "linters": {
    "phpstan": {
      "type": "phpstan",
      "include": "(\\.php$)",
      "config": "var/build/phpstan.neon", /* optional */
      "bin": "vendor/bin/phpstan", /* optional */
      "level": "0" /* optional */
    }
  }
}
```

## License

Licensed under the Apache 2.0 license. See LICENSE for details.
