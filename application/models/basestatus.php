<?php namespace App\Models;

use DB, Cache, Auth;

class BaseStatus extends BaseReference {

	/**
	 * Получаем массив возможных переходов
	 *
	 * @return array
	 */
	public function get_moves()
	{
		$moves = $this->get_attribute('moves');

		if(!empty($moves))
			return $moves;

		$moves = array();

		// если супер админ возвращаем все переходы
		if(Auth::user()->is_superuser())
		{
			$query_moves = DB::query('SELECT * FROM '. static::$table .'_edges ed INNER JOIN '. static::$table .' st ON ed.to = st.id WHERE ed.from = ?', array($this->id));
		}

		// получаем переходы для обычных пользователей
		else
		{
			// получаем права
			$bindings = Auth::user()->permissions['permissions'];

			// добавляем в начало текущий статус
			array_unshift($bindings, $this->id);

			// возвращаем доступные переходы в зависимости от прав аккаунта
			$query_moves = DB::query('SELECT * FROM '. static::$table .'_edges ed INNER JOIN '. static::$table .' st ON ed.to = st.id WHERE ed.from = ? AND ed.permission IN ('. substr(str_repeat('?,', count($bindings) - 1), 0, -1) .')', $bindings);
		}

		/**
		 * Формируем массив где ключи это переходы
		 */
		foreach ($query_moves as $move)
			$moves[$move->to] = $move;

		// заполяем свойство (вызовется магическая set_attribute)
		$this->moves = $moves;

		return $moves;
	}

	/**
	 * Получаем массив элементов ссылающихся на текущий независимо от прав
	 *
	 * @return array
	 */
	public function get_tails()
	{
		$tails = $this->get_attribute('tails');

		if(!empty($tails))
			return $tails;

		$tails = DB::query('SELECT * FROM '. static::$table .'_edges ed INNER JOIN '. static::$table .' st ON ed.from = st.id WHERE ed.to = ?', array($this->id));

		// заполяем свойство (вызовется магическая set_attribute)
		$this->tails = $tails;

		return $tails;
	}

	/**
	 * Проверяем есть ли переходы со статуса
	 *
	 * @return bool
	 */
	public function is_final()
	{
		return count($this->moves) == 0;
	}

	/**
	 * Проверяем есть ли переходы на этот статус
	 *
	 * @return bool
	 */
	public function is_alone()
	{
		return count($this->tails) == 0;
	}

	/**
	 * Проверяем возможность перехода на статус
	 *
	 * @param Status
	 * @return bool
	 */
	public function can_move($to_status)
	{
		return in_array($to_status->id, array_keys($this->moves));
	}

	/**
	 * Добавляем переходы с текущего статуса
	 *
	 * @return bool
	 */
	public function add_moves($params)
	{
		if(empty($params) || !is_array($params))
			return false;

		if(empty($params['moves']) || empty($params['perms']) || !is_array($params['moves']) || !is_array($params['perms']))
			return false;

		$values = '';
		$bindings = array();

		foreach ($params['moves'] as $to)
		{
			$values .= '(?, ?, ?),';
			array_push($bindings, $this->id, $to, $params['perms'][$to]);
		}

		return DB::query('INSERT INTO '. static::$table .'_edges (`from`, `to`, `permission`) VALUES '. substr($values, 0, -1), $bindings);
	}

	/**
	 * Удаляем переходы принадлежащие статусу
	 *
	 * @return bool
	 */
	public function delete_moves()
	{
		return DB::query('DELETE FROM '. static::$table .'_edges WHERE `from` = ?', array($this->id));
	}


	/**
	 * Получаем начальный статус прямым запросом чтобы не создавать объект AR
	 * 
	 * @return mixed
	 */
	public static function query_root()
	{
		return DB::first('SELECT * FROM '. static::$table .' WHERE is_root = 1');
	}

	/**
	 * Получаем начальный статус из кеша, если его нету то сохраняем в кеше
	 */
	public static function cget_root($ttl = 0)
	{
		$key = get_called_class(). '_root_status';

		$result = Cache::get($key);
		
		if ($result)
			return $result;
		
		$result = static::query_root();
		
		Cache::put($key, $result, $ttl);
		
		return $result;
	}

	/**
	 * Сбрасываем кеш характерный для статуса затем передаем управление родительскому классу BaseReference
	 */
	public static function cflush_all()
	{
		$keys_prefix = array('_root_status');

		foreach ($keys_prefix as $prefix)
			Cache::forget(get_called_class() . $prefix);

		return parent::cflush_all();
	}
}
