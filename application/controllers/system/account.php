<?php

use App\Models\Account\Account, App\Models\Account\Status, App\Models\Account\Group;

class System_Account_Controller extends System_Controller {

	public $view = 'system.account';
	public $uri = 'system/account';

	/**
	 * Конструктор
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Method: GET
	 *
	 * По умолчанию показываем список аккаунтов
	 */
	public function get_index()
	{
		$accounts = Account::with(array('status'))->paginate(Config::get('application.paginate'));

		return View::make($this->view .'.index', array('items' => $accounts));
	}

	/**
	 * Method: GET
	 *
	 * Страница добавления аккаунта
	 */
	public function get_create()
	{
		return View::make($this->view .'.form', array(
			'create'   => true,
			'statuses' => Status::cget_all(),
			'groups'   => Group::cget_all(),
		));
	}


	/**
	 * Method: POST
	 *
	 * Добавление аккаунта
	 */
	public function post_create()
	{
		$rules = array(
			'email'    => 'required|max:50|email|unique:'. Account::$table,
			'name'     => 'required|max:50',
			'password' => 'required|min:6|confirmed',
			'status'   => 'required|integer',
			'groups'   => 'required',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$status = Status::find(Input::get('status'));

			if($status)
			{
				$fields = array(
					'email'         => Input::get('email'),
					'name'          => Input::get('name'),
					'password'      => Input::get('password'),
					'status_id'     => $status->id,
				);

				$new_account = Account::create($fields);

				$new_account->groups()->sync(Input::get('groups'));

				if ($new_account !== false)
					return Redirect::to($this->uri);
				else
					$validator->errors->add('model_create_fail', true);
			} else
				$validator->errors->add('find_status_fail', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/create')->with('errors', $validator->errors)->with_input();
	}


	/**
	 * Method: GET
	 *
	 * Страница редактирования аккаунта
	 */
	public function get_edit($object_id = null)
	{
		$object_id = (int) $object_id;

		if(!$object_id)
			return Response::error('404');

		$account = Account::find($object_id);

		if(!$account)
			return Response::error('404');

		// подготавливаем группы аккаунта к удобному виду для работы с формами и не создавая модель АР
		$account->groups = $account->pivot_relation_array(Account::$group_pivot_table, 'group_id');

		return View::make($this->view .'.form', array(
			'create' => false,
			'account' => $account,
			'statuses' => Status::cget_all(),
			'groups'   => Group::cget_all(),
		));
	}

	/**
	 * Method: POST
	 *
	 * Редактирование аккаунта
	 */
	public function post_edit()
	{
		$rules = array(
			'id'       => 'required|integer',
			'email'    => 'required|max:50|email|unique:'. Account::$table .',email,'. Input::get('id'),
			'name'     => 'required|max:50',
			'status'   => 'required|integer',
			'groups'   => 'required',
			'password' => 'min:6',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$account = Account::find(Input::get('id'));

			if($account)
			{
				$fields = array(
					'email'       => Input::get('email'),
					'name'        => Input::get('name'),
					'status_id'   => Input::get('status'),
				);

				if(Input::get('password'))
					$fields['password'] = Input::get('password');

				$account->fill($fields);

				if($account->save())
				{
					// добавляем группы
					$groups_attach = $account->groups()->sync(Input::get('groups'));

					if($groups_attach !== false)
					{
						// Сбрасываем пользователский кеш
						Account::cache_flush($account->id);

						return Redirect::to($this->uri);
					} else
						$validator->errors->add('model_edit_fail', true);
				} else
					$validator->errors->add('model_edit_fail', true);
			}else
				$validator->errors->add('model_edit_fail', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/edit/'. Input::get('id'))->with('errors', $validator->errors)->with_input();
	}

}