<?php

if ( ! function_exists('is_assoc_array'))
{
	/**
	 * Determine whether an array is associative.
	 *
	 * @param  array  $array
	 * @return boolean
	 */
	function is_assoc_array(array $array)
	{
		return array_keys($array) !== range(0, count($array) - 1);
	}
}
