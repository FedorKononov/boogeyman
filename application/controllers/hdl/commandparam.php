<?php

use App\Models\HDL\CommandParam, App\Models\HDL\Command;

class Hdl_CommandParam_Controller extends Hdl_Controller {

	public $view = 'hdl.commandparam';
	public $uri = 'hdl/commandparam';

	/**
	 * Method: GET
	 *
	 * Отрисовываем список параметров команд
	 */
	public function get_index()
	{
		$items = CommandParam::with(array('command'))->paginate(Config::get('application.paginate'));

		return View::make($this->view .'.index', array('items' => $items));
	}

	/**
	 * Method: GET
	 *
	 * Страница добавления нового параметра команды
	 */
	public function get_create()
	{
		return View::make($this->view .'.form', array('create' => true, 'commands' => Command::all()));
	}

	/**
	 * Method: POST
	 *
	 * Добавление нового параметра команды
	 */
	public function post_create()
	{
		$rules = array(
			'title'   => 'required|max:50',
			'value'   => 'required',
			'command' => 'required|integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$command = Command::find(Input::get('command'));

			if ($command)
			{
				$fields = array(
					'title'      => Input::get('title'),
					'value'      => Input::get('value'),
					'command_id' => $command->id,
				);

				$new_command_param = CommandParam::create($fields);

				if ($new_command_param !== false)
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
	 * Страница редактирования нового параметра команды
	 */
	public function get_edit($object_id = null)
	{
		$object_id = (int) $object_id;

		if (!$object_id)
			return Response::error('404');

		$command_param = CommandParam::find($object_id);

		if (!$command_param)
			return Response::error('404');

		return View::make($this->view .'.form', array('commandparam' => $command_param, 'create' => false, 'commands' => Command::all()));
	}

	/**
	 * Method: POST
	 *
	 * Редактирование параметра команды
	 */
	public function post_edit()
	{
		$rules = array(
			'id'      => 'required|integer',
			'title'   => 'required|max:50',
			'value'   => 'required',
			'command' => 'required|integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$command = Command::find(Input::get('command'));

			if ($command)
			{
				$command_param = CommandParam::find(Input::get('id'));

				if ($command_param)
				{
					$fields = array(
						'title'      => Input::get('title'),
						'value'      => Input::get('value'),
						'command_id' => $command->id,
					);

					$command_param->fill($fields);

					if ($command_param->save())
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
	 * Удаление параметра команды
	 */
	public function post_delete()
	{
		$rules = array(
			'id' => 'required|integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$command_param = CommandParam::find(Input::get('id'));

			if ($command_param)
			{
				if ($command_param->delete())
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