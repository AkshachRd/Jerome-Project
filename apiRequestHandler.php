<?php
require_once 'config.php';
require_once 'vendor/autoload.php';
use \Dejurin\GoogleTranslateForFree;

//Функция возвращает массив с информацией о слове
function getWordInfo(string $word): array
{
    $originalWord = $word;
    //Заменяю пробел на %20, чтобы запрос фраз проходил корректно
    $word = str_replace(' ', '%20', $word);

    //Lingua Robot API
    $response = getAPIAnswer($word);
    $response = json_decode($response, true);

    $entries = $response["entries"][0];
    //Если слово существует, то получить данные
    if (!empty($entries))
    {
        $originalWord[0] = strtoupper($originalWord[0]);
        $pronunciations = getPronunciations($entries);
        $definitionsByPartOfSpeech = getDefinitionsByPartOfSpeech($entries);
        $translation = getTranslation($entries["entry"]);
        $translation[0] = strtoupper($translation[0]);

        return [
            "wordIsCorrect" => true,
            "word" => $originalWord,
            "pronunciations" => $pronunciations,
            "definitionsByPartOfSpeech" => $definitionsByPartOfSpeech,
            "translation" => $translation
        ];
    }
    else
    {
        return [
            "wordIsCorrect" => false
        ];
    }
}

function getAPIAnswer(string $word): string
{
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://lingua-robot.p.rapidapi.com/language/v1/entries/en/$word",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: lingua-robot.p.rapidapi.com",
            "x-rapidapi-key: " . LINGUA_ROBOT_API_KEY
        ]
    ]);

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
}

//Функция возвращает перевод слова
function getTranslation(string $word): ?string
{
    $source = 'en';
    $target = 'ru';
    $attempts = 5;

    $tr = new GoogleTranslateForFree();
    $result = $tr->translate($source, $target, $word, $attempts);

    return $result;
}

//Функция получает транкрипции и аудио английского и американского произношений слова
function getPronunciations(array $entries): array
{
    $pronunciations = $entries["pronunciations"];

    foreach ($pronunciations as $pronunciation)
    {
        if ($pronunciation["context"]["regions"][0] === "United Kingdom")
        {
            $transcriptionUK = end($pronunciation["transcriptions"])["transcription"];
            if (isset($pronunciation["audio"]))
            {
                $audioUK = $pronunciation["audio"]["url"];
            }
        }
        elseif ($pronunciation["context"]["regions"][0] === "United States")
        {
            $transcriptionUS = end($pronunciation["transcriptions"])["transcription"];
            if (isset($pronunciation["audio"]))
            {
                $audioUS = $pronunciation["audio"]["url"];
            }

            break;
        }
    }

    return [
        "transcriptionUK" => $transcriptionUK,
        "transcriptionUS" => $transcriptionUS,
        "audioUK" => $audioUK,
        "audioUS" => $audioUS
    ];
}

//Функция получает массив с ключами в виде частей речи. С каждым ключем максимум 3 определения с примерами использавания
function getDefinitionsByPartOfSpeech(array $entries): array
{
    $lexemes = $entries["lexemes"];
    $definitionsByPartOfSpeech = [];

    foreach ($lexemes as $lexeme)
    {
        $partOfSpeech = $lexeme["partOfSpeech"];
        $definitions = getDefinitions($lexeme);

        $definitionsByPartOfSpeech["$partOfSpeech"] = $definitions;
    }

    return $definitionsByPartOfSpeech;
}

//Функция возвращает массив с 3 определениями с примерами использования
function getDefinitions(array $lexeme): array
{
    $senses = $lexeme["senses"];
    $definitions = [];
    $index = 0;

    foreach ($senses as $sense)
    {
        if ($index !== 3)
        {
            $definitions[$index] = [
                "definition" => $sense["definition"]
            ];
            if (!empty($sense["usageExamples"]))
            {
                $definitions[$index]["usageExample"] = $sense["usageExamples"][0];
            }

            $index++;
        }
    }

    return $definitions;
}