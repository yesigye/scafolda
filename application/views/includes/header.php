<!DOCTYPE html>
<html lang="en">
<head>
	<title>Scafolda <?php if (isset($title)) echo '- '.$title; ?></title>
	<meta charset="utf-8">
	<!-- Tell the browser to be responsive to screen width -->
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/bootstrap.min.css') ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/font-awesome.min.css') ?>">
	
	<?php if (!empty($styles)): ?>
    <?php foreach ($styles as $style) echo $style ?>
  <?php endif ?>

	<link rel="stylesheet" href="<?php echo base_url('assets/css/style.css') ?>">
	<!-- Google Font -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:300,400" rel="stylesheet">
</head>

<?php
// Links to show active pages
if (!isset($breadcrumb)) $breadcrumb = array();
if (!isset($active)) $active = NULL;
if (!isset($link)) $link = NULL;
if (!isset($sub_link)) $sub_link = NULL;
?>

<body>
    <div id="wrapper" class="">
		<nav id="sidebar-wrapper" class="bg-dark text-white pb-3">
		  	<div class="p-3" style="background:rgba(0,0,0,0.2)">
				<div class="media">
					<h4><i class="fa fa-database mr-2 mt-2"></i></h4>
					<div class="media-body ml-2" style="line-height:1.">
						<div title="database name">
							<a href="<?php echo site_url() ?>" class="text-white"><?php echo $this->session->userdata('database') ?></a>
						</div>
						<?php if(isset($table)): $meta = $this->dashman->tableMeta($table) ?>
							<small title="table name"><?php echo $table ?></small>
							<div class="small text-muted" title="table engine and size"><?php echo $meta['Engine'].' ~ '.$meta['size'] ?></div>
						<?php endif ?>
					</div>
				</div>
			</div>

			<form class="p-3" action="">
				<input class="form-control form-control-dark" type="search" placeholder="Search Database" aria-label="Search">
			</form>
			<hr class="m-0">

			<?php if($recents = $this->dashman->recents()): ?>
			<div class="text-white-50 px-2 mt-3">
				Last updated
			</div>
			<ul class="sidebar-nav nav flex-column mt-2 text-left">
				<?php foreach($recents as $item): ?>
				<li class="nav-item text-truncate">
					<a class="nav-link <?php echo ($this->uri->segment(1) == $item['table']) ? 'active' : null ?>"
					href="<?php echo site_url($item['table']) ?>">
						<?php echo $this->dashman->icon($item['table']) ?>
						<?php echo $item['table'] ?>
					</a>
				</li>
				<?php endforeach ?>
			</ul>
			<?php endif ?>

			<div class="text-white-50 px-2 my-3 d-flex justify-content-between text-uppercase">
				<span style="cursor:pointer" data-toggle="collapse" href="#tables-menu">
					tables <i class="fa fa-caret-down ml-2"></i>
				</span>
				<a href="<?php echo site_url('create-table') ?>" class="btn btn-sm btn-success clearfix	" title="add a new table">
					<i class="fa fa-plus-circle float-left"></i>
				</a>
			</div>
			<div class="collapse show mx-2 py-3 bg-light text-dark rounded" id="tables-menu">
				<?php if($all_tables = $this->dashman->tables()): ?>
				<ul class="sidebar-nav nav flex-column text-left">
					<?php foreach($all_tables as $item): ?>
					<li class="nav-item text-truncate">
						<a class="nav-link <?php echo ($this->uri->segment(1) == $item['name']) ? 'active' : null ?>"
						href="<?php echo site_url($item['name']) ?>">
							<?php echo $this->dashman->icon($item['name']) ?>
							<?php echo $item['name'] ?>
							<span class="badge badge-pill">
								<?php echo $item['rows'] ?>
							</span>
						</a>
					</li>
					<?php endforeach ?>
				</ul>
				<?php else: ?>
					<p class="text-muted px-3 py-4">No tables were found.</p>
				<?php endif ?>
			</div>
		</nav>

		<main id="page-content-wrapper" class="bg-white">
			<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
				<div class="container-fluid px-0">
					<button class="navbar-toggler" type="button" data-toggle="collapse"  id="menu-toggle" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>
					<a class="navbar-brand font-weight-bold d-none d-md-block" href="#">
						<?php if(isset($table)):?>
						<?php echo $this->dashman->icon($table) ?>
						<h4 class="ml-2 d-inline"><?php echo $table ?></h4>
						<?php else: ?>
						Scafolda
						<?php endif ?>
					</a>
					<form class="form-inline">
						<?php $databases = $this->dashman->databases(); if($databases): ?>
						<div class="dropdown">
							<button
							type="button"
							class="btn btn-outline-secondary dropdown-toggle"
							data-toggle="dropdown"
							title="Switch to a different database"
							aria-label="Log out">
								Switch database
							</button>
							<div class="dropdown-menu">
								<?php foreach($databases as $db): ?>
								<a href="<?php echo site_url('switch-database/'.$db) ?>" class="dropdown-item">
									<?php echo $db ?>
								</a>
								<?php endforeach ?>
							</div>
						</div>
						<?php endif ?>
						<a href="<?php echo site_url('scafolda-settings') ?>" class="btn btn-outline-secondary mx-2" title="Settings" aria-label="settings">
							<i class="fa fa-cog" title="settings"></i>
						</a>
						<a href="<?php echo site_url('logout') ?>" class="btn btn-warning" title="logout" aria-label="Log out">
							Logout
						</a>
					</form>
				</div>
			</nav>

			<div class="container-fluid">
				<aside id="notifications">
				<?php $alert = $this->dashman->get_message();
				
				if (isset($alert['type'])):
					switch ($alert['type']) {
						case 'success':
							$icon = '<span class="fa fa-check-circle" style="margin-right:10px"></span>';
							break;
						case 'warning':
							$icon = '<span class="fa fa-exclamation-circle" style="margin-right:10px"></span>';
							break;
						case 'danger':
							$icon = '<span class="fa fa-exclamation-circle" style="margin-right:10px"></span>';
							break;
						default:
						$icon = '<span class="fa fa-info-circle" style="margin-right:10px"></span>';
							break;
					}
					?>
					<div class="alert bg-<?= $alert['type'] ?>">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
							<span class="sr-only">Close</span>
						</button>
						<?= $icon.$alert['message'] ?>
					</div>
				<?php endif; ?>
				</aside>