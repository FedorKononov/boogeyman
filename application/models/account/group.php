<?php namespace App\Models\Account;

use App\Models\BaseReference;

class Group extends BaseReference {

	public static $table = 'account_group';

	public static $connection = 'mysql_read';

	public function accounts()
	{
		return $this->has_many_and_belongs_to('App\Models\Account\Account', Account::$group_pivot_table);
	}

	public function permissions()
	{
		return $this->has_many_and_belongs_to('App\Models\Account\Permission', Permission::$group_pivot_table);
	}
}