<?=View::make('inc.meta', get_defined_vars() )->render()?>

<body>

	<?=View::make('inc.header', get_defined_vars() )->render()?>

	<div class="container">
		<div class="row">
			<div class="offset4 span3">
				<h1>Profile</h1>
				<?	if($errors->has('model_edit_fail'))
							echo 'user edit fail<br/>';
				?>

				<?= Form::open() ?>
					<?= Form::token() ?>

					<?	if($errors->has('name'))
							echo $errors->first('name'), '<br/>';
					?>

					<?= Form::label('name', 'Name: ') ?>
					<?= Form::text('name', Input::old('name') ? Input::old('name') : $user->name, array('placeholder' => 'Name', 'class' => 'input-xlarge')) ?> <br/><br/>

					<?= Form::label('balance', 'Balance: ') ?>
					<?= Form::text('balance', $user->balance, array('class' => 'input-xlarge', 'disabled' => 'disabled')) ?> <br/><br/>

					<?= Form::submit('Save', array('class' => 'btn btn-primary')) ?>
				<?= Form::close() ?>
			</div>
		</div>
	</div>

	<?=View::make('inc.scripts', get_defined_vars() )->render()?>
</body>
</html>