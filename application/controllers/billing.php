<?php

use App\Models\Billing\Billing;

class Billing_Controller extends Front_Controller {

	public $view = 'billing';
	public $uri = 'billing';

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
	 * По умолчанию показываем список транзакций
	 */
	public function get_index()
	{		
		$transactions = Billing::with(array('company'))->where('account_id', '=', Auth::user()->id)->paginate(Config::get('application.paginate'));

		return View::make($this->view .'.index', array('items' => $transactions));
	}
	
}