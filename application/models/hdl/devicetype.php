<?php namespace App\Models\HDL;

use App\Models\EloquentFix;

class DeviceType extends EloquentFix {
	public static $table = 'hdl_device_type';

	public static $connection = 'mysql_read';

	public function commands()
	{
		return $this->has_many('App\Models\HDL\Command', 'device_type_id');
	}

	public function devices()
	{
		return $this->has_many('App\Models\HDL\Device', 'device_type_id');
	}
}