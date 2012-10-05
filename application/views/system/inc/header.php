<div class="navbar navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container-fluid">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>
			<a class="brand" href="/system"><?= Config::get('application.project_name');?></a>
			<div class="btn-group pull-right">
				<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
					<i class="icon-user"></i> <?= Auth::user()->email;?>
					<span class="caret"></span>
				</a>
				<ul class="dropdown-menu">
					<li><a href="/account">Profile</a></li>
					<li class="divider"></li>
					<li><a href="/logout">Sign Out</a></li>
				</ul>
			</div>
			<div class="nav-collapse">
				<?
					echo MenuHandler::menu(Auth::user()->permissions['groups'])->items()->render(array('class' => 'nav'));
				?>
			</div><!--/.nav-collapse -->
		</div>
	</div>
</div>