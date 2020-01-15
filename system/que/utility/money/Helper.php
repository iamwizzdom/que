<?php

namespace que\utility\money;

use Exception;

class Helper {

    /**
     * @return bool|Item
     * @throws Exception
     */
	public static function sum() {
		if (func_num_args() > 1) $list = func_get_args();
		if (!isset($list) || !count($list)) return false;
		$sum = Item::zero();
		foreach($list as $item)
            if ($item instanceof Item) $sum->add($item); else $sum->add(Item::factor($item));
		return $sum;
	}

    /**
     * @param array $list
     * @return bool|Item
     * @throws Exception
     */
	public static function average(array $list){
		if (!count($list)) return false;
		return self::sum($list)->divide(new Item(count(($list))));
	}

    /**
     * @return mixed|null
     */
	function bcmax(){
		$max = null;
		foreach(func_get_args() as $value)
            if ($max == null) $max = $value; else if (bccomp($max, $value) < 0) $max = $value;
		return $max;
	}

    /**
     * @return mixed|null
     */
	function bcmin(){
		$min = null;
		foreach(func_get_args() as $value)
            if ($min == null) $min = $value; else if (bccomp($min, $value) > 0) $min = $value;
		return $min;
	}
}

