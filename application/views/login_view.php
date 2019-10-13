	<!DOCTYPE html>
<html>
<head>
	<title>Dataspark Login</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/bootstrap.min.css') ?>" />
	<!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
	<style type="text/css">
		body {
			font-family: sans-serif;
			font-size: 14px;
			font-weight: 300;
		}
		.contain-sm {
		  width: 100%;
		  margin: 0px auto;
		}
		@media screen and (min-width: 34em) {
		  .contain-sm {
		    width: 353px;
		  }
		}
	</style>
</head>

<body class="">
	<div class="container-fluid">
		<div class="row">
			<div class="col-xs-12 col-sm-8 col-md-6 col-lg-4 center-block" style="margin:auto;float:initial;margin-top:2.5%">
				<?php echo form_open(current_url()); ?>
					<div class="card">
						<div class="card-body bg-secondary text-white">
							<div class="help-block">Connect to your database</div>
						</div>
						
						<hr class="m-0">
						
						<div class="card-body">
							
							<!-- Error Alerts -->
							<?php if ($message): ?>
								<p class="alert bg-danger text-white"><?php echo $message; ?></p>
							<?php else: ?>
								<?php if ($redirect): ?>
								<p class="alert bg-warning">Your session expired.</p>
								<?php endif ?>
							<?php endif ?>

							<div class="form-group">
								<div class="row">
									<div class="col-md-3">
										<label class="font-weight-bold mt-2" for="validateHostname">Hostname</label>
									</div>
									<div class="col-md-9">
										<input type="text" id="validateHostname" name="hostname" value="<?= set_value('hostname') ?>"
										class="form-control <?php echo form_error('hostname') ? 'is-invalid' : '' ?>"
										/>
										<?php if (form_error('hostname')): ?>
											<div class="invalid-feedback"><?php echo form_error('hostname') ?></div>
										<?php endif ?>
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-md-3">
										<label class="font-weight-bold mt-2" for="validateDatabase">Database</label>
									</div>
									<div class="col-md-9">
										<input type="text" id="validateDatabase" name="database" value="<?= set_value('database') ?>"
										class="form-control <?php echo form_error('database') ? 'is-invalid' : '' ?>"
										/>
										<?php if (form_error('database')): ?>
											<div class="invalid-feedback"><?php echo form_error('database') ?></div>
										<?php endif ?>
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-md-3">
										<label class="font-weight-bold mt-2" for="validateUsername">Username</label>
									</div>
									<div class="col-md-9">
										<input type="text" id="validateUsername" name="username" value="<?= set_value('username') ?>"
										class="form-control <?php echo form_error('username') ? 'is-invalid' : '' ?>"
										/>
										<?php if (form_error('username')): ?>
											<div class="invalid-feedback"><?php echo form_error('username') ?></div>
										<?php endif ?>
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-md-3">
										<label class="font-weight-bold mt-2" for="validatePassword">Password</label>
									</div>
									<div class="col-md-9">
										<input type="password" id="validatePassword" name="password" value="<?= set_value('password') ?>"
										class="form-control <?php echo form_error('password') ? 'is-invalid' : '' ?>"
										/>
										<?php if (form_error('password')): ?>
											<div class="invalid-feedback"><?php echo form_error('password') ?></div>
										<?php endif ?>
									</div>
								</div>
							</div>

							<button type="submit" name="login" id="submit" value="submit" class="btn btn-block btn-primary">
								<small>CONNECT</small>
							</button>
						</div>
					<?php echo form_close() ?>
				</div>
				<div class="d-flex justify-content-between text-muted my-3">
					<a href="<?php echo site_url('docs').'#authentication' ?>" target="_blank">I have trouble connecting</a>
					<small class="mt-1"><?php echo 'Dataspark &copy '.date('Y') ?></small>
				</div>
		</div>
	</div><!-- /.container -->
</body>
</html>
