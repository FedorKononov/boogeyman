<?=View::make('system.inc.meta', get_defined_vars())->render()?>

<body>

	<?=View::make('system.inc.header', get_defined_vars())->render()?>

	<div class="container-fluid">

		<h1><?= ($create ? 'Create command' : 'Edit command')?></h1>

		<?	if($errors->has('model_create_fail'))
				echo 'model create fail<br/>';
		?>

		<?= Form::open() ?>
			<?= Form::token() ?>

			<?if(!$create):?>
				<?= Form::hidden('id', $command->id)?>
			<?endif;?>
			
			<?	if($errors->has('title'))
					echo $errors->first('title'), '<br/>';
			?>

			<?= Form::label('title', 'Title: ') ?>
			<?= Form::text('title', ( Input::old('title') || $create ? Input::old('title') : $command->title )) ?> <br/><br/>


			<?	if($errors->has('code'))
					echo $errors->first('code'), '<br/>';
			?>

			<?= Form::label('code', 'Code: ') ?>
			<?= Form::text('code', ( Input::old('code') || $create ? Input::old('code') : $command->code )) ?> <br/><br/>

			<?	if($errors->has('operate_code'))
					echo $errors->first('operate_code'), '<br/>';
			?>

			<?= Form::label('operate_code', 'Operate code: ') ?>
			<?= Form::text('operate_code', ( Input::old('operate_code') || $create ? Input::old('operate_code') : $command->operate_code )) ?> <br/><br/>


			<?	if($errors->has('device_type'))
					echo $errors->first('device_type'), '<br/>';
			?>

			<?
				$select_array = array();

				foreach ($device_types as $item)
					$select_array[$item->id] = $item->title;
			?>

			<?= Form::label('device_type', 'Device type: ') ?>
			<?= Form::select('device_type', $select_array, ( Input::old('device_type') || $create ? Input::old('device_type') : $command->device_type_id ))?><br/><br/>


			<?= Form::submit(($create ? 'create' : 'save'), array('class' => 'btn btn-primary')) ?>
		<?= Form::close() ?>


		<?if(!$create):?>
			<?= Form::open('hdl/command/delete') ?>
				<?= Form::token() ?>

				<?= Form::hidden('id', $command->id)?>

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