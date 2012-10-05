<?=View::make('inc.meta', get_defined_vars() )->render()?>

<body>

	<?=View::make('inc.header', get_defined_vars() )->render()?>

	<div class="container">
		<div class="row">
			<div class="offset4 span3">
				<?	if($errors->has('model_create_fail'))
							echo 'user register fail<br/>';
				?>

				<?= Form::open() ?>
					<?= Form::token() ?>

					<?	if($errors->has('email'))
							echo $errors->first('email'), '<br/>';
					?>

					<?= Form::label('email', 'E-Mail: ') ?>
					<?= Form::text('email', Input::old('email'), array('placeholder' => 'Email', 'class' => 'input-xlarge')) ?> <br/><br/>

					<?	if($errors->has('name'))
							echo $errors->first('name'), '<br/>';
					?>

					<?= Form::label('name', 'Name: ') ?>
					<?= Form::text('name', Input::old('name'), array('placeholder' => 'Name', 'class' => 'input-xlarge')) ?> <br/><br/>

					<?	if($errors->has('password'))
							echo $errors->first('password'), '<br/>';
					?>

					<?= Form::label('password', 'Password: ') ?>
					<?= Form::password('password', array('class' => 'input-xlarge')) ?> <br/>

					<?= Form::label('password', 'Password confirm: ') ?>
					<?= Form::password('password_confirmation', array('class' => 'input-xlarge')) ?> <br/><br/>

					<?	if($errors->has('terms'))
							echo $errors->first('terms'), '<br/>';
					?>

					<?= Form::checkbox('terms', 'yes', Input::old('terms')); ?> 
					Вы согласны с условмиями нашего <a href="#">соглашения.</a>
					<br/>

					<?= Form::submit('  Register  ', array('class' => 'btn btn-success btn-large')) ?>
				<?= Form::close() ?>
			</div>
		</div>
	</div>

	<?=View::make('inc.scripts', get_defined_vars() )->render()?>
</body>
</html>