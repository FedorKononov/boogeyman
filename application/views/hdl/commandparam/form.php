<?=View::make('system.inc.meta', get_defined_vars())->render()?>

<body>

	<?=View::make('system.inc.header', get_defined_vars())->render()?>

	<div class="container-fluid">

		<h1><?= ($create ? 'Create command param' : 'Edit command param')?></h1>

		<?	if($errors->has('model_create_fail'))
				echo 'model create fail<br/>';
		?>

		<?= Form::open() ?>
			<?= Form::token() ?>

			<?if(!$create):?>
				<?= Form::hidden('id', $commandparam->id)?>
			<?endif;?>
			
			<?	if($errors->has('title'))
					echo $errors->first('title'), '<br/>';
			?>

			<?= Form::label('title', 'Title: ') ?>
			<?= Form::text('title', ( Input::old('title') || $create ? Input::old('title') : $commandparam->title )) ?> <br/><br/>


			<?	if($errors->has('value'))
					echo $errors->first('value'), '<br/>';
			?>

			<?= Form::label('value', 'Value: ') ?>
			<?= Form::text('value', ( Input::old('value') || $create ? Input::old('value') : $commandparam->value )) ?> <br/><br/>


			<?	if($errors->has('command'))
					echo $errors->first('command'), '<br/>';
			?>

			<?
				$select_array = array();

				foreach ($commands as $item)
					$select_array[$item->id] = $item->title;
			?>

			<?= Form::label('command', 'Commands: ') ?>
			<?= Form::select('command', $select_array, ( Input::old('command') || $create ? Input::old('command') : $commandparam->command_id ))?><br/><br/>


			<?= Form::submit(($create ? 'create' : 'save'), array('class' => 'btn btn-primary')) ?>
		<?= Form::close() ?>


		<?if(!$create):?>
			<?= Form::open('hdl/commandparam/delete') ?>
				<?= Form::token() ?>

				<?= Form::hidden('id', $commandparam->id)?>

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