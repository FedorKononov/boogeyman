<?php

class System_Controller extends Base_Controller {

	/**
	 * Конструктор
	 */
	public function __construct()
	{
		// проверяем на авторизацию
		$this->filter('before', 'auth');

		// фильтр на подделку запросов для пост запросов
		$this->filter('before', 'csrf')->on('post');

		// проверяем права
		$this->filter('before', 'access');

		parent::__construct();
	}

	/**
	 * Method: GET
	 * 
	 * Главная страница для админа
	 */
	public function get_index()
	{
		return View::make('system.index', $this->data);
	}
	
}