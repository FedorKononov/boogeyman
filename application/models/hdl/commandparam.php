<?php namespace App\Models\HDL;

use App\Models\EloquentFix;

class CommandParam extends EloquentFix {
	public static $table = 'hdl_command_param';

	public static $connection = 'mysql_read';

	public function command()
	{
		return $this->belongs_to('App\Models\HDL\Command', 'command_id');
	}
}