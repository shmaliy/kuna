<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\ActiveOrders;
use app\models\Iterations;
use app\models\MarketSeek;
use app\models\Param;
use function GuzzleHttp\Psr7\parse_query;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class TradingController extends Controller
{
    public function actionIndex()
    {
    }
    
    public function actionMe()
    {
        var_export(ActiveOrders::me());
    }
    
    public function actionTrade($userId)
    {
        ActiveOrders::trade($userId);
    }
    
    public function actionStart($userId)
    {
        $iteration = Iterations::getRow($userId);
        $config = new Param();
        $config->userId = $userId;
        $config = $config->getParams();
        
        foreach ($config as $param) {
            if ($param['name'] == Param::P_iterationTimeout) {
                $config = $param;
                break;
            }
        }
        
        if (time() - $iteration->localTs > $config['value'] * 1.5) {
            
            try {
                $string =  shell_exec('pgrep -f trading/trade');
                if (intval($string) != 0) {
                    $pArray = explode("\n", trim($string));
                    var_export($pArray);
                    foreach ($pArray as $pid) {
                        exec('sudo kill -9 ' . $pid);
                    }
                }
        
                $path = dirname(dirname(__FILE__));
                $cmd = $path . '/yii trading/trade ' . $userId . ' > /dev/null 2>&1 &';
                echo $cmd, "\n";
                shell_exec($cmd);
        
            }
            catch(\Exception $e) {}
        }
        exit;
    }
    
}
