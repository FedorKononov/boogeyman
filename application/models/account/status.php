<?php namespace App\Models\Account;

use App\Models\BaseStatus;

class Status extends BaseStatus {

	public static $table = 'account_status';

	public static $connection = 'mysql_read';

	public function accounts()
	{
		return $this->has_many('App\Models\Account\Account');
	}

}