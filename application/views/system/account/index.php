<?=View::make('system.inc.meta', get_defined_vars() )->render()?>

<body>

	<?=View::make('system.inc.header', get_defined_vars() )->render()?>

	<div class="container-fluid">
		<h1>Accounts</h1>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Имя</th>
					<th>Email</th>
					<th>Статус</th>
					<th>Действия</th>
				</tr>
			</thead>
			<tbody>
				<?foreach ($items->results as $item):?>
					<tr>
						<td><?= $item->name?></td>
						
						<td><?= $item->email?></td>
						<td><?= $item->status->title?></td>

						<td><a class="btn" href="/system/account/edit/<?= $item->id?>"><i class="icon-edit"></i> редактировать</a></td>

					</tr>
				<?endforeach;?>
			</tbody>
		</table>
		<?=$items->links(); ?>
		<a class="btn btn-primary" href="account/create"><i class="icon-edit icon-white"></i> добавить</a>
	</div>

	<?=View::make('system.inc.scripts', get_defined_vars() )->render()?>
</body>
</html>