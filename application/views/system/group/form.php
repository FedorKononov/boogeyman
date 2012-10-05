<?=View::make('system.inc.meta', get_defined_vars() )->render()?>

<body>

	<?=View::make('system.inc.header', get_defined_vars() )->render()?>

	<div class="container-fluid">

		<h1><?= ($create ? 'Create group' : 'Edit group')?></h1>

		<?	if($errors->has('model_create_fail'))
				echo 'model create fail<br/>';
		?>

		<?= Form::open() ?>
			<?= Form::token() ?>

			<?if(!$create):?>
				<?= Form::hidden('id', $group->id)?>
			<?endif;?>
			
			<?	if($errors->has('title'))
					echo $errors->first('title'), '<br/>';
			?>

			<?= Form::label('title', 'Title: ') ?>
			<?= Form::text('title', ( Input::old('title') || $create ? Input::old('title') : $group->title )) ?> <br/><br/>


			<?	if($errors->has('code'))
					echo $errors->first('code'), '<br/>';
			?>

			<?= Form::label('code', 'Code: ') ?>
			<?= Form::text('code', ( Input::old('code') || $create ? Input::old('code') : $group->code )) ?> <br/><br/>


			<?	if($errors->has('permissions'))
					echo $errors->first('permissions'), '<br/>';
			?>

			<?= Form::label('permissions', 'Permissions: ') ?>
			<?
				if(Input::old('permissions') || $create )
				{
					$selected = is_array(Input::old('permissions')) ? Input::old('permissions') : array();

					foreach ($permissions as $item)
						echo '<br/>', Form::checkbox('permissions[]', $item->id, (in_array($item->id, $selected) ? true : false)), ' ', $item->title;
				} else
					foreach ($permissions as $item)
						echo '<br/>', Form::checkbox('permissions[]', $item->id, (in_array($item->id, $group->permissions) ? true : false)), ' ', $item->title;
			?>
			<br/><br/>

			<?= Form::submit(($create ? 'create' : 'save'), array('class' => 'btn btn-primary')) ?>
		<?= Form::close() ?>

		<?if(!$create):?>
			<?= Form::open('system/group/delete') ?>
				<?= Form::token() ?>

				<?= Form::hidden('id', $group->id)?>

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