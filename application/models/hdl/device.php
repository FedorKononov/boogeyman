<?php namespace App\Models\HDL;

use App\Models\EloquentFix;

class Device extends EloquentFix {
	public static $table = 'hdl_device';

	public static $connection = 'mysql_read';

	public function device_type()
	{
		return $this->belongs_to('App\Models\HDL\DeviceType', 'device_type_id');
	}
}