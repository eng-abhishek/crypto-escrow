<?php

if (! function_exists('getCaptchaBase64Image')) {

function getCaptchaBase64Image($document_path)
{
    $extension = pathinfo($document_path, PATHINFO_EXTENSION);
    $data = file_get_contents($document_path);
    //return $data;
    $base64_image = 'data:image/' . $extension . ';base64,' . base64_encode($data);
    return $base64_image;
}
}

if (! function_exists('getBase64Image')) {

function getBase64Image($document_path)
{
  // return asset('storage/'.$document_path);
   
 if(\Storage::exists($document_path)){
       
        $fullpath = 'storage/'.$document_path;
        
        $path = \Storage::path($document_path);
        $data = \File::get($path);

        $extension = pathinfo($fullpath, PATHINFO_EXTENSION);
         $base64_image = 'data:image/' . $extension . ';base64,' . base64_encode($data);
        return $base64_image;
    }else{
        return asset('Frontend/assets/img/image.png');
    }
}
}


if (! function_exists('getNormalImage')) {
function getNormalImage($document_path,$img_name)
{
    if($img_name != '' && \Storage::exists('public/'.$document_path.'/'.$img_name)){
       
        $image = asset('storage/'.$document_path.'/'.$img_name);
  
        return $image;
    }else{
        return asset('assets/backend/assets/images/user.jpg');
    }
}
}

if (! function_exists('getCryptoPrices')) {
function getCryptoPrices() {
    $url = "https://api.coingecko.com/api/v3/simple/price";
    $params = [
        "ids" => "bitcoin,monero",
        "vs_currencies" => "usd,eur,gbp,cad,aud"
    ];

    try {
        $url .= '?' . http_build_query($params);
        $response = file_get_contents($url);

        if ($response === false) {
            throw new Exception("Failed to fetch data.");
        }

        $data = json_decode($response, true);
        
        $cryptoPrices = [
            "Bitcoin (BTC)" => [
                "USD" => $data["bitcoin"]["usd"],
                "EUR" => $data["bitcoin"]["eur"],
                "GBP" => $data["bitcoin"]["gbp"],
                "CAD" => $data["bitcoin"]["cad"],
                "AUD" => $data["bitcoin"]["aud"]
            ],
            "Monero (XMR)" => [
                "USD" => $data["monero"]["usd"],
                "EUR" => $data["monero"]["eur"],
                "GBP" => $data["monero"]["gbp"],
                "CAD" => $data["monero"]["cad"],
                "AUD" => $data["monero"]["aud"]
            ]
        ];

        return $cryptoPrices;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        return null;
    }
}
}

if (! function_exists('getCryptoPricesOnLogin')) {
function getCryptoPricesOnLogin() {
    $url = "https://api.coingecko.com/api/v3/simple/price";
    $params = [
        "ids" => "bitcoin,monero",
        "vs_currencies" => "usd,eur,gbp"
    ];

    try {
        $url .= '?' . http_build_query($params);
        $response = file_get_contents($url);

        if ($response === false) {
            throw new Exception("Failed to fetch data.");
        }

        $data = json_decode($response, true);
        
        $cryptoPrices = [
            "Bitcoin (BTC)" => [
                "USD" => $data["bitcoin"]["usd"],
                "EUR" => $data["bitcoin"]["eur"],
                "GBP" => $data["bitcoin"]["gbp"]
     
            ],
            "Monero (XMR)" => [
                "USD" => $data["monero"]["usd"],
                "EUR" => $data["monero"]["eur"],
                "GBP" => $data["monero"]["gbp"]
            ]
        ];

        return $cryptoPrices;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        return null;
    }
}
}

if (! function_exists('getUserImageBase64')) {

 function getUserImageBase64($path){
 
try {
    // Make a GET request to the API to fetch the cartoon image
    $imageData = file_get_contents($path);
    // print_r($imageData);
    // die;
    if ($imageData === false) {
        throw new Exception("Failed to fetch image data.");
    }

    // Encode the image data as base64
    $base64Image = base64_encode($imageData);
    return $base64Image;
    
    // Display the base64-encoded image
    //echo '<img src="data:image/png;base64,' . $base64Image . '" alt="Random Cartoon Image">';

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
}
}

if (! function_exists('getHiddenUserName')) {
 function getHiddenUserName($username){
        $buyer = $username;
        $firstChar = substr($buyer,0,1);
        $lastChar = substr($buyer,-1,1);
        return $firstChar.'***'.$lastChar;
    }
}
?>