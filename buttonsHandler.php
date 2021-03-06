<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                                              /* Keyboard кнопка */
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function learnWords(object $telegram, mysqli $link, int $chatId): void
{
    $sql = 'SELECT MAX(word_num) FROM word_list WHERE chat_id = ' . $chatId;
    $sqlResult = mysqli_query($link, $sql);
    $maxWordNum = (int)mysqli_fetch_array($sqlResult)["MAX(word_num)"];

    if (!empty($maxWordNum))
    {
        $whichWordsToLearn = "";

        for ($wordNum = 1; $wordNum <= $maxWordNum; $wordNum++)
        {
            $whichWordsToLearn .= "$wordNum";
        }

        $sql = 'UPDATE users_data SET which_words_to_learn = "' . $whichWordsToLearn . '" WHERE chat_id = ' . $chatId;
        mysqli_query($link, $sql);

        $wordInfo = getWordInfoFromDB($link, $chatId, 1);

        //Здесь 2 кнопки: 'Понел' и 'Непонел'
        $inlineKeyboard = [[[ 'text' => "Хорошо", 'callback_data' => "good" ], [ 'text' => "Плохо", 'callback_data' => "bad" ]]];
        $keyboard = [ 'inline_keyboard' => $inlineKeyboard ];
        $replyMarkup = json_encode($keyboard);

        printWordAndTranscription($telegram, $chatId, $replyMarkup, $wordInfo);

        //Отправляю определение
        if (!empty($wordInfo["definition"]) && !empty($wordInfo["usageExample"]))
        {
            $reply = $wordInfo["definition"] . "\nUsage example: <i>" . $wordInfo["usageExample"] . "</i>";
            $telegram->sendMessage([ 'chat_id' => $chatId, 'text' => $reply, 'parse_mode' => "HTML" ]);
        }
        elseif (!empty($wordInfo["definition"]))
        {
            $reply = $wordInfo["definition"];
            $telegram->sendMessage([ 'chat_id' => $chatId, 'text' => $reply ]);
        }
        elseif (!empty($wordInfo["usageExample"]))
        {
            $reply = "Usage example: <i>" . $wordInfo["usageExample"] . "</i>";
            $telegram->sendMessage([ 'chat_id' => $chatId, 'text' => $reply, 'parse_mode' => "HTML" ]);
        }
    }
    else
    {
        $reply = 'НЕТУ СЛОВ БЛИН. Нужно скорее добавить!!!';

        $telegram->sendMessage([ 'chat_id' => $chatId, 'text' => $reply ]);
    }
}

function getWordInfoFromDB(mysqli $link, int $chatId, int $wordNum): array
{
    $sql = 'SELECT word, transcription_uk, transcription_us, translation, definition, usage_example FROM word_list WHERE chat_id = ' . $chatId . ' AND word_num = ' . $wordNum;
    $sqlResult = mysqli_fetch_array(mysqli_query($link, $sql));

    return [
        "word" => $sqlResult["word"],
        "pronunciations" => [
            "transcriptionUK" => $sqlResult["transcription_uk"],
            "transcriptionUS" => $sqlResult["transcription_us"]
        ],
        "definition" => $sqlResult["definition"],
        "usageExample" => $sqlResult["usage_example"],
        "translation" => $sqlResult["translation"]
    ];
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                                               /* Inline кнопки */
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function getButtonAnswer(object $telegram, mysqli $link, string $tempWordInfoFile, array $callbackQuery): void
{
    $callbackQueryData = $callbackQuery["data"];
    $chatId = $callbackQuery["message"]["chat"]["id"];
    $inlineKeyboard = [[]];

    //Получаю из файла временный массив
    $tempWordInfo = unserialize(file_get_contents($tempWordInfoFile));
    $definitionsByPartOfSpeech = $tempWordInfo["definitionsByPartOfSpeech"];

    if ($callbackQueryData === 'definitions')
    {
        getButtonDefinitionsAnswer($telegram, $chatId, $inlineKeyboard, $definitionsByPartOfSpeech);
    }
    elseif ($callbackQueryData === 'add_to_the_list')
    {
        addWordToList($telegram, $link, $chatId, $tempWordInfo);
    }
    elseif (in_array($callbackQueryData , [PART_OF_SPEECH_NOUN, PART_OF_SPEECH_VERB, PART_OF_SPEECH_ADJECTIVE, PART_OF_SPEECH_ADVERB, PART_OF_SPEECH_INTERJECTION, PART_OF_SPEECH_PRONOUN, PART_OF_SPEECH_CONJUNCTION]))
    {
        getButtonPartOfSpeechAnswer($telegram, $chatId, $inlineKeyboard, $tempWordInfoFile, $callbackQueryData, $tempWordInfo);
    }
    elseif (in_array($callbackQueryData , [DEFINITION_FIRST, DEFINITION_SECOND, DEFINITION_THIRD]))
    {
        addDefinitionToList($telegram, $chatId, $link, $callbackQueryData, $tempWordInfo);
    }
    elseif (in_array($callbackQueryData , [MARK_GOOD, MARK_BAD]))
    {
        getResponseFromLearning($telegram, $link, $chatId, $callbackQueryData);
    }
}

function getButtonDefinitionsAnswer(object $telegram, int $chatId, array $inlineKeyboard, array $definitionsByPartOfSpeech): void
{
    foreach ($definitionsByPartOfSpeech as $partOfSpeech => $lexemes)
    {
        $partOfSpeechText = $partOfSpeech;
        $partOfSpeechText[0] = strtoupper($partOfSpeechText[0]);

        array_push($inlineKeyboard[0], [ 'text' => $partOfSpeechText, 'callback_data' => $partOfSpeech ]);
    }

    //Здесь возможные кнопки 'существительное', 'глагол', 'прилагательное', 'наречие', 'междометие'
    $keyboard = [ 'inline_keyboard' => $inlineKeyboard ];
    $replyMarkup = json_encode($keyboard);

    $reply = "What part of speech is your word?";

    $telegram->sendMessage([ 'chat_id' => $chatId, 'text' => $reply, 'reply_markup' => $replyMarkup ]);
}

function addWordToList(object $telegram, mysqli $link, int $chatId, array $wordInfo): void
{
    $word = $wordInfo["word"];

    $sql = 'SELECT word_num FROM word_list WHERE chat_id = ' . $chatId . ' AND word = "' . $word . '"';
    $sqlResult = mysqli_query($link, $sql);

    $wordNum = (int)mysqli_fetch_array($sqlResult)["word_num"];

    if (empty($wordNum))
    {
        $sql = 'SELECT MAX(word_num) FROM word_list WHERE chat_id = ' . $chatId;
        $sqlResult = mysqli_query($link, $sql);

        $maxWordNum = (int)mysqli_fetch_array($sqlResult)["MAX(word_num)"];

        if (!empty($maxWordNum))
        {
            //В $wordNum номер последнего слова. Добавляю следующее слово в список
            addWordToDBList($link, $chatId, $maxWordNum + 1, $wordInfo);
        }
        else
        {
            //Номера последнего слова нет. Добавляю первое слово в списке
            addWordToDBList($link, $chatId, 1, $wordInfo);
        }

        $reply = "Слово успешно добавлено!";
    }
    else
    {
        $reply = "Слово уже есть в списке. Расслабься :)";
    }

    $telegram->sendMessage([ 'chat_id' => $chatId, 'text' => $reply ]);
}

function getButtonPartOfSpeechAnswer(object $telegram, int $chatId, array $inlineKeyboard, string $tempWordInfoFile, string $callbackQueryData, array $tempWordInfo): void
{
    $definitionsByPartOfSpeech = $tempWordInfo["definitionsByPartOfSpeech"];
    $reply = "";

    foreach ($definitionsByPartOfSpeech[$callbackQueryData] as $index => $sense)
    {
        $definition = $sense["definition"];
        $usageExample = $sense["usageExample"];
        $index++;

        $reply .= "<b>$index.</b> $definition\n";
        if (!empty($usageExample))
        {
            $reply .= "Usage example: <i>$usageExample</i>\n";
        }
        $inlineKeyboard[0][$index - 1] = [ 'text' => "$index", 'callback_data' => "$index" ];
    }

    $reply .= "\nIf you want to add a definition with a word to the list, then choose the one that you like the most.";

    //Здесь разлчное количество кнопок: от 1 до 3, в виде '1', '2', '3'
    $keyboard = [ 'inline_keyboard' => $inlineKeyboard ];
    $replyMarkup = json_encode($keyboard);

    $telegram->sendMessage([ 'chat_id' => $chatId, 'text' => $reply, 'parse_mode' => "HTML", 'reply_markup' => $replyMarkup ]);

    $definitionsByPartOfSpeech = [
        "$callbackQueryData" => $definitionsByPartOfSpeech[$callbackQueryData]
    ];
    $tempWordInfo["definitionsByPartOfSpeech"] = $definitionsByPartOfSpeech;

    //Вставляю в файл временный массив
    file_put_contents($tempWordInfoFile, "");
    file_put_contents($tempWordInfoFile, serialize($tempWordInfo));
}

function addDefinitionToList(object $telegram, int $chatId, mysqli $link, string $callbackQueryData, array $tempWordInfo): void
{
    $word = $tempWordInfo["word"];
    $definitionsByPartOfSpeech = $tempWordInfo["definitionsByPartOfSpeech"];

    $sql = 'SELECT definition FROM word_list WHERE chat_id = ' . $chatId . ' AND word = "' . $word . '"';
    $sqlResult = mysqli_query($link, $sql);

    $definition = mysqli_fetch_array($sqlResult)["definition"];

    if (empty($definition))
    {
        $sql = 'SELECT word_num FROM word_list WHERE chat_id = ' . $chatId . ' AND word = "' . $word . '"';
        $sqlResult = mysqli_query($link, $sql);

        $wordNum = (int)mysqli_fetch_array($sqlResult)["word_num"];

        if (empty($wordNum))
        {
            $sql = 'SELECT MAX(word_num) FROM word_list WHERE chat_id = ' . $chatId;
            $sqlResult = mysqli_query($link, $sql);

            $maxWordNum = (int)mysqli_fetch_array($sqlResult)["MAX(word_num)"];

            if (!empty($maxWordNum))
            {
                //В $wordNum номер последнего слова. Добавляю следующее слово в список
                addWordToDBList($link, $chatId, $maxWordNum + 1, $tempWordInfo);
            }
            else
            {
                //Номера последнего слова нет. Добавляю первое слово в списке
                addWordToDBList($link, $chatId, 1, $tempWordInfo);
            }
        }

        $sql = 'UPDATE word_list SET definition = "' . reset($definitionsByPartOfSpeech)[$callbackQueryData - 1]["definition"] . '", usage_example = "' . end($definitionsByPartOfSpeech)[$callbackQueryData - 1]["usageExample"] . '" WHERE chat_id = ' . $chatId . ' AND word = "' . $word . '"';
        mysqli_query($link, $sql);

        $reply = "Определение успешно добавлено!";
    }
    else
    {
        $reply = "Определение уже добавлено. Расслабься :)";
    }

    $telegram->sendMessage([ 'chat_id' => $chatId, 'text' => $reply ]);
}

function getResponseFromLearning(object $telegram, mysqli $link, int $chatId, string $callbackQueryData): void
{
    $sql = 'SELECT which_words_to_learn FROM users_data WHERE chat_id = ' . $chatId;
    $sqlResult = mysqli_query($link, $sql);

    $whichWordsToLearn = mysqli_fetch_array($sqlResult)["which_words_to_learn"];
    $currentWordNum = $whichWordsToLearn[0];
    $whichWordsToLearn = str_replace($whichWordsToLearn[0], '', $whichWordsToLearn);

    if ($callbackQueryData === MARK_BAD)
    {
        $whichWordsToLearn .= $currentWordNum;
    }

    $sql = 'UPDATE users_data SET which_words_to_learn = "' . $whichWordsToLearn . '" WHERE chat_id = ' . $chatId;
    mysqli_query($link, $sql);

    if (!empty($whichWordsToLearn))
    {
        $nextWordNum = $whichWordsToLearn[0];

        $wordInfo = getWordInfoFromDB($link, $chatId, $nextWordNum);

        //Здесь 2 кнопки: 'Понел' и 'Непонел'
        $inlineKeyboard = [[[ 'text' => "Хорошо", 'callback_data' => "good" ], [ 'text' => "Плохо", 'callback_data' => "bad" ]]];
        $keyboard = [ 'inline_keyboard' => $inlineKeyboard ];
        $replyMarkup = json_encode($keyboard);

        printWordAndTranscription($telegram, $chatId, $replyMarkup, $wordInfo);

        //Отправляю определение
        if (!empty($wordInfo["definition"]) && !empty($wordInfo["usageExample"]))
        {
            $reply = "<b>Definition:</b> " . $wordInfo["definition"] . "\n<b>Usage example:</b> <i>" . $wordInfo["usageExample"] . "</i>";
            $telegram->sendMessage([ 'chat_id' => $chatId, 'text' => $reply, 'parse_mode' => "HTML" ]);
        }
        elseif (!empty($wordInfo["definition"]))
        {
            $reply = "<b>Definition:</b> " . $wordInfo["definition"];
            $telegram->sendMessage([ 'chat_id' => $chatId, 'text' => $reply, 'parse_mode' => "HTML" ]);
        }
        elseif (!empty($wordInfo["usageExample"]))
        {
            $reply = "<b>Usage example:</b> <i>" . $wordInfo["usageExample"] . "</i>";
            $telegram->sendMessage([ 'chat_id' => $chatId, 'text' => $reply, 'parse_mode' => "HTML" ]);
        }
    }
    else
    {
        $sql = 'DELETE which_words_to_learn FROM users_data WHERE chat_id = ' . $chatId;
        mysqli_query($link, $sql);

        $reply = 'Увоооу, уже закончил. Теперь можешь довольно поурчать)';

        $telegram->sendMessage([ 'chat_id' => $chatId, 'text' => $reply ]);
    }
}

function addWordToDBList(mysqli $link, int $chatId, int $wordNum, array $wordInfo): void
{
    $sql = 'INSERT word_list(chat_id, word_num, word, transcription_uk, transcription_us, translation) VALUES (' . $chatId . ', ' . $wordNum . ', "' . $wordInfo["word"] . '", "' . $wordInfo["pronunciations"]["transcriptionUK"] . '", "' . $wordInfo["pronunciations"]["transcriptionUS"] . '", "' . $wordInfo["translation"] . '")';

    mysqli_query($link, $sql);
}