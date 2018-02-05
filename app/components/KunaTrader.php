<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 1/9/18
 * Time: 20:12
 */

namespace app\components;


use yii\base\Component;

/**
 * Class Cuna
 * @package app\components
 * @property string $protocol
 * @property string $tickersUrl
 * @property string $url
 */
class KunaTrader extends Component {
    public $protocol, $tickersUrl;
    
    public $url = null;
    
    public function setUrl($ticker)
    {
        $this->url = $this->protocol . $this->tickersUrl . '/' . $ticker;
        return $this;
    }
    
    public function getTickerData($ticker)
    {
        $this->setUrl($ticker);
        
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSLVERSION,CURL_SSLVERSION_DEFAULT);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        
    
        return json_decode($output);
    }
}