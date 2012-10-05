<?php 

use App\Models\Account\Permission;

class Acl_Task {

	public function run($arguments)
	{
		echo chr(10),'Used to generate permission list accordig to all your controllers',
			 chr(10), chr(10), 'Usage: ', chr(10), '	php artisan acl:generate_permissions', chr(10);
	}

	public function generate_permissions($arguments)
	{
		$codes = array();

		foreach (Controller::detect() as $controller)
		{
			echo $controller, chr(10);

			$resolved_cotroller = Controller::resolve('application', $controller);

			$methods = get_class_methods($resolved_cotroller);

			foreach ($methods as $method)
			{
				if(starts_with($method, 'get_') || starts_with($method, 'post_'))
				{
					$method = preg_replace('/^(post\_|get\_)/', '', $method);

					$code = trim($controller. '@' .$method);

					// Если еще не был обработан (нужно для исключения одинаковых методов)
					if($codes[$code] != 1)
					{
						echo '	', $code;
						
						$perm = DB::first('SELECT * FROM '. Permission::$table .' WHERE `code` = ?', array($code));
						if(!$perm)
						{
							$new_perm = new Permission();

							$new_perm->title       = 'Auto: '. $code;
							$new_perm->code        = $code;
							$new_perm->description = 'Created by ACL tool';

							$new_perm->save();
							echo '	- created';
						} else
							echo '	- existed';

						$codes[$code] = 1;
						echo chr(10);
					}
				}
			}
		}

		// чистим кеш
		Permission::cflush_all();
		echo chr(10), '	Cache cleaned', chr(10);
	}
}