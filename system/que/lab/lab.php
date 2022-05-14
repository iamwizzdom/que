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

//$exist = db()->exists('bank_accounts', function ($query) {
//    $query->where('user_id', 48);
//    $query->where('is_active', true);
//});

//echo $exist->getQueryString();
//debug_print($exist->isSuccessful() ? 'true' : 'false');
//$doc = new DOMDocument("1.0");
//$doc->loadXML("xml goes here");
//$responseDoc = new DOMDocument("1.0");
//$responseDoc->loadXML(trim($doc->getElementsByTagName("ResultMsg")->item(0)->nodeValue));
//echo $responseDoc->getElementsByTagName("OriginatorConversationID")->item(0)->nodeValue;

//echo $startTime;

//debug_print(array_diff_assoc([
//    'name' => 'Wisdom Samuel',
//    'gender' => 'Male'
//], [
//    'name' => 'Wisdom Emenike',
//    'gender' => 'Male'
//]));

//if (preg_match('/{{(.*?)}}/', "{{la.loan_id}}", $matches)) {
//    debug_print($matches[1]);
//}

//echo preg_match('/{{(.*?)}}/', "{{la.loan_id}}") . "\n";

//$ar = [1 => 'one', 3 => 'three', 5 => 'five', 8 => 'eight'];
//
//unset($ar[3]);
//unset($ar[5]);
//$ar[3] = '_three';
//debug_print(bubble_sort_keys($ar));

//echo preg_match("/^[a-zA-Z0-9]+$/", 'jfh76') == 1 ? 'true' : 'false';

//if (preg_match("/\\d+/", '{{$31970101287}}', $matches) == 1) {
//    echo $matches[0];
//}

//try {
//    $date = new DateTime(null);
//} catch (Exception $e) {
//    $date = null;
//}

//debug_print([DateTime::createFromFormat("Y", null)]);

//if (preg_match("/{(\?)(.*?):(.*?)}|{(.*?):(.*?)}|{(\?)(.*?)}|{(.*?)}/", "{id}", $matches) == 1) {
//
//    if (!empty($matches[8] ?? null)) {
//        debug_print([
//            'arg' => $matches[8],
//            'nullable' => false,
//            'expression' => null
//        ]);
//    }
//    if (!empty($matches[7] ?? null) && !empty($matches[6] ?? null)) {
//        debug_print([
//            'arg' => $matches[7],
//            'nullable' => true,
//            'expression' => null
//        ]);
//    }
//    if (!empty($matches[5] ?? null) && !empty($matches[4] ?? null)) {
//        debug_print([
//            'arg' => $matches[4],
//            'nullable' => false,
//            'expression' => $matches[5]
//        ]);
//    }
//    if (!empty($matches[3] ?? null) && !empty($matches[2] ?? null) && !empty($matches[1] ?? null)) {
//        debug_print([
//            'arg' => $matches[2],
//            'nullable' => true,
//            'expression' => $matches[3]
//        ]);
//    }
//}

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

//declare(ticks=1);
//
//$pid = pcntl_fork();
//if ($pid == -1) {
//    die("could not fork");
//} else if ($pid) {
//    exit(); // we are the parent
//} else {
//    // we are the child
//}

// detach from the controlling terminal
//if (posix_setsid() == -1) {
//    die("could not detach from terminal");
//}

// setup signal handlers
//pcntl_signal(SIGTERM, "sig_handler");
//pcntl_signal(SIGHUP, "sig_handler");

//$cache = \que\cache\Cache::getInstance();

// loop forever performing tasks
//while (1) {
//    // do something interesting here
//    $queue = $cache->lPop('jobs_from_que');
//    if ($queue) {
//        echo "\nProcessing job\n";
//        $queue = unserialize($queue);
//        debug_print($queue);
//        $queue->handle();
//        echo "\nJob processed\n";
//    }
////    echo 'Error: ' . $mail->getError();
////
////    echo "Completed task: ${task_id}.\n";
//
//}

//function sig_handler($signo)
//{
//
//    switch ($signo) {
//        case SIGTERM:
//            // handle shutdown tasks
//            exit;
//            break;
//        case SIGHUP:
//            // handle restart tasks
//            break;
//        default:
//            // handle all signals
//    }
//
//}

function query_filter($query, $field, $data) {
    return array_filter($data, function ($e) use ($query, $field) {
            return stripos($e[$field], $query) !== false;
        }, ARRAY_FILTER_USE_KEY);
}

$data = [
    [
        'id' => 11,
        'scientific_name' => 'Phacelia scopulina (A. Nelson) J.T. Howell var. scopulina',
        'common_name' => 'Debeque Phacelia',
        'family' => 'Hydrophyllaceae'
    ],
    [
        'id' => 12,
        'scientific_name' => 'Pogonatum urnigerum (Hedw.) P. Beauv.',
        'common_name' => 'Pogonatum Moss',
        'family' => 'Polytrichaceae'
    ],
    [
        'id' => 13,
        'scientific_name' => 'Phacelia infundibuliformis Torr.',
        'common_name' => 'Rio Grande Phacelia',
        'family' => 'Hydrophyllaceae'
    ],
    [
        'id' => 14,
        'scientific_name' => 'Campylium halleri (Hedw.) Lindb.',
        'common_name' => 'Haller\'s Campylium Moss',
        'family' => 'Amblystegiaceae'
    ]
];

//$res = query_filter("moss", "common_name", $data);
//
//debug_print($res);

//function shorten_path($path) {
//    $arr = explode('/', $path);
//    $s = [];
//
//    foreach ($arr as $str) {
//        if ($str == '.' || strlen($str) == 0) {
//            continue;
//        } elseif ($str == '..') {
//            if (count($s) > 0) {
//                array_pop($s);
//            }
//        } else {
//            array_push($s, $str);
//        }
//    }
//
//    $res = "";
//    while (count($s) > 0) {
//        $res .= ("/" . array_shift($s));
//    }
//
//    if (strlen($res) == 0) {
//        return '/';
//    }
//    return $res;
//}

//function shorten_path($path) {
//    $st = [];
//
//    $res = "/";
//
//    $size = strlen($path);
//
//    $arr = explode('/', $path);
//
//    for ($i = 0; $i < $size; $i++) {
//        $dir = "";
//
//        while ($i < $size && ($arr[$i] ?? null) == '/') {
//            $i++;
//        }
//
//        while ($i < $size && isset($arr[$i]) && $arr[$i] != '/') {
//            $dir .= $arr[$i];
//            $i++;
//        }
//
//        if ($dir == '..') {
//            if (count($st) != 0) {
//                array_pop($st);
//            }
//        } elseif ($dir == '.') {
//            continue;
//        } elseif (strlen($dir) != 0) {
//            array_push($st, $dir);
//        }
//
//        $_st = [];
//
//        while (count($st) != 0) {
//            array_push($_st, $st[count($st) - 1]);
//            array_pop($st);
//        }
//
//        while (count($_st) > 0) {
//            if (count($_st) != 1) {
//                $res .= ($_st[count($_st) - 1] . "/");
//            } else {
//                $res .= $_st[count($_st) - 1];
//            }
//            array_pop($_st);
//        }
//
//        return $res;
//    }
//}


//function shorten_path($path) {
//    $st = [];
//    $arr = explode('/', $path);
//    foreach ($arr as $i) {
//        if ($i == '..') {
//            if (count($st) > 1) {
//                array_pop($st);
//            } else {
//                continue;
//            }
//        } elseif ($i == '.') {
//            continue;
//        } elseif (!empty($i)) {
//            array_push($st, $i);
//        }
//    }
//
//    if (count($st) == 1) {
//        return '/';
//    }
//    return join('/', $st);
//}

function shorten_path($path) {
    $st = [];
    $arr = explode('/', $path);
    $size = 0;
    foreach ($arr as $i) {
        if ($i == '' || $i == '.' || $i == '..') {
            if ($i == '..' && $size > 0) {
                $size--;
            }
            continue;
        }
        $st[$size++] = $i;
    }

    if ($size == 0) {
        return '/';
    }

    $res = '';
    for ($n = 0; $n < $size; $n++) {
        $res .= '/' . $st[$n];
    }
    return $res;
}
$str = "../../foo/../test/../test/../foo//bar/./baz";
$res = shorten_path($str);
debug_print($res);

echo "\n\nStarted at: $start\n";
$end = microtime(true);
echo "Ended at: $end\n";
echo "Time diff: " . ($end - $start);

