<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/30/2018
 * Time: 2:24 PM
 */

namespace que\security;

use que\session\Session;

class Captcha
{
    private $key; // ultra private static text
    private $long; // size of text
    private $lx; // width of picture
    private $ly; // height of picture
    private $nb_noise; // number of background noisy characters
    private $font_size; // character font size
    private $host; // file of captcha picture stored on disk
    private $font = QUE_PATH . "/assets/font/comic.ttf";

    /**
     *
     * @param int $long
     * @param int $lx
     * @param int $ly
     * @param int $nb_noise
     * @param int $font_size
     * @internal param $ int length $long*            int length $long
     * @internal param $ int width $lx*            int width $lx
     * @internal param $ int height $ly*            int height $ly
     * @internal param $ int noise $nb_noise*            int noise $nb_noise
     */
    public function __construct($long = 6, $lx = 120, $ly = 30, $nb_noise = 25, $font_size = 10)
    {
        $this->key = hash("SHA256", "Jehovah will reward all those who endure to the end.");
        $this->long = $long;
        $this->lx = $lx;
        $this->ly = $ly;
        $this->nb_noise = $nb_noise;
        $this->font_size = $font_size;
    }

    public function setFont($font)
    {
        $this->font = $font;
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function getHost()
    {
        return $this->host . "?" . uniqid();
    }

    public function getPrivateKey()
    {
        if (!isset(Session::getInstance()->getFiles()->_get()['session']['captcha']))
            $this->setPrivateKey();
        return Session::getInstance()->getFiles()->_get()['session']['captcha'];
    }

    public function setPrivateKey()
    {
        Session::getInstance()->getFiles()->_get()['session']['captcha'] = $this->generatePrivateKey();
    }

    public function resetPrivateKey()
    {
        Session::getInstance()->getFiles()->_get()['session']['captcha'] = $this->generatePrivateKey();
    }

    private function generatePrivateKey()
    {
        return str_rand($this->long);
    }

    // display a captcha picture with private text and return the public text
    function make($noise = true)
    {
        $private_key = $this->getPrivateKey();
        $image = imagecreatetruecolor($this->lx, $this->ly);
        $back = ImageColorAllocate($image, intval(mt_rand(224, 255)), intval(mt_rand(224, 255)), intval(mt_rand(224, 255)));
        ImageFilledRectangle($image, 0, 0, $this->lx, $this->ly, $back);
        if ($noise) { // rand characters in background with random position,
            // angle, color
            for ($i = 0; $i < $this->nb_noise; $i++) {
                $size = intval(mt_rand(($this->font_size / 2), (($this->font_size / 2) * 2)));
                $angle = intval(mt_rand(0, 360));
                $x = intval(mt_rand(10, $this->lx - 10));
                $y = intval(mt_rand(0, $this->ly - 5));
                $color = imagecolorallocate($image, intval(mt_rand(160, 224)), intval(mt_rand(160, 224)), intval(mt_rand(160, 224)));
                // $text=chr(intval(mt_rand(45,250)));
                $text = chr(intval(mt_rand(45, 126)));

                imagettftext($image, $size, $angle, $x, $y, $color, $this->font, $text);
            }
        } else { // random grid color
            for ($i = 0; $i < $this->lx; $i += 10) {
                $color = imagecolorallocate($image, intval(mt_rand(160, 224)), intval(mt_rand(160, 224)), intval(mt_rand(160, 224)));
                imageline($image, $i, 0, $i, $this->ly, $color);
            }
            for ($i = 0; $i < $this->ly; $i += 10) {
                $color = imagecolorallocate($image, intval(mt_rand(160, 224)), intval(mt_rand(160, 224)), intval(mt_rand(160, 224)));
                imageline($image, 0, $i, $this->lx, $i, $color);
            }
        }
        // private text to read
        for ($i = 0, $x = ($this->lx / 2.3); $i < $this->long; $i++) {
            $r = intval(mt_rand(0, 128));
            $g = intval(mt_rand(0, 128));
            $b = intval(mt_rand(0, 128));
            $color = ImageColorAllocate($image, $r, $g, $b);
            $shadow = ImageColorAllocate($image, $r + 128, $g + 128, $b + 128);
            $size = intval(mt_rand($this->font_size, ($this->font_size * 2)));
            $angle = intval(mt_rand(-30, 30));
            $text = substr($private_key, $i, 1);
            imagettftext($image, $size, $angle, $x + 2, 45, $shadow, $this->font, $text);
            imagettftext($image, $size, $angle, $x, 42, $color, $this->font, $text);
            $x += $size + 2;
        }

        header('Content-Type: image/png');
        imagepng($image);
        ImageDestroy($image);
    }

    public function render($noise = true)
    {
        $this->make($noise);
    }
}