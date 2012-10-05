<?=View::make('inc.meta', get_defined_vars() )->render()?>

<body>

	<?=View::make('inc.header', get_defined_vars() )->render()?>

	<div class="container">
		<div class="row">
			<div class="offset4 span3">

			<?	if($errors->has('user_login_fail'))
					echo 'user login fail <br/>';
			?>

			<?= Form::open() ?>
				<?= Form::token() ?>
				
				<?	if($errors->has('email'))
						echo $errors->first('email');
				?>

				<?= Form::text('email', Input::old('email'), array('placeholder' => 'Email', 'class' => 'input-xlarge')) ?>

				<?	if($errors->has('password'))
						echo '<br/>', $errors->first('password');
				?>

				<?= Form::password('password', array('placeholder' => 'Password', 'class' => 'input-xlarge')) ?> <br/>

				<?= Form::submit('Login', array('class' => 'btn btn-primary offset1')) ?>
			<?= Form::close() ?>
			</div>
		</div>
	</div>

	<?=View::make('inc.scripts', get_defined_vars() )->render()?>
</body>
</html>