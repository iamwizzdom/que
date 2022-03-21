<?php

use que\common\exception\QueRuntimeException;
use que\utility\pattern\heap\Heap;

/**
 * This is a simple fisher yates shuffle algorithm, but modified to also shuffle associative arrays
 *
 * @param array $arr -- This is the array to be shuffled
 * @param int $repeat -- This is the number of times you want the array to be shuffled
 * @return array
 *
 * @modifier [Wisdom Emenike](https://github.com/iamwizzdom)
 */
function fisher_yates_shuffle(array $arr, int $repeat = 1): array
{
    for ($v = 0; $v < $repeat; $v++) {
        $n = count($arr);
        $keys = array_keys($arr);
        for ($i = ($n - 1); $i >= 1; $i--) {
            $j = $keys[mt_rand(0, ($n - 1))];
            list($arr[$j], $arr[$keys[$i]]) = array($arr[$keys[$i]], $arr[$j]);
        }
    }
    return $arr;
}

/**
 * @param array $arr
 * @param bool $reverse
 * @return array
 */
function bubble_sort_keys(array $arr, bool $reverse = false): array {
    $keys = bubble_sort(array_keys($arr), $reverse);
    $array = [];
    foreach ($keys as $key) $array[$key] = $arr[$key];
    return $array;
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
    $keys = array_keys($arr);
    for ($i = 0; $i < $len; $i++) {
        $swapped = false;
        $newBound = 0;
        for ($j = 0; $j < $bound; $j++) {
            if ($arr[$keys[$j]] > $arr[$keys[$j + 1]]) {
                $tmp = $arr[$keys[$j + 1]];
                $arr[$keys[$j + 1]] = $arr[$keys[$j]];
                $arr[$keys[$j]] = $tmp;
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
 * @param array $arr
 * @return array
 */
function insertion_sort(array $arr): array
{
    for ($i = 0; $i < count($arr); $i++) {
        $val = $arr[$i];
        $j = $i - 1;
        while ($j >= 0 && $arr[$j] > $val) {
            $arr[$j + 1] = $arr[$j];
            $j--;
        }
        $arr[$j + 1] = $val;
    }
    return $arr;
}

/**
 * A recursive binary search algorithm.
 * It returns location of x in given array otherwise -1
 *
 * @param array $arr
 * @param $search
 * @return float
 * @throws RunTimeException
 */
function binary_search(array $arr, $search): float
{
    if (!is_numeric_array($arr))
        throw new QueRuntimeException("binary_search expects a numeric array");

    if (!function_exists('b_search')) {
        function b_search($arr, $search, $left, $right): float|int
        {
            if ($right < $left) return -1;

            $mid = ($left + ($right - $left) / 2);

            // If the element is present
            // at the middle itself
            if ($arr[$mid] == $search)
                return floor($mid);

            // If element is smaller than
            // mid, then it can only be
            // present in left subarray
            if ($arr[$mid] > $search)
                return b_search($arr, $search, $left, $mid - 1);

            // Else the element can only
            // be present in right subarray
            return b_search($arr, $search, $mid + 1, $right);
        }
    }
    return b_search($arr, $search, 0, count($arr));
}

/**
 * The selection sort is similar to the bubble sort only it improves it by
 * making only one exchange for every pass through the list.
 *
 * @param array $arr
 * @return array
 */
function selection_sort(array $arr): array
{
    if (!function_exists('swap_positions')) {
        function swap_positions($data, $left, $right)
        {
            $rightData = $data[$right];
            $data[$right] = $data[$left];
            $data[$left] = $rightData;
            return $data;
        }
    }

    for ($i = 0; $i < count($arr) - 1; $i++) {
        $min = $i;
        for ($j = $i + 1; $j < count($arr); $j++)
            if ($arr[$j] < $arr[$min]) $min = $j;
        $arr = swap_positions($arr, $i, $min);
    }
    return $arr;
}

/**
 * This is a simple merge sort algorithm
 *
 * @param array $arr
 * @return array
 */
function merge_sort(array $arr): array
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

    if (count($arr) == 1) return $arr;
    $mid = count($arr) / 2;
    $left = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);
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
function heap_sort(Heap $heap): array
{
    $size = $heap->getSize();
    // "shift" all nodes, except the lowest level as it has no children
    for ($j = (int)($size/2) - 1; $j >= 0; $j--) $heap->bubbleDown($j);

    // sort the heap
    for ($j = $size-1; $j >= 0; $j--) {
        $biggestNode = $heap->remove();
        // use same nodes array for sorted elements
        $heap->insertAt($j, $biggestNode);
    }
    return $heap->asArray();
}
