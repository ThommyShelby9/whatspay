<?php

$countriesFile = dirname(__FILE__)."/../data/countries_states_cities.json";
echo $countriesFile.PHP_EOL;
$c = "";
if (file_exists($countriesFile)) {
    $countries = json_decode(file_get_contents($countriesFile), true);
    foreach ($countries as $country){
        if(!empty($c)){
            $c .= ",";
        }
        $c .= "[
        \"id\" => \"". $country["id"]."\",
        \"name\" => \"". $country["name"]."\",
        \"iso3\" => \"". $country["iso3"]."\",
        \"iso2\" => \"". $country["iso2"]."\",
        \"numeric_code\" => \"". $country["numeric_code"]."\",
        \"phone_code\" => \"". $country["phone_code"]."\",
        \"capital\" => \"". $country["capital"]."\",
        \"currency\" => \"". $country["currency"]."\",
        \"currency_name\" => \"". $country["currency_name"]."\",
        \"currency_symbol\" => \"". $country["currency_symbol"]."\",
        \"tld\" => \"". $country["tld"]."\",
        \"native\" => \"". $country["native"]."\",
        \"nationality\" => \"". $country["nationality"]."\",
        \"latitude\" => \"". $country["latitude"]."\",
        \"longitude\" => \"". $country["longitude"]."\",
        \"emoji\" => \"". $country["emoji"]."\",
        \"emojiu\" => \"". $country["emojiU"]."\",
        \"timezones\" => \"". str_replace('"', "'", json_encode($country["timezones"])) ."\",
        \"translations\" => \"". str_replace('"', "'", json_encode($country["translations"]))."\",
        ]";
    }

    $dossier = dirname(__FILE__)."/../data/";
    $nomfichier = "countries.txt";
    if (!is_dir($dossier)) {
        mkdir($dossier, 0777, true);
    }
    $fichier = fopen($dossier . "/" . $nomfichier, "a+");
    fputs($fichier, $c);
    fclose($fichier);

}

