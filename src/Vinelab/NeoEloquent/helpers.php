<?php namespace Vinelab\NeoEloquent;

class Helpers {

	/**
	 * Determine whether an array is associative.
	 *
	 * @param  array  $array
	 * @return boolean
	 */
	public static function is_assoc_array($array)
	{
		return is_array($array) and array_keys($array) !== range(0, count($array) - 1);
	}
}
