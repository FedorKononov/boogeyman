<?php

use App\Models\HDL\DeviceType, App\Models\HDL\Command;

class Hdl_Command_Controller extends Hdl_Controller {

	public $view = 'hdl.command';
	public $uri = 'hdl/command';

	/**
	 * Method: GET
	 *
	 * Отрисовываем список команд
	 */
	public function get_index()
	{
		$items = Command::with(array('device_type'))->paginate(Config::get('application.paginate'));

		return View::make($this->view .'.index', array('items' => $items));
	}

	/**
	 * Method: GET
	 *
	 * Страница добавления новой команды
	 */
	public function get_create()
	{
		return View::make($this->view .'.form', array('create' => true, 'device_types' => DeviceType::all()));
	}

	/**
	 * Method: POST
	 *
	 * Добавление новой команды
	 */
	public function post_create()
	{
		$rules = array(
			'title'        => 'required|max:50',
			'code'         => 'required|min:3|max:50|match:/^[_a-z]+$/|unique:'. Command::$table,
			'operate_code' => 'required',
			'device_type'  => 'required|integer',
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
					'operate_code'   => Input::get('operate_code'),
					'device_type_id' => $device_type->id,
				);

				$new_command = Command::create($fields);

				if ($new_command !== false)
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
	 * Страница редактирования команды
	 */
	public function get_edit($object_id = null)
	{
		$object_id = (int) $object_id;

		if (!$object_id)
			return Response::error('404');

		$command = Command::find($object_id);

		if (!$command)
			return Response::error('404');

		return View::make($this->view .'.form', array('command' => $command, 'create' => false, 'device_types' => DeviceType::all()));
	}

	/**
	 * Method: POST
	 *
	 * Редактирование команды
	 */
	public function post_edit()
	{
		$rules = array(
			'id'           => 'required|integer',			
			'title'        => 'required|max:50',
			'code'         => 'required|min:3|max:50|match:/^[_a-z]+$/|unique:'. Command::$table .',code,'. Input::get('id'),
			'operate_code' => 'required',
			'device_type'  => 'required|integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$device_type = DeviceType::find(Input::get('device_type'));

			if ($device_type)
			{
				$command = Command::find(Input::get('id'));

				if ($command)
				{
					$fields = array(
						'title'          => Input::get('title'),
						'code'           => Input::get('code'),
						'operate_code'   => Input::get('operate_code'),
						'device_type_id' => $device_type->id,
					);

					$command->fill($fields);

					if ($command->save())
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
	 * Удаление команды
	 */
	public function post_delete()
	{
		$rules = array(
			'id' => 'required|integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$command = Command::find(Input::get('id'));

			if ($command)
			{
				$command->params()->delete();

				if ($command->delete())
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