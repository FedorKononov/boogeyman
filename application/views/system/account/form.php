<?=View::make('system.inc.meta', get_defined_vars() )->render()?>

<body>

	<?=View::make('system.inc.header', get_defined_vars() )->render()?>

	<div class="container-fluid">

		<h1><?= ($create ? 'Create account' : 'Edit account')?></h1>

		<?	if($errors->has('model_create_fail'))
				echo 'model create fail<br/>';
		?>

		<?= Form::open() ?>
			<?= Form::token() ?>

			<?if(!$create):?>
				<?= Form::hidden('id', $account->id)?>
			<?endif;?>
			
			<?	if($errors->has('name'))
					echo $errors->first('name'), '<br/>';
			?>

			<?= Form::label('name', 'Name: ') ?>
			<?= Form::text('name', ( Input::old('name') || $create ? Input::old('name') : $account->name )) ?> <br/><br/>


			<?	if($errors->has('email'))
					echo $errors->first('email'), '<br/>';
			?>

			<?= Form::label('email', 'Email: ') ?>
			<?= Form::text('email', ( Input::old('email') || $create ? Input::old('email') : $account->email )) ?> <br/><br/>


			<?	if($errors->has('password'))
					echo $errors->first('password'), '<br/>';
			?>

			<?= Form::label('password', 'Password: ') ?>
			<?= Form::password('password') ?><br/><br/>

			<?if($create):?>
				<?= Form::label('password', 'Password confirm: ') ?>
				<?= Form::password('password_confirmation') ?> <br/><br/>
			<?endif;?>


			<?	if($errors->has('status'))
					echo $errors->first('status'), '<br/>';
			?>

			<?
				$select_array = array();

				foreach ($statuses as $item)
					$select_array[$item->id] = $item->title;
			?>

			<?= Form::label('status', 'Status: ') ?>
			<?= Form::select('status', $select_array, ( Input::old('status') || $create ? Input::old('status') : $account->status_id ))?><br/><br/>


			<?	if($errors->has('groups'))
					echo $errors->first('groups'), '<br/>';
			?>

			<?= Form::label('groups', 'Groups: ') ?>
			<?
				if(Input::old('groups') || $create )
				{
					$selected = is_array(Input::old('groups')) ? Input::old('groups') : array();

					foreach ($groups as $item)
						echo Form::checkbox('groups[]', $item->id, (in_array($item->id, $selected) ? true : false)), ' ', $item->title, '<br/>';
				} else
					foreach ($groups as $item)
						echo Form::checkbox('groups[]', $item->id, (in_array($item->id, $account->groups) ? true : false)), ' ', $item->title, '<br/>';
			?>
			<br/><br/>

			<?= Form::submit(($create ? 'create' : 'save'), array('class' => 'btn btn-primary')) ?>
		<?= Form::close() ?>
	</div>

	<footer>
		<?=View::make('system.inc.footer', get_defined_vars() )->render()?>
	</footer>

	<?=View::make('system.inc.scripts', get_defined_vars() )->render()?>
</body>
</html>