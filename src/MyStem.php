<?php


namespace Sheronov\PhpMyStem;


use Sheronov\PhpMyStem\Utils\System;

class MyStem
{
    /**
     * Процесс приведения словоформы к лемме — её нормальной (словарной) форме.
     *
     * @param  string  $word
     *
     * @return string|null
     * @throws Exceptions\MyStemException
     * @throws Exceptions\MyStemNotFoundException
     */
    public static function lemma(string $word): string
    {
        $result = static::run($word);
        return $result[0]['analysis'][0]['lex'] ?? '';
    }

    /**
     * Лемматизация всего входного текста
     *
     * @param  string  $text
     *
     * @return array
     * @throws Exceptions\MyStemException
     * @throws Exceptions\MyStemNotFoundException
     */
    public static function lemmatization(string $text): array
    {
        $output = [];
        $result = static::run($text, ['--weight', '-gid']);
        foreach ($result as $analyze) {
            $output[] = [
                'text'   => $analyze['text'],
                'lemma'  => $analyze['analysis'][0]['lex'],
                'weight' => $analyze['analysis'][0]['wt'],
                'gram'   => $analyze['analysis'][0]['gr'],
            ];
        }

        return $output;
    }

    /**
     * @param  string  $input
     * @param  array  $arguments
     *
     * @return array
     * @throws Exceptions\MyStemException
     * @throws Exceptions\MyStemNotFoundException
     */
    protected static function run(string $input, array $arguments = []): array
    {
        $output = System::runMyStem($input, array_unique(array_merge($arguments, ['--format=json'])));
        if (!empty($output)) {
            return json_decode($output, true);
        }

        return [];
    }
}
