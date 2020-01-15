<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/30/2017
 * Time: 8:51 PM
 */

namespace que\common\time;

class Time
{
    /**
     * @var Time
     */
    private static $instance;

    private $templates = [
        'prefix' => "",
        'suffix' => " ago",
        'seconds' => "less than a minute",
        'minute' => "about a minute",
        'minutes' => "%d minutes",
        'hour' => "about an hour",
        'hours' => "about %d hours",
        'day' => "a day",
        'days' => "%d days",
        'month' => "about a month",
        'months' => "%d months",
        'year' => "about a year",
        'years' => "%d years"
    ];

    protected function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    private function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @return Time
     */
    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param $time
     * @return string
     */
    public function time_ago($time)
    {
        $time_ago = is_numeric($time) ? $time : strtotime($time);
        $current_time = time();
        $time_difference = $current_time - $time_ago;
        $seconds = $time_difference;
        $minutes = round($seconds / 60);
        $hours = round($minutes / 60);
        $days = round($hours / 24);
        $years = round($days / 365);


        return $this->templates['prefix'] . (
                $seconds < 45 ? $this->template('seconds', $seconds) : (
                    $seconds < 90 ? $this->template('minute', 1) : (
                    $minutes < 45 ? $this->template('minutes', $minutes) : (
                    $minutes < 90 ? $this->template('hour', 1) : (
                    $hours < 24 ? $this->template('hours', $hours) : (
                    $hours < 42 ? $this->template('day', 1) : (
                    $days < 30 ? $this->template('days', $days) : (
                    $days < 45 ? $this->template('month', 1) : (
                    $days < 365 ? $this->template('months', $days / 30) : (
                    $years < 1.5 ? $this->template('year', 1) : $this->template('years', $years)
                    )))))))))
            ) . $this->templates['suffix'];
    }

    private function template($t, $n)
    {
        return isset($this->templates[$t]) ? str_replace("%d", abs(round($n)), $this->templates[$t]) : "";
    }
}