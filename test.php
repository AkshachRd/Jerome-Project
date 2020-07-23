<?php
require_once 'MysqliDb.php';
require_once 'config.php';

$link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
$chatId = 401763451;
$tempWordInfo = '{"entries":[{"entry":"pearl","pronunciations":[{"audio":{"url":"http://audio.linguarobot.io/en/pearl-au.mp3","license":{"name":"BY-SA 4.0","url":"https://creativecommons.org/licenses/by-sa/4.0"},"sourceUrl":"https://commons.wikimedia.org/w/index.php?curid=79241558"},"context":{"regions":["Australia"]}},{"transcriptions":[{"transcription":"[pʰəːɫ]","notation":"IPA"},{"transcription":"/pɜːl/","notation":"IPA"}],"context":{"regions":["United Kingdom"]}},{"transcriptions":[{"transcription":"[pʰɝɫ]","notation":"IPA"},{"transcription":"/pɝl/","notation":"IPA"}],"context":{"regions":["United States"]}}],"interpretations":[{"lemma":"pearl","normalizedLemmas":[{"lemma":"pearl"}],"partOfSpeech":"noun","grammar":[{"number":["singular"],"case":["nominative"]}]},{"lemma":"pearl","normalizedLemmas":[{"lemma":"pearl"}],"partOfSpeech":"verb","grammar":[{"verbForm":["infinitive"]},{"person":["first-person","second-person","third-person"],"number":["plural"],"verbForm":["finite"],"tense":["present"],"mood":["indicative"]},{"person":["first-person","second-person","third-person"],"number":["singular","plural"],"verbForm":["finite"],"mood":["imperative"]},{"person":["first-person","second-person"],"number":["singular"],"verbForm":["finite"],"tense":["present"],"mood":["indicative"]},{"person":["first-person","second-person","third-person"],"number":["singular","plural"],"verbForm":["finite"],"tense":["present"],"mood":["subjunctive"]}]}],"lexemes":[{"lemma":"pearl","partOfSpeech":"noun","senses":[{"definition":"A shelly concretion, usually rounded, and having a brilliant luster, with varying tints, found in the mantle, or between the mantle and shell, of certain bivalve mollusks, especially in the pearl oysters and river mussels, and sometimes in certain univalves. It is usually due to a secretion of shelly substance around some irritating foreign particle. Its substance is the same as nacre, or mother-of-pearl. Round lustrous pearls are used in jewellery.","labels":["countable","uncountable"]},{"definition":"Something precious.","labels":["countable","figurative","uncountable"]},{"definition":"A capsule of gelatin or similar substance containing liquid for e.g. medicinal application.","labels":["countable","uncountable"]},{"definition":"Nacre, or mother-of-pearl.","labels":["countable","uncountable"]},{"definition":"A whitish speck or film on the eye.","labels":["countable","uncountable"]},{"definition":"A fish allied to the turbot; the brill.","labels":["countable","uncountable"]},{"definition":"A light-colored tern.","labels":["countable","uncountable"]},{"definition":"One of the circle of tubercles which form the bur on a deer\'s antler.","labels":["countable","uncountable"]},{"definition":"The size of type between diamond and agate, standardized as 5-point.","labels":["obsolete","uncountable"],"context":{"domains":["printing","typography"]}},{"definition":"A fringe or border.","labels":["countable","uncountable"]},{"definition":"A jewel or gem.","labels":["countable","obsolete","uncountable"]},{"definition":"The clitoris.","labels":["countable","euphemistic","slang","uncountable","vulgar"]}],"forms":[{"form":"pearls","grammar":[{"number":["plural"],"case":["nominative"]}]}]},{"lemma":"pearl","partOfSpeech":"verb","senses":[{"definition":"(sometimes figurative) To set or adorn with pearls, or with mother-of-pearl.","labels":["transitive"]},{"definition":"To cause to resemble pearls in shape; to make into small round grains.","labels":["transitive"],"usageExamples":["to pearl barley"]},{"definition":"To cause to resemble pearls in lustre or iridescence.","labels":["transitive"]},{"definition":"To resemble pearl or pearls.","labels":["intransitive"]},{"definition":"To hunt for pearls","labels":["intransitive"],"usageExamples":["to go pearling"]},{"definition":"To dig the nose of one\'s surfboard into the water, often on takeoff.","labels":["intransitive"],"context":{"domains":["board sports","surfing"]}}],"forms":[{"form":"pearled","grammar":[{"verbForm":["participle"],"tense":["past"]},{"person":["first-person","second-person","third-person"],"number":["singular","plural"],"verbForm":["finite"],"tense":["past"],"mood":["indicative"]}]},{"form":"pearling","grammar":[{"verbForm":["gerund"]},{"verbForm":["participle"],"tense":["present"]}]},{"form":"pearls","grammar":[{"person":["third-person"],"number":["singular"],"verbForm":["finite"],"tense":["present"],"mood":["indicative"]}]}]}],"license":{"name":"CC BY-SA 3.0","url":"https://creativecommons.org/licenses/by-sa/3.0"},"sourceUrls":["https://en.wiktionary.org/wiki/pearl"]}]}';
$tempWordInfo = json_decode($tempWordInfo, true);

$sql = 'INSERT temp_word_data(chat_id, word, transcription_uk, transcription_us, audio_uk, audio_us, translation) VALUES (' . $chatId . ', "' . $tempWordInfo["word"] . '", "' . $tempWordInfo["pronunciations"]["transcriptionUK"] . '", "' . $tempWordInfo["pronunciations"]["transcriptionUS"] . '", "' . $tempWordInfo["pronunciations"]["audioUK"] . '", "' . $tempWordInfo["pronunciations"]["audioUS"] . '", "' . $tempWordInfo["translation"] . '")';

$sqlResult = mysqli_query($link, $sql);
echo mysqli_error($link);
var_dump($sqlResult);