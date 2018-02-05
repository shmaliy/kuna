<?php
/**
 * @author Aleksandr Mokhonko
 * @version 2.0
 * @author yohan (ver 1.0)
 * Date: 19.11.15
 *
 * A component for execute console command of Yii console application
 */
namespace app\components;

use yii\base\Component;
use Yii;

class TConsoleRunner extends Component
{
	/**
	 * Running console command on background
	 * @param string $cmd argument that passed to console application
	 * @return boolean
	 */
	public function run($cmd)
	{
		$cmd = 'php ' . Yii::$app->getBasePath() . '/yii ' . $cmd;
		if($this->isWindows()) {
			pclose(popen('start /b '.$cmd, 'r'));
		}
		else {
			pclose(popen($cmd.' /dev/null &', 'r'));
		}

		return true;
	}

    public function execute($cmd) {
        $cmd2 = "/usr/bin/php " . Yii::$app->getBasePath() . "/yii " . $cmd . " > /dev/null 2>&1 &";
        exec($cmd2);
    }

	/**
	 * Function to check operating system
	 */
	protected function isWindows()
	{
		if(PHP_OS == 'WINNT' || PHP_OS == 'WIN32')
			return true;
		else
			return false;
	}
}