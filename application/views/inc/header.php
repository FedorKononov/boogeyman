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
				<?if (Auth::user()):?>
					<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
						<i class="icon-user"></i> <?= Auth::user()->email;?>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<li><a href="/account">Profile</a></li>
						<li class="divider"></li>
						<li><a href="/logout">Sign Out</a></li>
					</ul>
				<?endif;?>
			</div>
			<div class="nav-collapse">
				<?
					if(Auth::user())
						echo MenuHandler::menu(array_keys(Auth::user()->permissions['groups']))->items()->render(array('class' => 'nav'));
					else
					{
						$perms = App\Models\Account\Account::guest_permissions();
						
						echo MenuHandler::menu(array_keys($perms['groups']))->items()->render(array('class' => 'nav'));
					}
				?>
			</div><!--/.nav-collapse -->
		</div>
	</div>
</div>