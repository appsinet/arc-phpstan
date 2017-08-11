Based on [material-foundation/arc-tslint](https://github.com/material-foundation/arc-tslint). Provides basic support for `arc lint` to execute `phpstan`.

# arc-phpstan

Use [phpstan](https://github.com/phpstan/phpstan) to lint your PHP source code with
[Phabricator](http://phabricator.org)'s `arc` command line tool.

## Features

phpstan generates warning messages.

Example output:

    >>> Lint for src/index.ts:

     Warning  (quotemark) tslint violation
      ' should be "

                14  *  under the License.
                15  */
                16
      >>>       17 import $$observable from 'symbol-observable';
                18
                19 export default class IndefiniteObservable<T> implements Observable<T> {
                20   _creator: Creator;

     Warning  (semicolon) tslint violation
      Missing semicolon

                71 export type Unsubscribe = () => void;
                72 export type Subscription = {
                73   unsubscribe: Unsubscribe,
      >>>       74 }

## Installation

phpstan is required. Please follow [official instructions](https://github.com/phpstan/phpstan#installation) to install it.

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

To use the linter you must register it in your `.arclint` file.

```json
{
  "linters": {
    "phpstan": {
      "type": "phpstan",
      "include": "(\\.php$)"
    }
  }
}
```

## License

Licensed under the Apache 2.0 license. See LICENSE for details.
