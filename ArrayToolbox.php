<?php

class ArrayToolbox 
{
	
	/**
	 * Returns true if you can pass the variable to a foreach loop, or similar.
	 *
	 * @param mixed $array
	 * @return boolean
	 */
	public static function isIterable($array) {
		return 
			is_array($array) 
			|| $array instanceof Iterator;
	}
	
	/**
	 * Returns true if the variable can be `count()`ed.
	 *
	 * @param mixed $array
	 * @return boolean
	 */
	public static function isCountable($array) {
		return 
			is_array($array)
			|| $array instanceof Countable;
	}
	
	/**
	 * Returns true if you can access keys within the variable like an array.
	 * 
	 * eg: $array["keyname"]
	 *
	 * @param mixed $array
	 * @return boolean
	 */
	public static function isArrayAccessible($array) {
		return 
			is_array($array) 
			|| $array instanceof ArrayAccess;
	}
	
	/**
	 * Returns true if a variable is array-like.
	 * Objects such as ArrayObjects will not be considered 'arrays' by the 
	 * PHP builtin function `is_array()`. `isArrayLike()` will return true for 
	 * both arrays and objects that can be treated as arrays.
	 *
	 * @param mixed $array
	 * @return boolean
	 */
	public static function isArrayLike($array) {
		return 
			is_array($array)
			|| $array instanceof ArrayObject
			|| (
				self::isIterable($array) 
				&& self::isArrayAccessible($array) 
				&& self::isCountable($array)
			);
	}
	
	/**
	 * Pulls keys out from a sub-array in a multidimensional array.
	 * If an array of keys is passed in, will drill down repeatedly using 
	 * the first key at the root of the array, drilling down towards the leaves.
	 * 
	 * example usage:
	 * collect(
	 *   array(
	 *     "alice" => array(
	 *       "pet" => array(
	 *         "name" => "MrWhiskers",
	 *         "type" => "cat"
	 *       )
	 *     ),
	 *     "ben" => array(
	 *       "pet" => array(
	 *         "name" => "Blub",
	 *         "type" => "goldfish"
	 *       )
	 *     ),
	 *     "chris" => array(
	 *       "pet" => array(
	 *         "type" => "none"
	 *       )
	 *     )
	 *   ), 
	 *   array(
	 *     "pet", 
	 *     "name"
	 *   )
	 * );
	 * 
	 * example output:
	 * array(
	 *   "MrWhiskers",
	 *   "Blub",
	 *   NULL
	 * )
	 *
	 * @param array $array
	 * @param string|array $key
	 * 
	 * @return array
	 */
	public static function collect($array, $key) {
		$myKey = $key;
		if (self::isCountable($key) && count($key)) {
			$myKey = array_shift($key);
		}
		
		$results = array();
		foreach ($array as $index => $value) {
			if (self::isArrayAccessible($value) && isset($value[$myKey])) {
				$results[] = $value[$myKey];
			} else {
				$results[] = null;
			}
		}
		
		if (self::isCountable($key) && count($key)) {
			return self::collect($results, $key);
		}
		
		return $results;
	}
	
	/**
	 * Rotates a multidimensional array.
	 * Rotates so $a[row][col] becomes $a[col][row].
	 *
	 * @param array $array
	 * @return array
	 */
	public static function rotateArray($array) {
		$result = array();
		foreach ($array as $x => $row) {
			foreach ($row as $y => $val) {
				if (!isset($result[$y])) {
					$result[$y] = array();
				}
				$result[$y][$x] = $val;
			}
		}
		return $result;
	}
	
}