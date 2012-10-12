<?php namespace App\Models\HDL;

use App\Models\HDL\BaseCommand;

class Command extends BaseCommand {
	public static $table = 'hdl_command';

	public static $connection = 'mysql_read';

	public function device_type()
	{
		return $this->belongs_to('App\Models\HDL\DeviceType', 'device_type_id');
	}

	public function params()
	{
		return $this->has_many('App\Models\HDL\CommandParam', 'command_id');
	}
}