<?php

use App\Models\HDL\DeviceType, App\Models\HDL\Device;

class Hdl_Device_Controller extends Hdl_Controller {

	public $view = 'hdl.device';
	public $uri = 'hdl/device';

	/**
	 * Method: GET
	 *
	 * Отрисовываем список устройств
	 */
	public function get_index()
	{
		$items = Device::with(array('device_type'))->paginate(Config::get('application.paginate'));

		return View::make($this->view .'.index', array('items' => $items));
	}

	/**
	 * Method: GET
	 *
	 * Страница добавления нового устройства
	 */
	public function get_create()
	{
		return View::make($this->view .'.form', array('create' => true, 'device_types' => DeviceType::all()));
	}

	/**
	 * Method: POST
	 *
	 * Добавление нового устройства
	 */
	public function post_create()
	{
		$rules = array(
			'title'       => 'required|max:50',
			'code'        => 'required|min:3|max:50|match:/^[_a-z]+$/|unique:'. Device::$table,
			'subnet_id'   => 'required|integer',
			'device_id'   => 'required|integer',
			'device_type' => 'required|integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$device_type = DeviceType::find(Input::get('device_type'));

			if ($device_type)
			{
				$fields = array(
					'title'          => Input::get('title'),
					'code'           => Input::get('code'),
					'subnet_id'      => Input::get('subnet_id'),
					'device_id'      => Input::get('device_id'),
					'device_type_id' => $device_type->id,
				);

				$new_device = Device::create($fields);

				if ($new_device !== false)
					return Redirect::to($this->uri);
				else
					$validator->errors->add('model_create_fail', true);
			} else
				$validator->errors->add('device_type_find_fail', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/create')->with('errors', $validator->errors)->with_input();
	}


	/**
	 * Method: GET
	 *
	 * Страница редактирования устройства
	 */
	public function get_edit($object_id = null)
	{
		$object_id = (int) $object_id;

		if (!$object_id)
			return Response::error('404');

		$device = Device::find($object_id);

		if (!$device)
			return Response::error('404');

		return View::make($this->view .'.form', array('device' => $device, 'create' => false, 'device_types' => DeviceType::all()));
	}

	/**
	 * Method: POST
	 *
	 * Редактирование устройства
	 */
	public function post_edit()
	{
		$rules = array(
			'id'          => 'required|integer',			
			'title'       => 'required|max:50',
			'code'        => 'required|min:3|max:50|match:/^[_a-z]+$/|unique:'. Device::$table .',code,'. Input::get('id'),
			'subnet_id'   => 'required|integer',
			'device_id'   => 'required|integer',
			'device_type' => 'required|integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$device_type = DeviceType::find(Input::get('device_type'));

			if ($device_type)
			{
				$device = Device::find(Input::get('id'));

				if ($device)
				{
					$fields = array(
						'title'          => Input::get('title'),
						'code'           => Input::get('code'),
						'subnet_id'      => Input::get('subnet_id'),
						'device_id'      => Input::get('device_id'),
						'device_type_id' => $device_type->id,
					);

					$device->fill($fields);

					if ($device->save())
						return Redirect::to($this->uri);
					else
						$validator->errors->add('model_edit_fail', true);
				} else
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
	 * Удаление устройства
	 */
	public function post_delete()
	{
		$rules = array(
			'id' => 'required|integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$device = Device::find(Input::get('id'));

			if ($device)
			{
				if ($device->delete())
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