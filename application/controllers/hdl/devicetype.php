<?php

use App\Models\HDL\DeviceType;

class Hdl_DeviceType_Controller extends Hdl_Controller {

	public $view = 'hdl.devicetype';
	public $uri = 'hdl/devicetype';

	/**
	 * Method: GET
	 *
	 * Отрисовываем список типов устройств
	 */
	public function get_index()
	{
		$items = DeviceType::paginate(Config::get('application.paginate'));

		return View::make($this->view .'.index', array('items' => $items));
	}

	/**
	 * Method: GET
	 *
	 * Страница добавления нового типа устройства
	 */
	public function get_create()
	{
		return View::make($this->view .'.form', array('create' => true));
	}

	/**
	 * Method: POST
	 *
	 * Добавление нового типа устройства
	 */
	public function post_create()
	{
		$rules = array(
			'title'  => 'required|max:50',
			'code'   => 'required|min:3|max:50|match:/^[_a-z]+$/|unique:'. DeviceType::$table,
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$fields = array(
				'title' => Input::get('title'),
				'code'  => Input::get('code'),
			);

			$new_device_type = DeviceType::create($fields);

			if ($new_device_type !== false)
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
	 * Страница редактирования типа устройства
	 */
	public function get_edit($object_id = null)
	{
		$object_id = (int) $object_id;

		if (!$object_id)
			return Response::error('404');

		$device_type = DeviceType::find($object_id);

		if (!$device_type)
			return Response::error('404');

		return View::make($this->view .'.form', array('device_type' => $device_type, 'create' => false));
	}

	/**
	 * Method: POST
	 *
	 * Редактирование типа устройства
	 */
	public function post_edit()
	{
		$rules = array(
			'id'     => 'required|integer',
			'title'  => 'required|max:50',
			'code'   => 'required|min:3|max:50|match:/^[_a-z]+$/|unique:'. DeviceType::$table .',code,'. Input::get('id'),
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$device_type = DeviceType::find(Input::get('id'));

			if ($device_type)
			{
				$fields = array(
					'title' => Input::get('title'),
					'code'  => Input::get('code'),
				);

				$device_type->fill($fields);

				if ($device_type->save())
					return Redirect::to($this->uri);
				else
					$validator->errors->add('model_edit_fail', true);
			} else
				$validator->errors->add('model_edit_fail', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/edit/'. Input::get('id'))->with('errors', $validator->errors)->with_input();
	}


	/**
	 * Method: POST
	 *
	 * Удаление типа устройства
	 */
	public function post_delete()
	{
		$rules = array(
			'id' => 'required|integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$device_type = DeviceType::find(Input::get('id'));

			if ($device_type)
			{
				if (count($device_type->commands()->get()) > 0)
					$validator->errors->add('model_delete_fail', true);
				else if (count($device_type->devices()->get()) > 0)
					$validator->errors->add('model_delete_fail', true);
				else if ($device_type->delete())
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