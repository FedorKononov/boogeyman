<?=View::make('system.inc.meta', get_defined_vars() )->render()?>

<body>

	<?=View::make('system.inc.header', get_defined_vars() )->render()?>

	<div class="container-fluid">
		<h1>Command params</h1>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Заголовок</th>
					<th>Значение</th>
					<th>Команда</th>
					<th>Действия</th>
				</tr>
			</thead>
			<tbody>
				<?foreach ($items->results as $item):?>
					<tr>
						<td><?= $item->title?></td>
						<td><?= $item->value?></td>
						<td><?= $item->command->title?></td>
						
						<td><a class="btn" href="/hdl/commandparam/edit/<?= $item->id?>"><i class="icon-edit"></i> редактировать</a></td>
					</tr>
				<?endforeach;?>
			</tbody>
		</table>
		<?=$items->links(); ?>
		<a class="btn btn-primary" href="/hdl/commandparam/create"><i class="icon-edit icon-white"></i> добавить</a>
	</div>

	<?=View::make('system.inc.scripts', get_defined_vars() )->render()?>
</body>
</html>