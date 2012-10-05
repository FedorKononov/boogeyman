<?php

class Validator extends Laravel\Validator {

	/**
	 * Validate that an attribute is an array of integers.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	public function validate_integer_array($attribute, $value)
	{
		if(!is_array($value))
			return false;

		foreach ($value as $val)
		{
			if (filter_var($val, FILTER_VALIDATE_INT) === false)
				return false;
		}

		return true;
	}

	/**
	 * Validate the size of an attribute is greater than a minimum value.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validate_arr_min($attribute, $value, $parameters)
	{
		if(is_array($value))
			return count($value) >= $parameters[0];

		return false;
	}

	/**
	 * Validate the size of an attribute is less than a maximum value.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validate_arr_max($attribute, $value, $parameters)
	{
		if(is_array($value))
			return count($value) <= $parameters[0];

		return false;
	}

	/**
	 * Replace all place-holders for the min rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replace_arr_min($message, $attribute, $rule, $parameters)
	{
		return str_replace(':min', $parameters[0], $message);
	}

	/**
	 * Replace all place-holders for the max rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replace_arr_max($message, $attribute, $rule, $parameters)
	{
		return str_replace(':max', $parameters[0], $message);
	}

	/**
	 * Validate that an attribute is an array of dates with given format.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validate_date_array($attribute, $value, $parameters)
	{
		if(!is_array($value))
			return false;

		if ((int) $parameters[1] >= 0)
			$offset_date = time() + ((int) $parameters[1] * 24 * 3600);

		$last_date   = time() + ((int) $parameters[2] * 24 * 3600);

		foreach ($value as $val)
		{
			if (!preg_match($parameters[0], $val))
				return false;

			$date = strtotime($val);

			if (!$date)
				return false;

			if (!empty($offset_date))
				if ($date < $offset_date)
					return false;

			if ($date > $last_date)
				return false;
		}

		return true;
	}

	/**
	 * Validate that an attribute is an array of values with given format.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validate_array_match($attribute, $value, $parameters)
	{
		if(empty($value))
			return true;

		foreach ($value as $val)
		{
			if (!preg_match($parameters[0], $val))
				return false;
		}

		return true;
	}
}