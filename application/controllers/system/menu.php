<?php

use App\Models\System\Menu;

class System_Menu_Controller extends System_Controller {

	public $view = 'system.menu';
	public $uri = 'system/menu';

	/**
	 * Конструктор
	 */
	public function __construct()
	{
		// cбрасываем кеш после редактирования элементов
		$this->filter('after', 'single_cache_flush', array('App\Models\System\Menu'))->on('post');

		// Сбрасываем кеш хедер меню
		$this->filter('after', 'cache_header_flush', array(MenuHandler::$cache_key . 'header'))->on('post');

		parent::__construct();
	}

	/**
	 * Method: GET
	 *
	 * Отрисовываем список регионов
	 */
	public function get_index()
	{
		return View::make($this->view .'.index', array('items' => Menu::cget_flat_tree()));
	}

	/**
	 * Method: GET
	 *
	 * Страница добавления региона
	 */
	public function get_create()
	{
		return View::make($this->view .'.form', array('flat_tree' => Menu::cget_flat_tree(), 'create' => true));
	}

	/**
	 * Method: POST
	 *
	 * Добавление региона
	 */
	public function post_create()
	{
		$rules = array(
			'title'  => 'required|max:50',
			'route'  => 'required|max:50|unique:'. Menu::$table,
			'active' => 'integer',
			'parent' => 'required|integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$parent = Menu::find((int) Input::get('parent'));

			if ($parent)
			{
				$fields = array(
					'title'  => Input::get('title'),
					'route'  => Input::get('route'),
					'active' => Input::get('active'),
				);

				$new_menu = new Menu($fields);

				// making child and save model
				$new_menu->child_of($parent);

				if ($new_menu !== false)
					return Redirect::to($this->uri);
				else
					$validator->errors->add('model_create_fail', true);
			} else
				$validator->errors->add('parent_not_found', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/create')->with('errors', $validator->errors)->with_input();
	}


	/**
	 * Method: GET
	 *
	 * Страница редактирования региона
	 */
	public function get_edit($object_id = null)
	{
		$object_id = (int) $object_id;

		if(!$object_id)
			return Response::error('404');

		$menu = Menu::find($object_id);

		if(!$menu)
			return Response::error('404');

		return View::make($this->view .'.form', array('menu' => $menu, 'flat_tree' => Menu::cget_flat_tree(), 'create' => false));
	}

	/**
	 * Method: POST
	 *
	 * Редактирование региона
	 */
	public function post_edit()
	{
		$rules = array(
			'id'     => 'required|integer',
			'title'  => 'required|max:50',
			'route'  => 'required|max:50|unique:'. Menu::$table .',route,'. Input::get('id'),
			'active' => 'integer',
			'parent' => 'required|integer|different:id',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$menu = Menu::find(Input::get('id'));

			$parent = Menu::find(Input::get('parent'));

			if ($menu && $parent)
			{
				$fields = array(
					'title'  => Input::get('title'),
					'route'  => Input::get('route'),
					'active' => Input::get('active'),
				);

				$menu->fill($fields);

				// Если изменили родителя
				if(!$menu->is_child_of($parent))
				{
					// Делаем ребенком выбранного родителя
					$menu->child_of($parent);
				}

				if ($menu->save())
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
	 * Удаление региона
	 */
	public function post_delete()
	{
		$rules = array(
			'id' => 'required|integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$menu = Menu::find(Input::get('id'));

			if ($menu)
			{
				if ($menu->real_delete_from_tree())
					return Redirect::to($this->uri);
				else
					$validator->errors->add('model_delete_fail', true);
			} else
				$validator->errors->add('model_delete_fail', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/edit/'. Input::get('id'))->with('errors', $validator->errors)->with_input();
	}
}