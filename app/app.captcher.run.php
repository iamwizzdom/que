<?php

use que\security\Captcha;

require 'app.settings.php';

try{
    $hv = new Captcha(4,450,50,100, 14);
    $hv->setFont(QUE_PATH . "/assets/font/comic.ttf");
    $hv->setHost(APP_HOST . "/app.captcher.run");
    $hv->render(true);
}catch(Exception $e){
    readfile(QUE_PATH . "/assets/image/captcher.png");
}