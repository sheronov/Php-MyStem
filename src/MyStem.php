<?php


namespace Sheronov\PhpMyStem;


use Sheronov\PhpMyStem\Utils\System;

class MyStem
{
    /**
     * Лемма для первого слова
     *
     * @param  string  $word
     *
     * @return string|null
     */
    public static function lemma(string $word): ?string
    {
        if(!$result = System::runMyStem($word)) {
            return null;
        }

        $result = json_decode($result,true);
        return $result[0]['analysis'][0]['lex'] ?? null;
    }

    public static function stem(string $word):string
    {
        return System::runMyStem($word);
    }
}
