<?php

use App\Models\Account\Permission, App\Models\Account\Account, App\Models\Account\Group;

class System_Group_Controller extends System_Controller {

	public $view = 'system.group';
	public $uri = 'system/group';

	/**
	 * Конструктор
	 */
	public function __construct()
	{
		// Сбрасываем кеш после редактирования элементов
		$this->filter('after', 'single_cache_flush', array('App\Models\Account\Group'))->on('post');

		// Сбрасываем кеш хедер доступов
		$this->filter('after', 'cache_header_flush', array(Account::$permission_cache_key . 'header'))->only(array('edit', 'delete'))->on('post');

		// Сбрасываем кеш хедер меню
		$this->filter('after', 'cache_header_flush', array(MenuHandler::$cache_key . 'header'))->only(array('edit', 'delete'))->on('post');

		parent::__construct();
	}

	/**
	 * Method: GET
	 *
	 * Отрисовываем список групп
	 */
	public function get_index()
	{
		$groups = Group::paginate(Config::get('application.paginate'));

		return View::make($this->view .'.index', array('items' => $groups));
	}

	/**
	 * Method: GET
	 *
	 * Страница добавления новой группы
	 */
	public function get_create()
	{
		return View::make($this->view .'.form', array('permissions' => Permission::cget_all(), 'create' => true));
	}

	/**
	 * Method: POST
	 *
	 * Добавление новой группы
	 */
	public function post_create()
	{
		$rules = array(
			'title'       => 'required|max:50',
			'code'        => 'required|min:3|max:50|match:/^[._a-z]+$/|unique:'. Group::$table,
			'permissions' => 'required',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$fields = array(
				'title' => Input::get('title'),
				'code'  => Input::get('code'),
			);

			$new_group = Group::create($fields);

			if($new_group !== false)
			{
				$prems_attach = $new_group->permissions()->sync(Input::get('permissions'));

				if($prems_attach !== false)
					return Redirect::to($this->uri);
				else
					$validator->errors->add('model_create_fail', true);
			} else
				$validator->errors->add('model_create_fail', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/create')->with('errors', $validator->errors)->with_input();
	}

	/**
	 * Method: GET
	 *
	 * Страница редактирования группы
	 */
	public function get_edit($object_id = null)
	{
		$object_id = (int) $object_id;

		if(!$object_id)
			return Response::error('404');

		$group = Group::find($object_id);

		if(!$group)
			return Response::error('404');

		// подготавливаем доступы к удобному виду для работы с формами и не создавая модель АР
		$permissions = DB::query('SELECT * FROM '. Permission::$group_pivot_table .' WHERE group_id = ?', array($group->id));

		$tmp = array();
		foreach ($permissions as $perm)
			$tmp[] = $perm->permission_id;

		$group->permissions = $tmp;

		return View::make($this->view .'.form', array('group' => $group, 'permissions' => Permission::cget_all(), 'create' => false));
	}

	/**
	 * Method: POST
	 *
	 * Редактирование группы
	 */
	public function post_edit()
	{
		$rules = array(
			'id'          => 'required|integer',
			'title'       => 'required|max:50',
			'code'        => 'required|min:3|max:50|match:/^[._a-z]+$/|unique:'. Group::$table .',code,'. Input::get('id'),
			'permissions' => 'required',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$group = Group::find(Input::get('id'));

			if($group)
			{
				$fields = array(
					'title' => Input::get('title'),
					'code'  => Input::get('code'),
				);

				$group->fill($fields);

				if($group->save())
				{
					$prems_attach = $group->permissions()->sync(Input::get('permissions'));

					if($prems_attach !== false)
						return Redirect::to($this->uri);
					else
						$validator->errors->add('model_edit_fail', true);
				} else
					$validator->errors->add('model_edit_fail', true);
			}else
				$validator->errors->add('model_edit_fail', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/edit/'. Input::get('id'))->with('errors', $validator->errors)->with_input();
	}

	/**
	 * Method: POST
	 *
	 * Удаление группы
	 */
	public function post_delete()
	{
		$rules = array(
			'id' => 'required|integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$group = Group::find(Input::get('id'));

			if($group)
			{
				// удаляем связи с аккаунтами
				$group->accounts()->delete();

				// удаляем связи с доступами
				$group->permissions()->delete();

				if($group->delete())
					return Redirect::to($this->uri);
				else
					$validator->errors->add('model_delete_fail', true);
			}else
				$validator->errors->add('model_delete_fail', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/edit/'. Input::get('id'))->with('errors', $validator->errors)->with_input();
	}
}