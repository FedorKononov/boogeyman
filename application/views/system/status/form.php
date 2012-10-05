<?=View::make('system.inc.meta', get_defined_vars() )->render()?>

<body>

	<?=View::make('system.inc.header', get_defined_vars() )->render()?>

	<div class="container-fluid">

		<h1><?= ($create ? 'Create status' : 'Edit status')?></h1>

		<?	if($errors->has('model_create_fail'))
				echo 'model create fail<br/>';
		?>

		<?= Form::open() ?>
			<?= Form::token() ?>
			
			<?if(!$create):?>
				<?= Form::hidden('id', $status->id)?>
				<?= Form::hidden('model', strstr($status->table(), '_', true))?>
			<?endif;?>

			<?= Form::hidden('model', Input::get('model'))?>

			<?	if($errors->has('title'))
					echo $errors->first('title'), '<br/>';
			?>

			<?= Form::label('title', 'Title: ') ?>
			<?= Form::text('title', ( Input::old('title') || $create ? Input::old('title') : $status->title )) ?> <br/><br/>

			<?	if($errors->has('code'))
					echo $errors->first('code'), '<br/>';
			?>

			<?= Form::label('code', 'Code: ') ?>
			<?= Form::text('code', ( Input::old('code') || $create ? Input::old('code') : $status->code )) ?> <br/><br/>

			<?	if($errors->has('weight'))
					echo $errors->first('weight'), '<br/>';
			?>

			<?= Form::label('weight', 'Weight: ') ?>
			<?= Form::text('weight', ( Input::old('weight') || $create ? Input::old('weight') : $status->weight )) ?> <br/><br/>

			<?	if($errors->has('is_root'))
					echo $errors->first('is_root'), '<br/>';
			?>

			<?= Form::label('is_root', 'Is root: ') ?>
			<?= Form::checkbox('is_root', 1, ( Input::old('is_root') || $create ? Input::old('is_root') : $status->is_root )) ?> <br/><br/>


			<?	if($errors->has('moves'))
				echo $errors->first('moves'), '<br/>';
			?>

			<?	if($errors->has('moves_perms'))
				echo $errors->first('moves_perms'), '<br/>';
			?>

			<?= Form::label('moves', 'Moves: ') ?>
			<?
				if(Input::old('moves') || $create )
				{
					$selected = is_array(Input::old('moves')) ? Input::old('moves') : array();
					$moves_perm = Input::old('moves_perms');

					foreach ($statuses as $item)
						echo Form::checkbox('moves[]', $item->id, (in_array($item->id, $selected) ? true : false)), ' ',
							 $item->title, ' ',
							 Form::text('moves_perms['. $item->id .']', $moves_perm[$item->id], array('class' => 'input-small')),
							 '<br/>';
				}

				else
				{
					$selected = array_keys($status->moves);
					$moves_perm = $status->moves;

					foreach ($statuses as $item)
						echo Form::checkbox('moves[]', $item->id, (in_array($item->id, $selected) ? true : false)), ' ',
							 $item->title, ' ',
							 Form::text('moves_perms['. $item->id .']', (in_array($item->id, $selected) ? $moves_perm[$item->id]->permission : ''), array('class' => 'input-small')),
							 '<br/>';
				}
			?>
			<br/><br/>

			<?= Form::submit(($create ? 'create' : 'save'), array('class' => 'btn btn-primary')) ?>
		<?= Form::close() ?>


		<?if(!$create):?>
			<?= Form::open('system/status/delete') ?>
				<?= Form::token() ?>

				<?= Form::hidden('id', $status->id)?>
				<?= Form::hidden('model', strstr($status->table(), '_', true))?>

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