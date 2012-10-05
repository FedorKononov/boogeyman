<?=View::make('system.inc.meta', get_defined_vars() )->render()?>

<body>

	<?=View::make('system.inc.header', get_defined_vars() )->render()?>

	
	<? if(!empty($items)): ?>
		<div class="container-fluid">
			<h1>Statuses</h1>

			<?= Form::open(null, 'get') ?>
				<?= Form::select('model', $models, Input::get('model'), array('class' => 'input-xlarge'))?>
				<?= Form::submit('Filter', array('class' => 'btn btn-success')) ?>
			<?= Form::close() ?>

			<table class="table table-striped">
				<thead>
					<tr>
						<th>Название</th>
						<th>Код</th>
						<th>Вес</th>
						<th>Стартовый</th>
						<th>Действия</th>
					</tr>
				</thead>
				<tbody>
					<?foreach ($items->results as $item):?>
						<tr>
							<td><?= $item->title?></td>

							<td><?= $item->code?></td>

							<td><?= $item->weight?></td>

							<td><?= ($item->is_root ? 'Да' : 'Нет')?></td>

							<td><a class="btn" href="/system/status/edit/<?= $item->id?>?model=<?= Input::get('model')?>"><i class="icon-edit"></i> редактировать</a></td>

						</tr>
					<?endforeach;?>
				</tbody>
			</table>
			<?=$items->appends(array('model' => Input::get('model')))->links(); ?>
			<a class="btn btn-primary" href="/system/status/create?model=<?= Input::get('model')?>"><i class="icon-edit icon-white"></i> добавить</a>
		</div>
	<?else:?>
		<div class="container">
			<div class="row">
				<div class="offset4 span3">
					<br/><br/>
					<?= Form::open(null, 'get') ?>
						<?= Form::select('model', $models, Input::get('model'), array('class' => 'input-xlarge'))?>
						<?= Form::submit('Filter', array('class' => 'btn btn-success btn-large offset1')) ?>
					<?= Form::close() ?>
				</div>
			</div>
		</div>
	<?endif;?>

	<?=View::make('system.inc.scripts', get_defined_vars() )->render()?>
</body>
</html>