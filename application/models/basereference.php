<?php namespace App\Models;

use App\Models\EloquentFix, Cache, DB;

class BaseReference extends EloquentFix {

	/**
	 * Получаем список всех элементов справочника не загружая их в модель AR
	 */
	public static function query_all(){
		return DB::query('SELECT * FROM '. static::$table .' ORDER BY weight ASC');
	}

	/**
	 * Получаем список активных элементов справочника не загружая их в модель AR
	 */
	public static function query_active(){
		return DB::query('SELECT * FROM '. static::$table .' WHERE `active` = 1 ORDER BY weight ASC');
	}

	/**
	 * Получаем дефолтный элемент справочника не загружая его в модель AR
	 */
	public static function query_default(){
		return DB::first('SELECT * FROM '. static::$table .' WHERE `default` = 1 ORDER BY weight ASC');
	}


	/**
	 * Получаем список всех элементов из кеша, если их нету то сохраняем в кеше
	 */
	public static function cget_all($ttl = 0){
		$key = get_called_class(). '_all';

		$result = Cache::get($key);
		
		if ($result)
			return $result;
		
		$result = static::query_all();

		Cache::put($key, $result, $ttl);
		
		return $result;
	}


	/**
	 * Получаем список активных элементов из кеша, если их нету то сохраняем в кеше
	 */
	public static function cget_active($ttl = 0){
		$key = get_called_class(). '_active';

		$result = Cache::get($key);
		
		if ($result)
			return $result;

		$result = static::query_active();

		Cache::put($key, $result, $ttl);
		
		return $result;
	}


	/**
	 * Получаем дефолтный элемент из кеша, если его нету то сохраняем в кеше
	 */
	public static function cget_default($ttl = 0){
		$key = get_called_class(). '_default';

		$result = Cache::get($key);
		
		if ($result)
			return $result;
		
		$result = static::query_default();
		
		Cache::put($key, $result, $ttl);
		
		return $result;
	}

	/**
	 * Сбрасываем весь кеш для вызываемой модели
	 */
	public static function cflush_all(){
		$keys_prefix = array('_default', '_active', '_all');

		foreach ($keys_prefix as $prefix)
			Cache::forget(get_called_class() . $prefix);

		return true;
	}

}
