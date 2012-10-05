<?php

use App\Models\Account\Permission, App\Models\Account\Account;

class System_Permission_Controller extends System_Controller {

	public $view = 'system.permission';
	public $uri = 'system/permission';

	/**
	 * Конструктор
	 */
	public function __construct()
	{
		// Сбрасываем кеш после редактирования элементов
		$this->filter('after', 'single_cache_flush', array('App\Models\Account\Permission'))->on('post');

		// Сбрасываем кеш хедер доступов
		$this->filter('after', 'cache_header_flush', array(Account::$permission_cache_key . 'header'))->only(array('edit', 'delete'))->on('post');

		// Сбрасываем кеш хедер меню
		$this->filter('after', 'cache_header_flush', array(MenuHandler::$cache_key . 'header'))->only(array('edit', 'delete'))->on('post');

		parent::__construct();
	}

	/**
	 * Method: GET
	 *
	 * Отрисовываем список допусков
	 */
	public function get_index()
	{
		$permissions = Permission::paginate(Config::get('application.paginate'));

		return View::make($this->view .'.index', array('items' => $permissions));
	}

	/**
	 * Method: GET
	 *
	 * Страница добавления нового допуска
	 */
	public function get_create()
	{
		return View::make($this->view .'.form', array('create' => true));
	}

	/**
	 * Method: POST
	 *
	 * Добавление нового допуска
	 */
	public function post_create()
	{
		$rules = array(
			'title'        => 'required|max:50',
			'code'         => 'required|min:3|max:50|match:/^[@._a-z]+$/|unique:'. Permission::$table,
			'description'  => 'max:255',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$fields = array(
				'title'        => Input::get('title'),
				'code'         => Input::get('code'),
				'description'  => Input::get('description'),
			);

			$new_permission = Permission::create($fields);

			if ($new_permission !== false)
				return Redirect::to($this->uri);
			else
				$validator->errors->add('model_create_fail', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/create')->with('errors', $validator->errors)->with_input();
	}


	/**
	 * Method: GET
	 *
	 * Страница редактирования допуска
	 */
	public function get_edit($object_id = null)
	{
		$object_id = (int) $object_id;

		if(!$object_id)
			return Response::error('404');

		$permission = Permission::find($object_id);

		if(!$permission)
			return Response::error('404');

		return View::make($this->view .'.form', array('permission' => $permission, 'create' => false));
	}

	/**
	 * Method: POST
	 *
	 * Редактирование допуска
	 */
	public function post_edit()
	{
		$rules = array(
			'id'           => 'required|integer',
			'title'        => 'required|max:50',
			'code'         => 'required|min:3|max:50|match:/^[@._a-z]+$/|unique:'. Permission::$table .',code,'. Input::get('id'),
			'description'  => 'max:255',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$permission = Permission::find(Input::get('id'));

			if($permission)
			{
				$fields = array(
					'title'        => Input::get('title'),
					'code'         => Input::get('code'),
					'description'  => Input::get('description'),
				);

				$permission->fill($fields);

				if($permission->save())
					return Redirect::to($this->uri);
				else
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
	 * Удаление допуска
	 */
	public function post_delete()
	{
		$rules = array(
			'id' => 'required|integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$permission = Permission::find(Input::get('id'));

			if($permission)
			{
				// удаляем связи с группами
				$permission->groups()->delete();

				if($permission->delete())
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