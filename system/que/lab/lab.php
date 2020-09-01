<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/28/2019
 * Time: 11:01 PM
 */

$startTime = ($start = microtime(true)) . "\n";

//echo dechex(0x3fff);

use que\support\Config;

require "../../../app/app.settings.php";

//echo $startTime;

//echo preg_match("/^[a-zA-Z0-9]+$/", 'jfh76') == 1 ? 'true' : 'false';

if (preg_match("/{(\?)(.*?):(.*?)}|{(.*?):(.*?)}|{(\?)(.*?)}|{(.*?)}/", "{id}", $matches) == 1) {

    if (!empty($matches[8] ?? null)) {
        debug_print([
            'arg' => $matches[8],
            'nullable' => false,
            'expression' => null
        ]);
    }
    if (!empty($matches[7] ?? null) && !empty($matches[6] ?? null)) {
        debug_print([
            'arg' => $matches[7],
            'nullable' => true,
            'expression' => null
        ]);
    }
    if (!empty($matches[5] ?? null) && !empty($matches[4] ?? null)) {
        debug_print([
            'arg' => $matches[4],
            'nullable' => false,
            'expression' => $matches[5]
        ]);
    }
    if (!empty($matches[3] ?? null) && !empty($matches[2] ?? null) && !empty($matches[1] ?? null)) {
        debug_print([
            'arg' => $matches[2],
            'nullable' => true,
            'expression' => $matches[3]
        ]);
    }
}

//$mds = [90,6,78,9];
//array_callback($mds, function ($md) {
//    $md = (object) $md;
//    return new \que\database\model\Model($md, 'user', 'id');
//});
//$modelStack = new \que\database\model\ModelStack($mds, true);
//
//foreach ($modelStack as $key => $model) unset($modelStack[$key]);
//
//debug_print($modelStack);
//usleep(
//    (1.2 * 1000000)
//);
//
//print_r(find_in_array([
//    'user' => [
//        'names' => [
//            'surname' => [
//                'firstname' => 'Wisdom',
//                'middlename' => 'Obinna'
//            ]
//        ]
//    ]
//], ['user.names.surname.middlename'], 'not found'));

//$data = [
//    'user' => [
//        'names' => [
//            'surname' => [
//                'firstname' => 'Wisdom',
//                'middlename' => 'Obinna'
//            ]
//        ]
//    ]
//];

//echo str_strip_repeated_char('l', "helll\\\lo\\\\ guys");

//$params = Config::get('database.connections.mysql');
//if ($params['unix_socket'] !== null) {
//    unset($params['host']);
//    unset($params['port']);
//}
//echo serializer_recursive($params, ";", function ($value) {
//    return $value !== null;
//});

//$class = Config::get("database.drivers.mysql");
//debug_print(['stat' => class_exists($class) ? 'true' : 'false']);
//debug_print(new $class);


//use que\permission\AccessLevelEnum;

//use que\common\time\Time;
//
//if (preg_match('/\[(.*?)\]/', "app_job.jobID[>=]", $matches)) {
//
//    debug_print([trim($matches[1], '?'), preg_replace('/\[(.*?)\]/', "", "app_job.jobID[>=]")]);
//}
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


//echo Time::getInstance()->time_ago(date("Y/m/d H:i:s",'1564582940'));

//if (preg_match('/\[(.*?)\]\((.*?)\)/', '[a:2:{s:7:"browser";s:13:"Google Chrome";s:8:"platform";s:10:"Windows 10";}](array)', $matches)) {
//
//    print_r($matches);
//} else echo "Not match";


echo "\n" . ($end = microtime(true));
echo "\n" . ($end - $start);

