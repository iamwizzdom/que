<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/30/2018
 * Time: 2:24 PM
 */

namespace que\utility;

class ImageGenerator
{
    private $long; // size of text
    private $lx; // width of picture
    private $ly; // height of picture
    private $noise_txt; // background noisy characters
    private $nb_noise; // number of background noisy characters
    private $image_txt; //foreground text
    private $font_size; // character font size
    private $host; // file of captcha picture stored on disk
    private $font = QUE_PATH . "/assets/font/comic.ttf";

    /**
     * ImageGenerator constructor.
     * @param int $long
     * @param int $lx
     * @param int $ly
     * @param int $font_size
     * @param string $image_txt
     * @param string $noise_txt
     * @param int $nb_noise
     */
    public function __construct($long = 6, $lx = 1300, $ly = 600, $font_size = 14,
                                $image_txt = "Hello there! I'm Que and I generated this image.",
                                $noise_txt = "Que", $nb_noise = 100)
    {
        $this->setLong($long);
        $this->setLx($lx);
        $this->setLy($ly);
        $this->setFontSize($font_size);
        $this->setImageTxt($image_txt);
        $this->setNoiseTxt($noise_txt);
        $this->setNbNoise($nb_noise);
    }

    /**
     * @return int
     */
    public function getLong(): int
    {
        return $this->long;
    }

    /**
     * @param int $long
     */
    public function setLong(int $long): void
    {
        $this->long = $long;
    }

    /**
     * @return int
     */
    public function getLx(): int
    {
        return $this->lx;
    }

    /**
     * @param int $lx
     */
    public function setLx(int $lx): void
    {
        $this->lx = $lx;
    }

    /**
     * @return int
     */
    public function getLy(): int
    {
        return $this->ly;
    }

    /**
     * @param int $ly
     */
    public function setLy(int $ly): void
    {
        $this->ly = $ly;
    }

    /**
     * @return int
     */
    public function getNbNoise(): int
    {
        return $this->nb_noise;
    }

    /**
     * @param int $nb_noise
     */
    public function setNbNoise(int $nb_noise): void
    {
        $this->nb_noise = $nb_noise;
    }

    /**
     * @return mixed
     */
    public function getNoiseTxt()
    {
        return $this->noise_txt;
    }

    /**
     * @param mixed $noise_txt
     */
    public function setNoiseTxt($noise_txt): void
    {
        $this->noise_txt = $noise_txt;
    }

    /**
     * @return mixed
     */
    public function getImageTxt()
    {
        return $this->image_txt;
    }

    /**
     * @param mixed $image_txt
     */
    public function setImageTxt($image_txt): void
    {
        $this->image_txt = $image_txt;
    }

    /**
     * @return int
     */
    public function getFontSize(): int
    {
        return $this->font_size;
    }

    /**
     * @param int $font_size
     */
    public function setFontSize(int $font_size): void
    {
        $this->font_size = $font_size;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host . "?" . uniqid();
    }

    /**
     * @param mixed $host
     */
    public function setHost($host): void
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getFont(): string
    {
        return $this->font;
    }

    /**
     * @param string $font
     */
    public function setFont(string $font): void
    {
        $this->font = $font;
    }

    // display a captcha picture with private text and return the public text
    function make($noise = true)
    {
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

                imagettftext($image, $size, $angle, $x, $y, $color, $this->font, $this->noise_txt);
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

        $r = intval(mt_rand(0, 128));
        $g = intval(mt_rand(0, 128));
        $b = intval(mt_rand(0, 128));
        $color = ImageColorAllocate($image, $r, $g, $b);
        $shadow = ImageColorAllocate($image, $r + 128, $g + 128, $b + 128);
        $tb = imagettfbbox(17, 0, $this->font, $this->image_txt);
        $x = ceil(($this->lx - $tb[2]) / 2);
        $y = ceil(($this->ly - $tb[5]) / 2.2);
        imagettftext($image, $this->font_size, 0, $x, $y, $shadow, $this->font, $this->image_txt);
        imagettftext($image, $this->font_size, 0.1, $x, $y, $color, $this->font, $this->image_txt);

        header('Content-Type: image/png');
        imagepng($image);
        ImageDestroy($image);
    }

    public function render($noise = true)
    {
        $this->make($noise);
    }
}