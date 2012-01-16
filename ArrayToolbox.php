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
	
	public static function xor_values($left, $right) {
		if (!self::isIterable($left) || !self::isIterable($right)) {
			throw new Exception("Can't loop over the variables given");
		}
		$L = self::and_not_values($left, $right);
		$R = self::and_not_values($right, $left);
		$LuR = array_merge($L, $R);
		$LnR = array_intersect($L, $R);
		return self::and_not_values($LuR, $LnR);
	}
	
	public static function xor_keys($left, $right) {
		if (!self::isIterable($left) || !self::isIterable($right)) {
			throw new Exception("Can't loop over the variables given");
		}
		return 
			self::and_not_keys(
				self::or_keys($left, $right), // Ab, aB, AB.
				self::and_keys($left, right)  // AB
			);
	}
	
	public static function and_not_keys($left, $right) {
		if (!self::isIterable($left) || !self::isIterable($right)) {
			throw new Exception("Can't loop over the variables given");
		}
		$known = array();
		foreach ($right as $key => $val) {
			$known[] = $key;
			$val = $val;
		}
		$result = array();
		foreach ($left as $key => $val) {
			if (!in_array($key, $known)) {
				$result[$key] = $val;
			}
		}
		
		return $result;
	}
	
	public static function and_not_values($left, $right) {
		if (!self::isIterable($left) || !self::isIterable($right)) {
			throw new Exception("Can't loop over the variables given");
		}
		$known = array();
		foreach ($right as $val) {
			$known[] = $val;
		}
		$result = array();
		foreach ($left as $key => $val) {
			if (!in_array($val, $known)) {
				$result[$key] = $val;
			}
		}
		
		return $result;
	}
	
	public static function and_values($left, $right) {
		if (!self::isIterable($left) || !self::isIterable($right)) {
			throw new Exception("Can't loop over the variables given");
		}
		$known = array();
		foreach ($right as $val) {
			$known[] = $val;
		}
		$result = array();
		foreach ($left as $key => $val) {
			if (in_array($val, $known)) {
				$result[$key] = $val;
			}
		}
		
		return $result;
	}
	
	public static function and_keys($left, $right) {
		if (!self::isIterable($left) || !self::isIterable($right)) {
			throw new Exception("Can't loop over the variables given");
		}
		$known = array();
		foreach ($right as $key => $val) {
			$known[] = $key;
		}
		$result = array();
		foreach ($left as $key => $val) {
			if (in_array($key, $known)) {
				$result[$key] = $val;
			}
		}
		
		return $result;
	}
	
	public static function or_keys($left, $right) {
		$result = array();
		foreach ($left as $key => $val) {
			$result[$key] = $val;
		}
		foreach ($right as $key => $val) {
			$result[$key] = $val;
		}
		return $result;
	}
	
	public static function or_values($left, $right) {
		$result = array();
		foreach ($left as $key => $val) {
			if (!in_array($val, $result)) {
				$result[$key] = $val;
			}
		}
		foreach ($right as $key => $val) {
			if (!in_array($val, $result)) {
				if (!isset($result[$key])) {
					$result[$key] = $val;
				} else {
					$result[] = $val;
				}
			}
		}
		return $result;
	}
	
}

/*

$left = array("cat" => 1, "dog" => 2, "mouse" => 3);
$right = array("car" => 2, "truck" => 3, "moped" => 4);
var_dump(ArrayToolbox::and_values($left, $right));
var_dump(ArrayToolbox::or_values($left, $right));
var_dump(ArrayToolbox::and_not_values($left, $right));
var_dump(ArrayToolbox::xor_values($left, $right));

$petOwners = array(
	array(
		"name" => "robert",
		"pet" => array(
			"type" => "cat",
			"name" => "Mr Whiskers"
		)
	),
	array(
		"name" => "susan",
		"pet" => array(
			"type" => "dog",
			"name" => "fido"
		)
	),
	array(
		"name" => "paul",
		"pet" => "none"
	)
);

// expecting: array("Mr Whiskers", "Fido", NULL)
var_dump(ArrayToolbox::collect($petOwners, array("pet", "name")));

$productIDs = array(
	1234,
	5678,
);
$productNames = array(
	"Toaster",
	"Kettle",
);

// expecting: array(0 => array("ID" => 1234, "Name" => "Toaster"), 1 => array("ID" => 5678, "Name" => "Kettle"))
var_dump(ArrayToolbox::rotateArray(array("ID" => $productIDs, "Name" => $productNames)));

 */