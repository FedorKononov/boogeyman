<?=View::make('system.inc.meta', get_defined_vars() )->render()?>

<body>

	<?=View::make('system.inc.header', get_defined_vars() )->render()?>

	<div class="container-fluid">

		<h1><?= ($create ? 'Create permission' : 'Edit permission')?></h1>

		<?	if($errors->has('model_create_fail'))
				echo 'model create fail<br/>';
		?>

		<?= Form::open() ?>
			<?= Form::token() ?>

			<?if(!$create):?>
				<?= Form::hidden('id', $permission->id)?>
			<?endif;?>
			
			<?	if($errors->has('title'))
					echo $errors->first('title'), '<br/>';
			?>

			<?= Form::label('title', 'Title: ') ?>
			<?= Form::text('title', ( Input::old('title') || $create ? Input::old('title') : $permission->title )) ?> <br/><br/>


			<?	if($errors->has('code'))
					echo $errors->first('code'), '<br/>';
			?>

			<?= Form::label('code', 'Code: ') ?>
			<?= Form::text('code', ( Input::old('code') || $create ? Input::old('code') : $permission->code )) ?> <br/><br/>


			<?	if($errors->has('description'))
					echo $errors->first('description'), '<br/>';
			?>

			<?= Form::label('description', 'Description: ') ?>
			<?= Form::textarea('description', ( Input::old('description') || $create ? Input::old('description') : $permission->description )) ?> <br/><br/>

			<?= Form::submit(($create ? 'create' : 'save'), array('class' => 'btn btn-primary')) ?>
		<?= Form::close() ?>


		<?if(!$create):?>
			<?= Form::open('system/permission/delete') ?>
				<?= Form::token() ?>

				<?= Form::hidden('id', $permission->id)?>

				<?= Form::submit('delete', array('class' => 'btn btn-danger')) ?>
			<?= Form::close() ?>
		<?endif;?>
	</div>

	<footer>
		<?=View::make('system.inc.footer', get_defined_vars() )->render()?>
	</footer>

	<?=View::make('system.inc.scripts', get_defined_vars() )->render()?>
</body>
</html>