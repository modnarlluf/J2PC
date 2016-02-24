#  J2PC
J2PC (Json To PHP Class) can build PHP classes from JSON.
It is **ABSOLUTELY** not a complete nor secure solution.
And it's dirty. You wish your girlfriend was this dirty.

En' !

(yup, I lost the joy)
 
## Options
| Options   | Type   | Utility                         |
|-----------|:------:|---------------------------------|
| json      | string | String representing JSON        |
| file      | string | File containing JSON            |
| baseClass | string | Class name of the root class    |
| namespace | string | Namespace for generated classes | 
| output    | string | Existing path to folder output  |

## Usage
#### From JSON string
```sh
$ bin/j2pc generate --json="{\"foo\": \"bar\"}"
<?php

class BaseClass
{
    /**
     * @var string
     **/
     protected $foo;
}
```

#### From JSON file
```sh
$ bin/j2pc generate --file=tests/data/test.json
<?php

class User
{
    /**
     * @var string
     */
     protected $name;
}

<?php

class BaseClass
{
    /**
     * @var string
     */
     protected $foo;

    /**
     * @var User
     */
     protected $user;

    /**
     * @var boolean
     */
     protected $complete;
}

```

#### With output to ./out
```sh
$ bin/j2pc generate --json="{\"care\": false}" --output=./out
```
```php
<?php
// ./out/BaseClass.php

class BaseClass
{
    /**
     * @var boolean
     */
     protected $care;
}
```

#### With namespace
```sh
$ bin/j2pc generate --file=tests/data/test.json --output=./out --namespace="Foo\Model"
```

```php
<?php
// ./out/BaseClass.php
namespace Foo\Model;

class BaseClass
{
    /**
     * @var string
     */
     protected $foo;

    /**
     * @var Foo\Model\User
     */
     protected $user;

    /**
     * @var boolean
     */
     protected $complete;
}
```

```php
<?php
// ./out/User.php
namespace Foo\Model;

class User
{
    /**
     * @var string
     */
     protected $name;
}
```

#### With base class name provided
```sh
$ bin/j2pc generate --baseClass=User --json="{\"name\": \"John Doe\"}"
<?php

class User
{
    /**
     * @var string
     */
     protected $name;
}

``` 