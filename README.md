# LeanTrans

LeanTrans is a small message translator for internationalization.

It supports Twig template engine, but it works without Twig.


## License

BSD 2-Clause License


## Installation

`
composer require "ranvis/leantrans:^1.0"
`


## Example Usage

### Translating messages in PHP code

```php
use Ranvis\LeanTrans;

require_once __DIR__ . '/vendor/autoload.php';

function getTranslator(): LeanTrans\Translator
{
    $msgs = [
        '' => [ // the default domain
            'ID_GREETING' => 'Ciao, {name}!',
        ],
    ];
    $provider = new LeanTrans\ArrayProvider($msgs);
    $formatter = new LeanTrans\MessageFormatter('it'); // target locale
    return new LeanTrans\Translator($provider, $formatter);
}

// define helper function
function __(string $msg, ?array $params = null): string
{
    static $translator;
    $translator ??= getTranslator();
    return $translator->translate($msg, $params);
}

// call it
echo __('ID_GREETING', ['name' => 'PHP']);
```

### Translating messages in Twig 3 template

```php
use Ranvis\LeanTrans;

require_once __DIR__ . '/vendor/autoload.php';

// set up Twig
$loader = new \Twig\Loader\ArrayLoader([
    'test' => <<<'END'
        <p>{{ "Hello, %name%!"|trans({name}) }}</p>
        <p>{{ "Hello, %name%!"|t({name}) }}</p>
        <p>{% trans with {name: name|e} %}Hello, %name%!{% endtrans %}</p>
        END,
]);
$twig = new \Twig\Environment($loader);

// set up a translator
$msgs = [
    '' => [ // the default domain
        'Hello, %name%!' => 'Ciao, %name%!',
    ],
];
$provider = new LeanTrans\ArrayProvider($msgs);
$formatter = new LeanTrans\VarFormatter();
$twig->addExtension(new LeanTrans\TwigTranslator($provider, $formatter));

// render
echo $twig->render('test', ['name' => 'PHP']);
```


## Documentation

The documentation is available at [USAGE.md](USAGE.md).
