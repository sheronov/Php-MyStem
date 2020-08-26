<?php


namespace Sheronov\PhpMyStem;


use Sheronov\PhpMyStem\Utils\System;

class MyStem
{
    public const UNDEFINED_PART_OF_SPEECH = '_undefined'; //неизвестная часть речи
    public const PARTS_OF_SPEECH          = [
        'A'      => 'adjective', // прилагательное
        'ADV'    => 'adverb', //наречие
        'ADVPRO' => 'pronominal_adverb', //местоименное наречие
        'ANUM'   => 'numeral_adjective', //числительное-прилагательное
        'APRO'   => 'adjective_pronoun', //местоимение-прилагательное
        'COM'    => 'part_of_composite', //часть композита - сложного сова
        'CONJ'   => 'conjunction', //союз
        'INTJ'   => 'interjection', // междометие
        'NUM'    => 'numeral', // числительное
        'PART'   => 'particle', // частица
        'PR'     => 'preposition',  //предлог
        'S'      => 'noun',// существительное
        'SPRO'   => 'pronoun_noun', // местоимение-существительное
        'V'      => 'verb', //глагол
    ];

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
        $result = static::run($text, ['--weight', '-gi']);
        foreach ($result as $analyze) {
            if (isset($analyze['analysis'][0]['lex'])) {
                $output[] = [
                    'text'        => $analyze['text'],
                    'lemma'       => $analyze['analysis'][0]['lex'],
                    'weight'      => $analyze['analysis'][0]['wt'] ?? 0,
                    'gram'        => $analyze['analysis'][0]['gr'] ?? '',
                    'part_letter' => static::partOfSpeech($analyze['analysis'][0]['gr'] ?? '', true) ?: null,
                    'part_more'   => static::partOfSpeech($analyze['analysis'][0]['gr'] ?? '', false),
                    'wrong'       => ($analyze['analysis'][0]['qual'] ?? null) === 'bastard'
                ];
            }
        }

        return $output;
    }

    /**
     * Запуск бинарника myStem с опцией возврата json формата
     *
     * @param  string  $input
     * @param  array  $arguments
     *
     * @return array
     * @throws Exceptions\MyStemException
     * @throws Exceptions\MyStemNotFoundException
     */
    public static function run(string $input, array $arguments = []): array
    {
        $output = System::runMyStem($input, array_unique(array_merge($arguments, ['--format=json'])));
        if (!empty($output)) {
            return json_decode($output, true);
        }

        return [];
    }

    public static function partOfSpeech(string $gram, bool $shortVariant = false): string
    {
        $shortPoS = explode(',', explode('=', $gram)[0])[0];

        return $shortVariant ? $shortPoS : (static::PARTS_OF_SPEECH[$shortPoS] ?? static::UNDEFINED_PART_OF_SPEECH);
    }
}
