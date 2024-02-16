<?php
    
    $curl = curl_init();

    curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_POST => 1,
    CURLOPT_URL => 'https://api.mercadolibre.com/oauth/token',
    CURLOPT_POSTFIELDS => [
        'grant_type' => 'authorization_code',
        'client_id' => '3714073256993613',
        'client_secret' => 'EvWf1XrLIhDY19JRACWKcCyVThkUWocb',
        'redirect_uri' => 'https://localhost.com.br',
        'code' => '$code'

    ]

    ]);

    $response = curl_exec($curl);

    file_put_contents('curl.log',print_r($response,true));


    /*
    // Iniciamos a função do CURL:
    $ch = curl_init('https://auth.mercadolivre.com.br/authorization');

    curl_setopt_array($ch, [

        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_POST => 1,
        CURLOPT_URL => 'https://auth.mercadolivre.com.br/authorization',
        CURLOPT_POSTFIELDS => [
            'response_type' => 'code',
            'client_id' => '3714073256993613',
            'redirect_uri' => 'https://localhost:8888'
    
        ]
    
        ]);

    //$resposta = json_decode(curl_exec($ch), true);
    $resposta = curl_exec($ch);

    curl_close($ch);
    */

    /*
    $url = 'https://auth.mercadolivre.com.br/authorization/';
    $apiKey = 'response_type=code&client_id=3714073256993613';
    //$apiKey = '';
    $curl = curl_init($url.$apiKey);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, True);
    $return = curl_exec($curl);
    curl_close($curl);
    
    echo $return; // a tua resposta em string json
    $arrResp = json_decode($return, true); // o teu json de resposta convertido para array

    //file_put_contents('curl.log',print_r($resposta,true));
    */

?>