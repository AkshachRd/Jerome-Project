<?php
//Lingua Robot

/*function getWordInfo($word)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://lingua-robot.p.rapidapi.com/language/v1/entries/en/$word",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "x-rapidapi-host: lingua-robot.p.rapidapi.com",
            "x-rapidapi-key: 99d6039723mshcaa6930780810d7p173043jsna4ae1d23e3de"
        )
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        echo $response;
    }

    getPronunciations();
}

function getPronunciations()
{

}*/
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://lingua-robot.p.rapidapi.com/language/v1/entries/en/testx",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "x-rapidapi-host: lingua-robot.p.rapidapi.com",
        "x-rapidapi-key: 99d6039723mshcaa6930780810d7p173043jsna4ae1d23e3de"
    )
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    if (empty($response['entries']))
    {
        echo 'NULL';
    }
    else
    {
        echo $response;
    }
}
?>