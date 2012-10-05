<?=View::make('system.inc.meta', get_defined_vars() )->render()?>

<body>

	<?=View::make('system.inc.header', get_defined_vars() )->render()?>

	<div class="container-fluid">

		<h1><?= ($create ? 'Create menu' : 'Edit menu')?></h1>

		<?	if($errors->has('model_create_fail'))
				echo 'model create fail<br/>';
		?>

		<?= Form::open() ?>
			<?= Form::token() ?>
			
			<?if(!$create):?>
				<?= Form::hidden('id', $menu->id)?>
			<?endif;?>

			<?	if($errors->has('title'))
					echo $errors->first('title'), '<br/>';
			?>

			<?= Form::label('title', 'Title: ') ?>
			<?= Form::text('title', ( Input::old('title') || $create ? Input::old('title') : $menu->title )) ?> <br/><br/>

			<?	if($errors->has('route'))
					echo $errors->first('route'), '<br/>';
			?>

			<?= Form::label('route', 'Route: ') ?>
			<?= Form::text('route', ( Input::old('route') || $create ? Input::old('route') : $menu->route )) ?> <br/><br/>

			<?	if($errors->has('active'))
					echo $errors->first('active'), '<br/>';
			?>

			<?= Form::label('active', 'Active: ') ?>
			<?= Form::checkbox('active', 1, ( Input::old('active') || $create ? Input::old('active') : $menu->active )) ?> <br/><br/>

			<?
				if(!Input::old('parent') && !$create )
				{
					foreach ($flat_tree as $item)
					{
						$select_array[$item->id] = str_repeat('&nbsp;&nbsp;', $item->depth) . str_repeat('--', $item->depth) . ' ' . $item->title;

						if($menu->is_child_of($item))
							$parent_default = $item->id;
					}

				} else {
					foreach ($flat_tree as $item)
						$select_array[$item->id] = str_repeat('&nbsp;&nbsp;', $item->depth) . str_repeat('--', $item->depth) . ' ' . $item->title;

					$parent_default = Input::old('parent');
				}
			?>
			
			<?	if($errors->has('parent'))
					echo $errors->first('parent'), '<br/>';
			?>

			<?= Form::label('parent', 'Parent: ') ?>
			<?= Form::select('parent', $select_array, $parent_default)?><br/><br/>
			
			<?= Form::submit(($create ? 'create' : 'save'), array('class' => 'btn btn-primary')) ?>
		<?= Form::close() ?>


		<?if(!$create):?>
			<?= Form::open('system/menu/delete') ?>
				<?= Form::token() ?>

				<?= Form::hidden('id', $menu->id)?>

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