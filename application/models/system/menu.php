<?php namespace App\Models\System;

use App\Models\BaseCatalog;

class Menu extends BaseCatalog {

	public static $table = 'menu';

	public static $connection = 'mysql_read';
}