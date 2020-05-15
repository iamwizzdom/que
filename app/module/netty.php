<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/10/2020
 * Time: 12:44 AM
 */

require '../app.settings.php';

$detector = new Netty\NetworkDetect("08064048764");

$networkName = $detector->getNetworkName();
$numberPrefix = $detector->getNumberPrefix();

print_r([
    $networkName,
    $numberPrefix
]);