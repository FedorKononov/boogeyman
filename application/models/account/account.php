<?php namespace App\Models\Account;

use App\Models\EloquentFix, Hash, Cache, Config, Auth;

class Account extends EloquentFix {

	public static $table = 'account';
	public static $group_pivot_table = 'account_account_group';

	public static $permission_cache_key = 'account_permissions_';

	public static $connection = 'mysql_read';

	public static $hidden = array('password');

	public function status()
	{
		return $this->belongs_to('App\Models\Account\Status', 'status_id');
	}

	public function groups()
	{
		return $this->has_many_and_belongs_to('App\Models\Account\Group', self::$group_pivot_table);
	}

	public function set_password($password)
	{
		$this->set_attribute('password', Hash::make($password));
	}

	/**
	 * Загружаем права пользователя
	 * 
	 * @return array
	 */
	public function get_permissions()
	{
		$account_perms = $this->get_attribute('permissions');

		// если свойтво уже заполняли то возвращаем его
		if (!empty($account_perms))
			return $account_perms;

		$user_key = self::$permission_cache_key . $this->id;
		$header_key = self::$permission_cache_key .'header';

		// Получаем кеш хедер в котором будут все закешированные аккаунты
		$cache_header = Cache::get($header_key, array($user_key => 0));

		// Если пользователь есть в кеш хедере значит эти права актуальны
		if (!empty($cache_header[$user_key]))
			$cache_data = Cache::get($user_key);

		//  Если пользователя нету в кеш хедере или нету кеша прав в целом то считам что кеша нету и его надо генерить заного.
		if (empty($cache_data))
		{
			$account = Account::with(array('groups', 'groups.permissions'))
						->where(Account::key(), '=', $this->id)
						->first();

			$cache_data = array('groups' => array(), 'permissions' => array());

			foreach ($account->groups as $group)
			{
				$cache_data['groups'][$group->id] = $group->code;

				foreach ($group->permissions as $permission)
					$cache_data['permissions'][$permission->id] = $permission->code;
			}

			// Добавляем аккаунт в кеш хедер
			$cache_header[$user_key] = 1;

			Cache::put($user_key, $cache_data, 0);
			Cache::put($header_key, $cache_header, 0);
		}
		
		// заполяем свойство (вызовется магическая set_attribute)
		$this->permissions = $cache_data;

		return $cache_data;
	}

	/**
	 * Доступен ли доступ аккаунту
	 * 
	 * @param  array|string
	 * @return bool
	 */
	public static function can($access)
	{
		$access = !is_array($access) ? array($access) : $access;

		$account = Auth::user();

		if ($account)
		{
			if ($account->is_superuser())
				return true;

			if (array_intersect($access, $account->permissions['permissions']))
				return true;
		}
		// проверяем доступ для гостевой группы
		else
		{
			$guest_perms = self::guest_permissions();

			if (array_intersect($access, $guest_perms['permissions']))
				return true;
		}

		return false;
	}

	/**
	 * Загружаем права гостей
	 * 
	 * @return static array
	 */
	public static function guest_permissions()
	{
		// права для пользователя получаем 1 раз и пишем в статик переменную
		static $cache_data = array();

		if ($cache_data)
			return $cache_data;

		$user_key = self::$permission_cache_key . '0';
		$header_key = self::$permission_cache_key .'header';

		// Получаем кеш хедер в котором будут все закешированные аккаунты
		$cache_header = Cache::get($header_key, array($user_key => 0));

		// Если пользователь есть в кеш хедере значит эти права актуальны
		if (!empty($cache_header[$user_key]))
			$cache_data = Cache::get($user_key);

		//  Если пользователя нету в кеш хедере или нету кеша прав в целом то считам что кеша нету и его надо генерить заного.
		if (empty($cache_data))
		{
			// для гостя получаем одноименну группы и её права
			$group = Group::with(array('permissions'))
						->where('code', '=', Config::get('application.guest_group'))
						->first();

			$cache_data = array('groups' => array(), 'permissions' => array());

			if ($group)
			{
				$cache_data['groups'][$group->id] = $group->code;

				foreach ($group->permissions as $permission)
					$cache_data['permissions'][$permission->id] = $permission->code;

				// Добавляем аккаунт в кеш хедер
				$cache_header[$user_key] = 1;
			}

			Cache::put($user_key, $cache_data, 0);
			Cache::put($header_key, $cache_header, 0);
		}

		return $cache_data;
	}

	/**
	 * Является ли пользователь учатником группы супер админов
	 * 
	 * @return bool
	 */
	public function is_superuser()
	{
		return in_array(Config::get('application.super_group'), $this->permissions['groups']);
	}

	/**
	 * Переопределяем метод сохранения для исключения неявных полей модели
	 * 
	 * @return bool
	 */
	public function save()
	{
		unset($this->attributes['permissions']);
		unset($this->attributes['groups']);
		
		return parent::save();
	}


	/**
	 * Сбрасываем пользовательский кеш
	 *
	 * @param int $account_id
	 * @return bool
	 */
	public static function cache_flush($account_id){

		Cache::forget(self::$permission_cache_key . $account_id);

		return true;
	}
}