<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/6/2020
 * Time: 6:21 PM
 */

namespace que\utility\math;


class Math {

    private $precision = 3;

    /**
     * @param $a
     * @param $b
     * @return int
     */
    public function comp($a, $b) {
        return bccomp($a, $b, $this->precision);
    }

    /**
     * @param $a
     * @param $b
     * @return string
     */
    public function sub($a, $b) {
        return bcsub($a, $b, $this->precision);
    }

    /**
     * @param $a
     * @param $b
     * @return string|null
     */
    public function mod($a, $b) {
        return bcmod($a, $b);
    }

    /**
     * @param $a
     * @param $b
     * @return string|null
     */
    public function div($a, $b) {
        return bcdiv($a, $b, $this->precision);
    }

    /**
     * @param $a
     * @param $b
     * @return string
     */
    public function mul($a, $b) {
        return bcmul($a, $b, $this->precision);
    }

    /**
     * @param $a
     * @param $b
     * @return string
     */
    public function add($a, $b) {
        return bcadd($a, $b, $this->precision);
    }

    /**
     * @param $precision
     */
    public function scale($precision) {
        $this->precision = $precision;
    }

    /**
     * @param mixed ...$argv
     * @return string|string[]|null
     */
    public function run(...$argv) {

        $string = str_replace(' ', '', "({$argv[0]})");

        $operations = array();
        if (strpos($string, '^') !== false)
            $operations[] = '\^';
        if (strpbrk($string, '*/%') !== false)
            $operations[] = '[\*\/\%]';
        if (strpbrk($string, '+-') !== false)
            $operations[] = '[\+\-]';
        if (strpbrk($string, '<>!=') !== false)
            $operations[] = '<|>|=|<=|==|>=|!=|<>';

        $string = preg_replace('/\$([0-9\.]+)/e', '$argv[$1]', $string);
        while(preg_match('/\(([^\)\(]*)\)/', $string, $match)){
            foreach($operations as $operation ){
                if (preg_match("/([+-]{0,1}[0-9\.]+)($operation)([+-]{0,1}[0-9\.]+)/", $match[1], $m)){
                    switch ($m[2]){
                        case '+':
                            $result = bcadd($m[1], $m[3], $this->precision);
                            break;
                        case '-':
                            $result = bcsub($m[1], $m[3], $this->precision);
                            break;
                        case '*':
                            $result = bcmul($m[1], $m[3], $this->precision);
                            break;
                        case '/':
                            $result = bcdiv($m[1], $m[3], $this->precision);
                            break;
                        case '%':
                            $result = bcmod($m[1], $m[3], $this->precision);
                            break;
                        case '^':
                            $result = bcpow($m[1], $m[3], $this->precision);
                            break;
                        case '==':
                        case '=':
                            $result = bccomp($m[1], $m[3], $this->precision) == 0;
                            break;
                        case '>':
                            $result = bccomp($m[1], $m[3], $this->precision) == 1;
                            break;
                        case '<':
                            $result = bccomp($m[1], $m[3], $this->precision) == -1;
                            break;
                        case '>=':
                            $result = bccomp($m[1], $m[3], $this->precision) >= 0;
                            break;
                        case '<=':
                            $result = bccomp($m[1], $m[3], $this->precision) <= 0;
                            break;
                        case '<>':
                        case '!=':
                            $result = bccomp($m[1], $m[3], $this->precision) != 0;
                            break;
                    }
                    $match[1] = str_replace($m[0], $result, $match[1]);
                }
            }
            $string = str_replace($match[0], $match[1], $string);
        }

        return $string;
    }
}