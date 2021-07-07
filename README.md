# Php-MyStem

A PHP wrapper for Yandex MyStem program https://yandex.ru/dev/mystem/

## Installation 
1. Add the github repo:
```
composer config repositories.php-mystem vcs https://github.com/sheronov/php-mystem
```
2. Require the package
```
composer require sheronov/php-mystem
```
## Usage

**lemmatization** (Приведение словоформы к лемме — её нормальной (словарной) форме.)
```php
use Sheronov\PhpMyStem\MyStem;

MyStem::lemma('Бегущий'); //бежать

MyStem::lemmatization('Бегущий по лезвию'); /* array (
  0 =>
  array (
    'text' => 'Бегущий',
    'lemma' => 'бежать',
    'weight' => 1,
    'gram' => 'V,нп=(непрош,вин,ед,прич,полн,муж,несов,действ,неод|непрош,им,ед,прич,полн,муж,несов,действ)',
    'part_letter' => 'V',
    'part_more' => 'verb',
    'wrong' => false,
  ),
  1 =>
  array (
    'text' => 'по',
    'lemma' => 'по',
    'weight' => 1,
    'gram' => 'PR=',
    'part_letter' => 'PR',
    'part_more' => 'preposition',
    'wrong' => false,
  ),
  2 =>
  array (
    'text' => 'лезвию',
    'lemma' => 'лезвие',
    'weight' => 1,
    'gram' => 'S,сред,неод=дат,ед',
    'part_letter' => 'S',
    'part_more' => 'noun',
    'wrong' => false,
  ),
)
*/

```

**Raw binary run** with custom arguments from Yandex docs
```php
\Sheronov\PhpMyStem\MyStem::run($someText, ['--weight', '-gi']); // unprepared decoded array from json 
```

