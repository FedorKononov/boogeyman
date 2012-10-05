<?php 

use App\Models\Account\Status, App\Models\Account\Account;

class Account_Controller extends Base_Controller {

	public $view = 'account';
	public $uri  = 'account';

	/**
	 * Конструктор
	 */
	public function __construct()
	{
		// Запрещаем гостям для всех страни кроме
		$this->filter('before', 'auth')->except(array('login', 'register'));
		
		// Запрещаем авторизованным страницы 
		$this->filter('before', 'guest')->only(array('login', 'register'));

		// csrf фильтр
		$this->filter('before', 'csrf')->on('post');

		parent::__construct();
	}

	/**
	 * Method: GET
	 *
	 * Страница профиля
	 */
	public function get_index()
	{
		$user = Account::with(array('status'))
				   ->where(Account::key(), '=', Auth::user()->id)
				   ->first();

		if(!$user)
			return Response::error('404');

		return View::make($this->view .'.profile', array(
			'user' => $user,
		));
	}

	/**
	 * Method: GET
	 *
	 * Cтраница авторизации
	 */
	public function get_login()
	{
		return View::make($this->view .'.login');
	}

	/**
	 * Method: POST
	 *
	 * Авторизация
	 */
	public function post_login()
	{
		$rules = array(
			'email' => 'required|email',
			'password' => 'required',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			$credentials = array(
				'username' => Input::get('email'),
				'password' => Input::get('password')
			);

			if (Auth::attempt($credentials))
				return Redirect::to('account');
			else
				$validator->errors->add('user_login_fail', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/login')->with('errors', $validator->errors)->with_input();
	}

	/**
	 * Method: GET
	 *
	 * Logout
	 */
	public function get_logout()
	{
		Auth::logout();
		
		return Redirect::to($this->uri .'/login');
	}

	/**
	 * Method: GET
	 *
	 * Страница регистрации
	 */
	public function get_register()
	{
		return View::make($this->view .'.register');
	}

	/**
	 * Method: POST
	 *
	 * Регистрация
	 */
	public function post_register()
	{
		$rules = array(
			'email'    => 'required|max:50|email|unique:'. Account::$table,
			'name'     => 'required|max:50',
			'password' => 'required|min:6|confirmed',
			'terms'    => 'accepted',
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->valid())
		{
			// получаем стартовый статус
			if ($root_status = Status::cget_root())
			{
				$fields = array(
					'email'       => Input::get('email'),
					'name'        => Input::get('name'),
					'password'    => Input::get('password'),
					'status_id'   => $root_status->id,
				);

				$new_account = Account::create($fields);

				if ($new_account !== false)
				{
					//авторизуем аккаунт после регистрации
					Auth::login($new_account->id);

					return Redirect::to('account');
				} else
					$validator->errors->add('model_create_fail', true);

			} else
				$validator->errors->add('get_root_status_fail', true);
		}

		// редиректим сохранив данные формы для показа после
		return Redirect::to($this->uri .'/register')->with('errors', $validator->errors)->with_input();
	}
}