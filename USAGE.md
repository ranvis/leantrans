# Documentation

## Introduction

LeanTrans is a small message translator for internationalization.

It supports Twig template engine, but it works without Twig.


LeanTrans consists of the three parts: Provider, Formatter and Translator.

To translate messages, you pass a translation dictionary to Provider, instantiate appropriate Formatter, and pass Translator the source message to translate.

### Provider

Provider accepts a message to be translated and returns translated string.
If it cannot find one, it will return the passed message itself.

The following Providers are bundled:

- `ArrayProvider(array $domains)`

  Accepts an array as translation dictionaries.

  ```php
  [
      'domain' => [ // empty string for the default domain
          'message' => 'translation',
          ...
      ],
      ...
  ]
  ```

- `PhpProvider(array $domains, ?string $dir = null, $defaultDomain = 'messages')`

  Accepts an array of PHP script file path as value.
  Or null value to look for `$dir/domain.php`.

  ```php
  [
      'domain' => '/path/to/script.php',
      'domain2' => null,
      ...
  ]
  ```

  Those PHP script should return an array of translation dictionary.

  ```php
  <?php return ['message' => 'translation', ...];
  ```

  Apparently this works performant with OPcache.

### Formatter

Formatter accepts a message string and parameters.
It parses a message and expands variables inside.

The use of formatter is optional, and you may omit it if the messages doesn't contain variables at all.

The following Formatters are bundled:

- `VarFormatter`

  Replaces `%var_name%` variables in a message with the named parameter.
  For escaping `%%` are replaced with `%`.

- `MessageFormatter(string $locale, bool $cache = true)`

  Format message using powerful [ICU MessageFormatter](https://unicode-org.github.io/icu/userguide/format_parse/messages/).
  Supports pluralization and number/date/time formatting.
  It has simple in-memory cache to skip message parsing when translating the same messages multiple times (in a loop for example.)

  Requires Intl extension.

### Translator

Translator coordinates Provider and (optional) Formatter, and actually translates messages.

The following Translators are bundled:

- `Translator`

  Plain translator.

- `TwigTranslator`

  Translator that also works as a Twig extension.

  It provides `trans`/`t` filter and `trans` tag for translation. 

## Message Format

There's of course no complex rule for messages to be translated.

Either write a message in a main language: `Hello, world!`,
or as a message ID: `MSG_HELLO_WORLD`.
You can mix up both ways.

Pros of writing in ID string is that you can translate otherwise the same source messages that map to different translations.
This sometimes happens when the two languages has no direct relationship:

```php
[
    'en' => [
        [
            '' => [
                'BE_DUPLICATE' => "Duplicate",
                'TO_DUPLICATE' => "Duplicate",
            ],
        ],
    ],
    'ja' => [
        [
            '' => [
                'BE_DUPLICATE' => "重複",
                'TO_DUPLICATE' => "複製",
            ],
        ],
    ],
]
```

Cons is that when no translation message is provided, `Provider` will return the original message itself. Meaning if you use ID and fail to update messages, ID can be displayed to the user.

To make translation performance wise, the supported encodings for the source message string that `Translator` accepts are ASCII compatible encodings such as UTF-8, 1-byte encodings like ISO-8859 families, etc.

`MessageFormatter` expects UTF-8 for messages.

### Domain

You can split up messages into multiple groups called domains.

Those who used to `gettext` have known domains already, but what LeanTrans differs from `gettext` domain is that a domain may be implanted in the message to be translated as follows.

`.domain_name.actual message`

Domain names may contain `/[0-9A-Za-z_-]/` characters.

If you have a message that looks like a domain, put an empty domain:

`...not_a_domain.`

(What else differs from `gettext` is that LeanTrans is context-free.
There are no global states for locale or domain. The caller controls everything.)

```php
[
    [
        '' => [
            'ID_HELLO_WORLD' => "Hello, world!",
        ],
        'admin' => [
            'ID_GREETING' => "Hello, admin!",
            // Reference this with '.admin.ID_GREETING'
        ],
    ],
]
```

Of course, you may instead instantiate a different `Provider` if those groups of messages are isolated.

## Translating messages in PHP code

You can translate messages inside code directly by calling `translate()` method.
Usually it is handy to define your translation function, like `_()` function being an alias of `gettext()`.

```php
echo __('Hardcoded message') . "\n";
echo __('MESSAGE_ID', ['param' => 123]) . "\n";

function __(string $msg, ?array $params = null): string
{
    static $translator;
    $translator ??= getTranslator();  // Get a Translator instance from your DI
    return $translator->translate($msg, $params);
}
```

## Translating messages in Twig templates

Integration with Twig is pretty simple.
First, add the extension to the Twig environment:

```php
$translatorExtension = new LeanTrans\TwigTranslator($provider, $formatter);
//$translatorExtension = new LeanTrans\TwigTranslator(getTranslator());  // if you already have a Translator instance
$twig->addExtension($translatorExtension);
```

And then call the filter/tag from the template:

```html
<p>{{ 'Sample message'|t }}</p>
<p>{{ 'MESSAGE_ID'|t({name: user.name}) }}</p>
{% trans %}<p>Message with tags</p>{% endtrans %}}
{% trans with {name: user.name|e} %}HTML_GREETING{% endtrans %}}
```

Be sure to escape unsafe variables when using `trans` tag.

As shown above, the parameters in translation message must be passed explicitly even if it is prominent in a message.
So, although it is not recommended, you may be tempted to pass the current context `_context` as an argument:

```html
<p>{{ '%count% item(s) in your cart.'|t(_context)' }}}</p>
```

```html
<p>{{ '{count, plural, one {# item is} other {# items are} } in your cart.'|t(_context) }}</p>
```

This works for `VarFormatter`.
But for `MessageFormatter`, because Intl extension will bind every element in `_context` to ICU MessageFormatter, it will complain if any of the elements cannot be stringified.
