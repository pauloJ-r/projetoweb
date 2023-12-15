<?php 

// echo "<br>" . "Validate Token" . "</br>";

function validateToken() {

    $token = $_COOKIE['token'];
    // var_dump($token);

    $arrayToken = explode('.', $token);   
    // var_dump($arrayToken);

    $header = $arrayToken[0];
    $payload = $arrayToken[1];
    $signature = $arrayToken[2];

    $key = "JR3rKQea7lgvtOM5wXCD";

    $validateToken = hash_hmac('sha256', "$header.$payload", $key, true);

    $validateToken = base64_encode($validateToken);

    if ($signature == $validateToken) {

        $dataToken = base64_decode($payload);

        $dataToken = json_decode($dataToken);
        // var_dump($dataToken);
        
        if ($dataToken->exp > time()) {
            return true;
        } else {
            return false;
        }

    } else {
        return false;
    }

}

function getName() {
    $token = $_COOKIE['token'];

    $arrayToken = explode('.', $token);
    $payload = $arrayToken[1];

    $dataToken = base64_decode($payload);
    $dataToken = json_decode($dataToken);
    // var_dump($dataToken->name);   
    return $dataToken->name;
}