<?php


namespace Modules\MultiCurrency\Converter;
use App\Setting;

class Converter
{
    /**
     * Api URL for calls
     * @var string
     */
    //protected $apiUrl = 'https://api.exchangeratesapi.io/latest?base=USD';

      //protected $apiUrl = 'http://api.exchangeratesapi.io/v1/latest?access_key=5f12c5758084396f17d6824dd7db3019';
     
    protected $apiUrl = '';

    public function __construct()
    {
     $record = Setting::take(1)->orderBy('id','desc')->first();
     $key = $record->api_key;

     $this->apiUrl = 'http://api.exchangeratesapi.io/v1/latest?access_key='.$key.'';

    }
     //  protected $apiUrl = 'http://api.exchangeratesapi.io/v1/latest?access_key=44f8544d230163ee5dce6070ce7ccdf4';

     // $key = Setting::where()

      //protected $apiUrl = 'http://api.exchangeratesapi.io/v1/latest?access_key='.$key.'';




    // http://api.exchangeratesapi.io/v1/latest?access_key=5f12c5758084396f17d6824dd7db3019&format=1

    // http://api.exchangeratesapi.io/v1/latest?access_key=5f12c5758084396f17d6824dd7db3019?base=USD

    /**
     * Time in minutes for how long are rates cached
     * @var int
     */
    protected $cacheTime = 2;

    public function getRates() {

        try {
            $rates = \Cache::remember('multicurrency_rates_array', $this->cacheTime, function () {
                $json = json_decode(file_get_contents($this->apiUrl), true);
           
                
                $ratesArray = $json['rates'];

                return $ratesArray;
            });

            return $rates;
         } catch (\Exception $e) {

             return null;
         }
    }

    public function convert($usdValue, $currencyName = 'USD') {
  
          try{
            $currency = strtoupper($currencyName);
            $rates = $this->getRates();
            $rate = $rates[$currency];
            return $usdValue*$rate;
        } catch (\Exception $e){
            return $usdValue;
        }
    }

    public function convertFromLocal($localValue, $currencyName = 'USD') {
        try{
            $currency = strtoupper($currencyName);
            $rates = $this->getRates();
            $rate = $rates[$currency];
            return $localValue/$rate;
        } catch (\Exception $e){
            return $localValue;
        }
    }

    public function getSupportedCurrencies(){

        return  Currencies::getCurrencies();
    }

}