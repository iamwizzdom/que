<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/28/2019
 * Time: 11:01 PM
 */

//echo dechex(0x3fff);

//require "../../../app/config/config.php";
//use que\permission\AccessLevelEnum;

use que\common\time\Time;

require "../../../app/app.settings.php";

if (preg_match('/\[(.*?)\]/', "app_job.jobID[>=]", $matches)) {

    debug_print([trim($matches[1], '?'), preg_replace('/\[(.*?)\]/', "", "app_job.jobID[>=]")]);
}
//
//$browser = \que\utility\client\Browser::browserInfo();
//debug_print(AccessLevelEnum::getList());
//
//$insert = "INSERT INTO table (column1, column2, column3) Values ";
////
////print_r($array);
//for ($i = 0; $i < count($array); $i++) {
//    $insert .= $array[$i] . "\n";
//}
//
//echo $insert;

//class Foo {
//
//    /**
//     * Foo constructor.
//     * @throws Exception
//     */
//    public function __construct()
//    {
//        if (!defined('MISC')) {
//            throw new Exception("Constant 'MISC' not defined");
//        }
//    }
//}
//
//class Bar extends Foo {
//
//    const MISC = 1;
//
//    public function __construct()
//    {
//        parent::__construct();
//    }
//}
//
//new Foo();


echo Time::getInstance()->time_ago(date("Y/m/d H:i:s",'1564582940'));

