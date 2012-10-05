<?=View::make('inc.meta', get_defined_vars() )->render()?>

<body>

	<?=View::make('inc.header', get_defined_vars() )->render()?>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="hero-unit">
				<?if(Auth::user()):?>
					<h1>Hello, <?=Auth::user()->email?>!</h1>
					<p>You have unique ability to try our ad network!</p>
					<p><a class="btn btn-warning btn-large" href="/company/create"><i class="icon-fire icon-white"></i>&nbsp;&nbsp;&nbsp;Create your company now !&nbsp;&nbsp;&nbsp;</a></p>
				<?else:?>
					<h1>Hello, guest!</h1>
					<p>You have unique ability to try our ad network!</p>
					<p><a class="btn btn-success btn-large" href="/register"><i class="icon-fire icon-white"></i>&nbsp;&nbsp;&nbsp;Register !&nbsp;&nbsp;&nbsp;</a> &nbsp;&nbsp;&nbsp; or &nbsp;&nbsp;&nbsp; <a class="btn btn-primary btn-large" href="/login"><i class="icon-fire icon-white"></i>&nbsp;&nbsp;&nbsp;Login&nbsp;&nbsp;&nbsp;</a></p>
				<?endif?>
			</div>
		</div>
	</div>

	<?=View::make('inc.scripts', get_defined_vars() )->render()?>
</body>
</html>