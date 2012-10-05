<?php namespace App\Models\Account;

use App\Models\BaseReference;

class Permission extends BaseReference
{
	public static $table = 'account_permission';
	public static $group_pivot_table = 'account_group_permission';

	public static $connection = 'mysql_read';

	public function groups()
	{
		return $this->has_many_and_belongs_to('App\Models\Account\Group', self::$group_pivot_table);
	}

}