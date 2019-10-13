<!DOCTYPE html>
<html>
<head>
	<title>Dashman Login</title>
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
			<div class="col-xs-12 col-sm-8 col-md-4 center-block" style="margin:auto;float:initial;margin-top:2%">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">Problem connecting to your database</div>
                    <div class="card-body">
                        We could not connect to your database with the database credentials you provided.
                        
                        <table class="table table-striped table-borderless my-3">
                            <tr>
                                <th>Hostname</th>
                                <td><?php echo $hostname ? $hostname : '<span class="text-muted">not set<span>' ?></td>
                            </tr>
                            <tr>
                                <th>Database</th>
                                <td><?php echo $database ? $database : '<span class="text-muted">not set<span>' ?></td>
                            </tr>
                            <tr>
                                <th>Username</th>
                                <td><?php echo $username ? $username : '<span class="text-muted">not set<span>' ?></td>
                            </tr>
                            <tr>
                                <th>Password</th>
                                <td><?php echo $password ? $password : '<span class="text-muted">not set<span>' ?></td>
                            </tr>
                        </table>

                        <div class="text-right">
                            <a href="<?php echo site_url('docs').'#auth-coded' ?>" target="_blank">Get more help</a>
                        </div>
                    </div>

                    <div class="card-footer alert alert-danger m-0 rounded-0 small">
                        <div class="font-weight-bold text-uppercase">Database error: <?php echo $code; ?></div>
                        <?php echo $message; ?>
                    </div>
                    
				</div>
				<div class="text-right text-muted my-3">
					<small><?php echo 'DASHMAN &copy '.date('Y') ?></small>
				</div>
            </div>
		</div>
	</div><!-- /.container -->
</body>
</html>