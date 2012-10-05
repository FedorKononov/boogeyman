<?=View::make('system.inc.meta', get_defined_vars() )->render()?>

<body>

	<?=View::make('system.inc.header', get_defined_vars() )->render()?>

	<div class="container-fluid">

		<div class="row-fluid">
			<div class="span9">
				<h1>Kaiban admin backend</h1>
				<p>Here you can admin everything.</p>
			</div>
		</div>
	</div>

	<?=View::make('system.inc.scripts', get_defined_vars() )->render()?>
</body>
</html>