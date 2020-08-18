<?php


namespace Sheronov\PhpMyStem;


class MyStem
{
    public static function lemma(string $word):string
    {
        return 'Лемма: '.$word;
    }
}
