<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/6/2020
 * Time: 3:36 PM
 */

namespace que\utility\random;


final class ULID
{
    const ENCODING = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
    const ENCODING_LENGTH = 32;
    private $time_src;
    private $random_float_src;

    public function __construct(TimeSourceInterface $ts = null, RandomFloatInterface $rf = null)
    {
        $this->time_src = $ts === null ? new PHPTimeSource() : $ts;
        $this->random_float_src = $rf === null ? New LcgRandomGenerator() : $rf;
    }

    public function get()
    {
        return sprintf('%s%s', $this->encodeTime($this->time_src->getTime(), 10), $this->encodeRandom(16));
    }

    private function encodeTime($time, $length)
    {
        $out = '';
        while ($length > 0) {
            $mod = (int)($time % self::ENCODING_LENGTH);
            $out = self::ENCODING[$mod] . $out;
            $time = ($time - $mod) / self::ENCODING_LENGTH;
            $length--;
        }
        return $out;
    }

    private function encodeRandom(int $length): string
    {
        $out = '';
        while ($length > 0) {
            $rand = (int)floor(self::ENCODING_LENGTH * $this->random_float_src->generate());
            $out = self::ENCODING[$rand] . $out;
            $length--;
        }
        return $out;
    }
}


class LcgRandomGenerator implements RandomFloatInterface
{
    public function generate()
    {
        // TODO: Implement generate() method.
        return lcg_value();
    }
}


class PHPTimeSource implements TimeSourceInterface
{
    public function getTime()
    {
        // TODO: Implement getTime() method.
        return time();
    }
}


interface RandomFloatInterface
{
    public function generate();
}


interface TimeSourceInterface
{
    public function getTime();
}