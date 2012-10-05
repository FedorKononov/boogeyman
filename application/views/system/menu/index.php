<?=View::make('system.inc.meta', get_defined_vars() )->render()?>

<body>

	<?=View::make('system.inc.header', get_defined_vars() )->render()?>

	<div class="container-fluid">
		<h1>Регионы</h1>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Заголовок</th>
					<th>Маршрут</th>
					<th>Видимость</th>
					<th>Действия</th>
				</tr>
			</thead>
			<tbody>
				<?foreach ($items as $item):?>
					<tr>
						<td><p class="offset<?= $item->depth?>"><?= $item->title?></p></td>
						
						<td><?= $item->route?></td>
						<td><?= $item->active ? 'Да' : 'Нет'?></td>

						<td><?if ($item->depth):?><a class="btn" href="/system/menu/edit/<?= $item->id?>"><i class="icon-edit"></i> редактировать</a><?endif;?></td>

					</tr>
				<?endforeach;?>
			</tbody>
		</table>

		<a class="btn btn-primary" href="/system/menu/create"><i class="icon-edit icon-white"></i> добавить</a>
	</div>

	<?=View::make('system.inc.scripts', get_defined_vars() )->render()?>
</body>
</html>