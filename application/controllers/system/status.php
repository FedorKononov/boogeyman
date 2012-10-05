<?php

class System_Status_Controller extends System_Controller {

	public $view   = 'system.status';
	public $uri    = 'system/status';
	public $models = array('account' => 'Account');

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
	 * Показываем список статусов для выбранной модели
	 */
	public function get_index()
	{
		if (!Input::get('model'))
			return View::make($this->view .'.index', array('items' => false, 'models' => $this->models));

		switch (Input::get('model'))
		{
			case 'account':
				$statuses = App\Models\Account\Status::order_by('weight', 'asc')->paginate(Config::get('application.paginate'));

			default:
				return View::make($this->view .'.index', array('items' => false, 'models' => $this->models));
		}

		return View::make($this->view .'.index', array('items'  => $statuses, 'models' => $this->models));
	}

	/**
	 * Method: GET
	 *
	 * Страница добавления статуса
	 */
	public function get_create()
	{
		if (!Input::get('model'))
			return Redirect::to($this->uri);

		if (!array_key_exists(Input::get('model'), $this->models))
			return Redirect::to($this->uri);

		switch (Input::get('model'))
		{
			case 'account':
				$statuses = App\Models\Account\Status::cget_all();
				break;

			default:
				$statuses = array();
		}

		return View::make($this->view .'.form', array(
			'create'   => true,
			'models'   => $this->models,
			'statuses' => $statuses,
		));
	}


	/**
	 * Method: POST
	 *
	 * Добавление статуса
	 */
	public function post_create()
	{
		$rules = array(
			'model'       => 'required|in:'. implode(',', array_keys($this->models)),
			'title'       => 'required|max:50',
			'code'        => 'required|min:3|max:50|match:/^[._a-z]+$/|unique:'. Input::get('model') .'_status',
			'weight'      => 'integer',
			'is_root'     => 'integer',
			'moves'       => 'integer_array',
			'moves_perms' => 'array_match:/^[._a-z]*$/',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$fields = array(
				'title'  => Input::get('title'),
				'code'   => Input::get('code'),
				'weight' => Input::get('weight'),
			);

			switch (Input::get('model'))
			{
				case 'account':
					$new_status = App\Models\Account\Status::create($fields);
					break;

				default:
					$new_status = false;
			}

			if ($new_status !== false)
			{
				// сбрасываем текущий рутовый статус
				if (Input::get('is_root'))
				{
					DB::table($new_status->table())->where('is_root', '=', 1)->update(array('is_root' => 0));

					$new_status->is_root = 1;
					$new_status->save();
				}

				// сбасываем кеш конкретной модели статуса
				$new_status::cflush_all();

				if(Input::get('moves'))
					$new_status->add_moves(array('moves' => Input::get('moves'), 'perms' => Input::get('moves_perms')));

				return Redirect::to($this->uri .'?model='. Input::get('model'));
			}
			else
				$validator->errors->add('model_create_fail', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/create/?model='. Input::get('model'))->with('errors', $validator->errors)->with_input();
	}


	/**
	 * Method: GET
	 *
	 * Страница редактирования статуса
	 */
	public function get_edit($object_id = null)
	{
		if(!Input::get('model'))
			return Redirect::to($this->uri);

		if(!array_key_exists(Input::get('model'), $this->models))
			return Redirect::to($this->uri);

		$object_id = (int) $object_id;

		if(!$object_id)
			return Response::error('404');

		switch (Input::get('model'))
		{
			case 'account':
				$status = App\Models\Account\Status::find($object_id);
				break;

			default:
				$status = false;
		}

		if (!$status)
			return Response::error('404');

		$statuses = $status::cget_all();

		return View::make($this->view .'.form', array(
			'create'   => false,
			'status'   => $status,
			'models'   => $this->models,
			'statuses' => $statuses,
		));
	}

	/**
	 * Method: POST
	 *
	 * Редактирование статуса
	 */
	public function post_edit()
	{
		$rules = array(
			'id'          => 'required|integer',
			'model'       => 'required|in:'. implode(',', array_keys($this->models)),
			'title'       => 'required|max:50',
			'code'        => 'required|min:3|max:50|match:/^[._a-z]+$/|unique:'. Input::get('model') .'_status,code,'. Input::get('id'),
			'weight'      => 'integer',
			'is_root'     => 'integer',
			'moves'       => 'integer_array',
			'moves_perms' => 'array_match:/^[._a-z]*$/',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			switch (Input::get('model'))
			{
				case 'account':
					$status = App\Models\Account\Status::find(Input::get('id'));
					break;

				default:
					$status = false;
			}

			if ($status)
			{
				$status->fill(array(
					'title'  => Input::get('title'),
					'code'   => Input::get('code'),
					'weight' => Input::get('weight'),
				));

				// стартуем тразакцию потому что может быть ситуация когда рут сбросится а статус не обновится, тогда потеряется рут
				DB::connection($status::$connection)->pdo->beginTransaction();

				if (Input::get('is_root') && $status->is_root == 0)
				{
					DB::table($status->table())->where('is_root', '=', 1)->update(array('is_root' => 0));

					$status->is_root = 1;
				}

				if ($status->save())
				{
					// сбасываем кеш конкретной модели статуса
					$status::cflush_all();

					// коммитим
					DB::connection($status::$connection)->pdo->commit();

					// удаляем переходы
					$status->delete_moves();

					if(Input::get('moves'))
						$status->add_moves(array('moves' => Input::get('moves'), 'perms' => Input::get('moves_perms')));

					return Redirect::to($this->uri .'?model='. Input::get('model'));
				} else
					$validator->errors->add('model_edit_fail', true);

				// что то случилось... откатываемся
				DB::connection($status::$connection)->pdo->rollBack();
			}else
				$validator->errors->add('model_edit_fail', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/edit/'. Input::get('id') .'?model='. Input::get('model'))->with('errors', $validator->errors)->with_input();
	}


	/**
	 * Method: POST
	 *
	 * Удаление статуса
	 */
	public function post_delete()
	{
		$rules = array(
			'id'      => 'required|integer',
			'model'   => 'required|in:'. implode(',', array_keys($this->models)),
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			switch (Input::get('model'))
			{
				case 'account':
					$status = App\Models\Account\Status::find(Input::get('id'));
					break;

				default:
					$status = false;
			}

			if ($status)
			{
				// если одинок (на него нету переходов) и не стартовый статус
				if ($status->is_alone() && $status->is_root != 1)
				{
					if($status->delete())
					{
						// сбасываем кеш конкретной модели статуса
						$status::cflush_all();

						// удаляем переходы
						$status->delete_moves();

						return Redirect::to($this->uri .'?model='. Input::get('model'));
					} else
						$validator->errors->add('model_delete_fail', true);
				} else
					$validator->errors->add('model_delete_fail', true);
			} else
				$validator->errors->add('model_delete_fail', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/edit/'. Input::get('id') .'?model='. Input::get('model'))->with('errors', $validator->errors)->with_input();
	}
}