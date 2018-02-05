<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\MarketSeek;
use yii\console\Controller;
use yii\console\ExitCode;
use linslin\yii2\curl;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class TestController extends Controller
{
    public function actionIndex()
    {
        var_export(hash_hmac('sha256', 'test', KUNA_SK));
    }
    
    private function gettimestamp()
    {
        $curl = new curl\Curl();
        return $curl->get('https://kuna.io/api/v2/timestamp');
    }
    
    public function actionTest()
    {
    
//        https://kuna.io/api/v2/members/me?access_key=dV6vEJe1CO&tonce=1465850766246&signature=secret
        
        var_export($this->gettimestamp());
        echo "\n";
        var_export(time());
        echo "\n";
        return;
        
        
        $curl = new curl\Curl();
        $tonce = microtime();
        $str = "GET|/api/v2/members/me|access_key=" . KUNA_PK . "tonce=" . $tonce;
        $res = $curl->setGetParams([
            'access_key' => KUNA_PK,
            'tonce' => $tonce,
            'signature' => hash_hmac('sha256', $str, KUNA_SK)
        ])->get('https://kuna.io/api/v2/members/me');
        var_export($res);
        echo "\n";
    }
}
