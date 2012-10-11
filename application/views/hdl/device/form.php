<?=View::make('system.inc.meta', get_defined_vars())->render()?>

<body>

	<?=View::make('system.inc.header', get_defined_vars())->render()?>

	<div class="container-fluid">

		<h1><?= ($create ? 'Create device' : 'Edit device')?></h1>

		<?	if($errors->has('model_create_fail'))
				echo 'model create fail<br/>';
		?>

		<?= Form::open() ?>
			<?= Form::token() ?>

			<?if(!$create):?>
				<?= Form::hidden('id', $device->id)?>
			<?endif;?>
			
			<?	if($errors->has('title'))
					echo $errors->first('title'), '<br/>';
			?>

			<?= Form::label('title', 'Title: ') ?>
			<?= Form::text('title', ( Input::old('title') || $create ? Input::old('title') : $device->title )) ?> <br/><br/>


			<?	if($errors->has('code'))
					echo $errors->first('code'), '<br/>';
			?>

			<?= Form::label('code', 'Code: ') ?>
			<?= Form::text('code', ( Input::old('code') || $create ? Input::old('code') : $device->code )) ?> <br/><br/>

			<?	if($errors->has('subnet_id'))
					echo $errors->first('subnet_id'), '<br/>';
			?>

			<?= Form::label('subnet_id', 'Subnet id: ') ?>
			<?= Form::text('subnet_id', ( Input::old('subnet_id') || $create ? Input::old('subnet_id') : $device->subnet_id )) ?> <br/><br/>

			<?	if($errors->has('device_id'))
					echo $errors->first('device_id'), '<br/>';
			?>

			<?= Form::label('device_id', 'Device id: ') ?>
			<?= Form::text('device_id', ( Input::old('device_id') || $create ? Input::old('device_id') : $device->device_id )) ?> <br/><br/>

			<?	if($errors->has('device_type'))
					echo $errors->first('device_type'), '<br/>';
			?>

			<?
				$select_array = array();

				foreach ($device_types as $item)
					$select_array[$item->id] = $item->title;
			?>

			<?= Form::label('device_type', 'Device type: ') ?>
			<?= Form::select('device_type', $select_array, ( Input::old('device_type') || $create ? Input::old('device_type') : $device->device_type_id ))?><br/><br/>


			<?= Form::submit(($create ? 'create' : 'save'), array('class' => 'btn btn-primary')) ?>
		<?= Form::close() ?>


		<?if(!$create):?>
			<?= Form::open('hdl/device/delete') ?>
				<?= Form::token() ?>

				<?= Form::hidden('id', $device->id)?>

				<?= Form::submit('delete', array('class' => 'btn btn-danger')) ?>
			<?= Form::close() ?>
		<?endif;?>
	</div>

	<footer>
		<?=View::make('system.inc.footer', get_defined_vars())->render()?>
	</footer>

	<?=View::make('system.inc.scripts', get_defined_vars())->render()?>
</body>
</html>