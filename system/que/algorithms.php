<?php

use que\common\exception\QueRuntimeException;
use que\utility\pattern\heap\Heap;

/**
 * This is a simple fisher yates shuffle algorithm, but modified to also shuffle associative arrays
 *
 * @param array $init_arr -- This is the array to be shuffled
 * @param int $repeat -- This is the number of times you want the array to be shuffled
 * @return array
 *
 * @modifier [Wisdom Emenike](https://github.com/iamwizzdom)
 */
function fisher_yates_shuffle(array $init_arr, int $repeat = 1)
{
    for ($v = 0; $v < $repeat; $v++) {
        $n = count($init_arr);
        $keys = array_keys($init_arr);
        $l = count($keys);
        for ($i = ($n - 1); $i >= 1; $i--) {
            $j = $keys[mt_rand(0, ($l - 1))];
            list($init_arr[$j], $init_arr[$keys[$i]]) = array($init_arr[$keys[$i]], $init_arr[$j]);
        }
    }
    return $init_arr;
}

/**
 * This is a simple bubble sort algorithm
 *
 * @param array $arr
 * @param bool $reverse
 * @return array
 */
function bubble_sort(array $arr, bool $reverse = false): array
{
    $len = count($arr);
    $bound = $len - 1;
    for ($i = 0; $i < $len; $i++) {
        $swapped = false;
        $newBound = 0;
        for ($j = 0; $j < $bound; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                $tmp = $arr[$j + 1];
                $arr[$j + 1] = $arr[$j];
                $arr[$j] = $tmp;
                $swapped = true;
                $newBound = $j;
            }
        }
        $bound = $newBound;
        if (!$swapped) break;
    }
    return $reverse === true ? array_reverse($arr) : $arr;
}

/**
 * This is a simple insertion sort algorithm
 *
 * @param array $init_arr
 * @return array
 */
function insertion_sort(array $init_arr)
{
    for ($i = 0; $i < count($init_arr); $i++) {
        $val = $init_arr[$i];
        $j = $i - 1;
        while ($j >= 0 && $init_arr[$j] > $val) {
            $init_arr[$j + 1] = $init_arr[$j];
            $j--;
        }
        $init_arr[$j + 1] = $val;
    }
    return $init_arr;
}

/**
 * A recursive binary search algorithm.
 * It returns location of x in given array otherwise -1
 *
 * @param array $arr
 * @param int $left
 * @param int $right
 * @param $search
 * @return float
 * @throws RunTimeException
 */
function binary_search(array $arr, int $left, int $right, $search)
{
    if (!is_numeric_array($arr))
        throw new QueRuntimeException("binary_search expects a numeric array");

    if ($right < $left)
        throw new QueRuntimeException("binary_search expects right to be greater than left");

    if ($right >= $left) {

        $mid = ($left + ($right - $left) / 2);

        // If the element is present
        // at the middle itself
        if ($arr[$mid] == $search)
            return floor($mid);

        // If element is smaller than
        // mid, then it can only be
        // present in left subarray
        if ($arr[$mid] > $search)
            return binary_search($arr, $left, $mid - 1, $search);

        // Else the element can only
        // be present in right subarray
        return binary_search($arr, $mid + 1, $right, $search);
    }
}

/**
 * The selection sort is similar to the bubble sort only it improves it by
 * making only one exchange for every pass through the list.
 *
 * @param array $data
 * @return array|mixed
 */
function selection_sort(array $data)
{
    if (!function_exists('swap_positions')) {
        function swap_positions($data1, $left, $right)
        {
            $backup_old_data_right_value = $data1[$right];
            $data1[$right] = $data1[$left];
            $data1[$left] = $backup_old_data_right_value;
            return $data1;
        }
    }

    for ($i = 0; $i < count($data) - 1; $i++) {
        $min = $i;
        for ($j = $i + 1; $j < count($data); $j++)
            if ($data[$j] < $data[$min]) $min = $j;
        $data = swap_positions($data, $i, $min);
    }
    return $data;
}

/**
 * This is a simple merge sort algorithm
 *
 * @param array $array
 * @return array
 */
function merge_sort(array $array)
{
    if (!function_exists('merge')) {
        function merge($left, $right)
        {
            $res = [];
            while (count($left) > 0 && count($right) > 0) {
                if ($left[0] > $right[0]) {
                    $res[] = $right[0];
                    $right = array_slice($right, 1);
                } else {
                    $res[] = $left[0];
                    $left = array_slice($left, 1);
                }
            }
            while (count($left) > 0) {
                $res[] = $left[0];
                $left = array_slice($left, 1);
            }
            while (count($right) > 0) {
                $res[] = $right[0];
                $right = array_slice($right, 1);
            }
            return $res;
        }
    }

    if (count($array) == 1) return $array;
    $mid = count($array) / 2;
    $left = array_slice($array, 0, $mid);
    $right = array_slice($array, $mid);
    $left = merge_sort($left);
    $right = merge_sort($right);
    return merge($left, $right);
}

/**
 * This is a heap sort algorithm for sorting a Heap object
 *
 * @param Heap $heap
 * @return array
 */
function heap_sort(Heap $heap)
{
    $size = $heap->getSize();
    // "shift" all nodes, except lowest level as it has no children
    for ($j = (int)($size/2) - 1; $j >= 0; $j--) $heap->bubbleDown($j);

    // sort the heap
    for ($j = $size-1; $j >= 0; $j--) {
        $biggestNode = $heap->remove();
        // use same nodes array for sorted elements
        $heap->insertAt($j, $biggestNode);
    }
    return $heap->asArray();
}
