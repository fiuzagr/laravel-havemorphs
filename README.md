# Have Morphs trait for Laravel

This is a PHP trait to work with 
[Polymorphic Relations](https://laravel.com/docs/5.2/eloquent-relationships#polymorphic-relations)
more easily.

## Use

Put `HaveMorphs.php` file in your `\App` namespace and use in your model that
have polymorphic relations.

This trait provides a public method called `createOrUpdateMorphs` thats accept
one `$data` array argument. The method looks recursively in your model for a 
public array property called `$morphs`.

The format of `$data` argument is:

```php
$data['<your morph relation name>'] = [
  'id' => '...', // should exists if is an update

  // ...another morph data

  '<inherited morph relation name>' => [
    // ...inherited morph data
  ]
];
```


## Example

Model with morph relations:

```php
<?php

namespace App;

use App\HaveMorphs;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{

    use HaveMorphs;

    public $morphs = [
        'emails',
        'phones',
    ];

    public function emails()
    {
        return $this->morphMany('App\Email', 'owner');
    }

    public function phones()
    {
        return $this->morphMany('App\Phone', 'owner');
    }

}
```

Use in your controller:

```php
$person = new Person;
$person->name = 'Guilherme';
$person->createOrUpdateMorphs([
  'emails' => [
    ['id' => 1, 'email' => 'email@example.net'], // update
    ['email' => 'email@example.net'],            // create
  ],
  'phones' => [
    ['id' => 1, 'phone' => '999 999 999'], // update
    ['phone' => '999 999 999'],            // create
  ],
]);
```

