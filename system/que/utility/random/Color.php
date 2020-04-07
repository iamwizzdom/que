<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/6/2020
 * Time: 4:27 PM
 */

namespace valve\random;

class Color
{
    /**
     * @return string
     */
    public function basic()
    {
        $color = '#';
        for ($i = 0; $i < 6; $i++) {
            $randNum = mt_rand(0, 15);
            switch ($randNum) {
                case 10 :
                    $randNum = 'A';
                    break;
                case 11 :
                    $randNum = 'B';
                    break;
                case 12 :
                    $randNum = 'C';
                    break;
                case 13 :
                    $randNum = 'D';
                    break;
                case 14 :
                    $randNum = 'E';
                    break;
                case 15 :
                    $randNum = 'F';
                    break;
            }
            $color .= $randNum;
        }

        return $color;
    }

    public function range($r = "0-255", $g = "0-255", $b = "0-255")
    {
        // ensure that values are in the range between 0 and 255
        $r = $this->part($r);
        $g = $this->part($g);
        $b = $this->part($b);

        return vsprintf("#%s%s%s", [
            $this->pad($r[0], $r[1]),
            $this->pad($g[0], $g[1]),
            $this->pad($b[0], $b[1])
        ]);
    }

    /**
     * Usage Example
     * "<div style='background-color:rgb($r,$g,$b); width:10px; height:10px; float:left;'></div>";
     *
     * @param int $total
     * @param int $spread
     * @param bool $hex
     * @return array
     */
    public function relative($total = 3, $spread = 25, $hex = false)
    {
        $list = [];
        for ($row = 0; $row < $total; ++$row) {
            for ($c = 0; $c < 3; ++$c) {
                $color[$c] = mt_rand(0 + $spread, 255 - $spread);
            }

            $colors = [];
            for ($i = 0; $i < 92; ++$i) {

                if ($hex) {
                    $colors[] = vsprintf("#%s%s%s", [
                        $this->pad($color[0] - $spread, $color[0] + $spread),
                        $this->pad($color[1] - $spread, $color[1] + $spread),
                        $this->pad($color[2] - $spread, $color[2] + $spread)
                    ]);
                } else {
                    $colors[] = [
                        "r" => mt_rand($color[0] - $spread, $color[0] + $spread),
                        "g" => mt_rand($color[1] - $spread, $color[1] + $spread),
                        "b" => mt_rand($color[2] - $spread, $color[2] + $spread)
                    ];
                }
            }
            $list[] = $colors;
        }

        return $list;
    }

    public function pad($min, $max)
    {
        return str_pad(dechex(mt_rand($min, $max)), 2, '0', STR_PAD_LEFT);
    }

    public function part($v)
    {
        if (!is_array($v)) {
            $v = explode("-", $v);
        }
        $v = array_filter($v);

        switch (count($v)) {
            case 0 :
                $min = 0;
                $max = 255;
                break;

            case 1 :
                $min = 0;
                $max = max(0, min((int)reset($v), 255));
                break;

            default :
                $min = max(0, min((int)reset($v), 255));
                $max = max(0, min((int)next($v), 255));
                break;
        }

        return [
            $min,
            $max
        ];
    }
}

