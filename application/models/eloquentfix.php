<?php namespace App\Models;

use DB, Event;

class EloquentFix extends \Eloquent {

	public static $timestamps = true;

	public function connection(){
		return static::$connection;
	}

	public static function read_mode(){
		static::$connection = 'mysql_read';
	}

	public static function write_mode(){
		static::$connection = 'mysql_write';
	}

	/**
	 * Получаем связанные модели many-to-many не создавая АР модели
	 *
	 * @param string $table
	 * @param string $fkey
	 * @param string $key
	 * @return array
	 */
	public function pivot_relation_array($table, $fkey, $key = null)
	{
		if(empty($key))
			$key = strtolower(substr(strrchr(get_called_class(), '\\'), 1)) .'_'. static::$key;
		
		$items = DB::query('SELECT '. $fkey .' FROM '. $table .' WHERE '. $key .' = ?', array($this->id));

		if(empty($items))
			return array();

		$relations = array();
		foreach ($items as $item)
			$relations[] = $item->{$fkey};

		return $relations;
	}

	/**
	 * Перемещение по статусам моделей
	 *
	 * Внимание! 
	 * Метод действует для моделей у который есть свзять с моделью статусов
	 *
	 * @param Status $move_to
	 * @return bool
	 */
	public function status_shift($move_to)
	{
		if ($this->status->can_move($move_to))
		{
			$from = $this->status;

			/**
			 * Race condition!
			 *
			 * Если в эту секцию попадут несколько процессов то получится каша в истории переходов и победит последний процесс.
			 * Для реализации атомарности мы обновим запись по ключю и текущему статусу.
			 * В таком случае первый процесс отработает а остальные получат false. Запись в истории будет одна.
			 */

			$affected = DB::query('UPDATE '. $this::$table .' SET `status_id` =  ? WHERE `id` = ? AND `status_id` = ?', array($move_to->id, $this->id, $this->status_id));

			if ($affected > 0)
			{
				Event::fire('system.status_shift', array($this, $from, $move_to));

				return true;
			} else
				return false;
		}

		return false;
	}
}