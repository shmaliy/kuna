<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
	    '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css'
    ];
    public $js = [
	    '//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js',
	    '//code.jquery.com/ui/1.12.1/jquery-ui.js',
        '//www.gstatic.com/charts/loader.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
	public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
}
