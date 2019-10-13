<?php $this->load->view('includes/header'); ?>

<section class="row mt-3" id="info-boxes">
	<div class="col-md-3">
		<div class="card card-body mb-4">
			<div class="card-title mb-1">CPU Speed</div>
			<div class="row text-success">
				<div class="col-md-4 h1 m-0">
					<i class="fa fa-dashboard"></i>
				</div>
				<div class="col-md-8 text-muted text-right">
					<b class="info-box-number" id="speed-test">0 GHZ</b>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card card-body mb-4">
			<div class="card-title mb-0">Database</div>
			<div class="row text-info">
				<div class="col-md-3 h1 mb-1">
					<i class="fa fa-database text-muted"></i>
				</div>
				<div class="col-md-9 text-muted text-right">
					<div class="info-box-number"><?php echo $this->db->platform() ?></div>
					<div class="small text-truncate text-muted" title="<?php echo $this->db->version() ?>"><?php echo $this->db->version() ?></div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card card-body mb-4">
			<div class="card-title mb-1">Database Queries</div>
			<div class="row text-info">
				<div class="col-md-3 h1 m-0">
					<i class="fa fa-file-text text-danger"></i>
				</div>
				<div class="col-md-9 text-muted text-right">
					<b class="info-box-number"><?php echo $this->db->total_queries() ?></b>
					<div class="small text-truncate" title="<?php echo $this->db->last_query() ?>">
						<i class="fa fa-info-circle"></i> last query executed	
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card card-body mb-4">
			<div class="card-title mb-1">Tables</div>
			<div class="row text-info">
				<div class="col-md-3 h1 m-0">
					<i class="fa fa-table text-info"></i>
				</div>
				<div class="col-md-9 text-muted text-right">
					<b class="info-box-number"><?php echo count($table_list) ?></b>
				</div>
			</div>
		</div>
	</div>
</section>

<section>
	<div class="table-responsive">
		<table class="table">
			<thead>
				<th>Table</th>
				<th class="text-center">Size</th>
				<th class="text-center">Rows</th>
				<th class="text-center">Engine</th>
				<th class="text-center">Generate Rows</th>
			</thead>
			<tbody>
				<?php foreach($table_list as $tbl): ?>
				<tr>
					<td>
						<a href="<?php echo site_url($tbl['table_name']) ?>">
							<?php echo $tbl['table_name'] ?>
						</a>
						<div class="small">
							<?php if($tbl['created']) echo 'Created: '.$tbl['created'] ?>
							<?php if($tbl['updated']) echo ' | Updated: '.$tbl['updated'] ?>
						</div>
					</td>
					<td class="text-center"><?php echo $tbl['size'] ?></td>
					<td class="text-center"><?php echo $tbl['table_rows'] ?></td>
					<td class="text-center">
						<?php echo $tbl['Engine'] ?>
						<div class="small text-muted"><?php echo $tbl['table_collation'] ?></div>
					</td>
					<td>
						<?php echo form_open(current_url(), 'class="form-inline"') ?>
						<input type="hidden" class="d-none" name="table" value="<?php echo $tbl['table_name'] ?>">
						<div class="input-group mb-3 mx-auto">
							<input
							type="number"
							name="rows"
							class="form-control"
							style="width:130px"
							placeholder="number"
							required
							>
							<div class="input-group-append">
								<button class="btn btn-secondary" name="auto_generate" value="go" type="submit">GO</button>
							</div>
						</div>
						<?php echo form_close() ?>
					</td>
				</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</div>
	
	<nav class="btn-group">
		<a href="<?php echo site_url('download-database/'.$this->db->database) ?>" class="btn btn-outline-secondary">Export</a>
	</nav>

	<div class="modal fade" id="modal-import">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Import sql file</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<?= form_open_multipart(current_url()) ?>
						<div class="form-group">
							<input type="file" class="form-control <?= form_error('userfile') ? 'is-invalid' : '' ?>" name="userfile" required/>
							<div class="invalid-feedback">
								<?php if (form_error('userfile')): ?>
									<?= form_error('userfile') ?>
								<?php else: ?>
									You did not upload a file
								<?php endif ?>
							</div>
						</div>
						
						<button type="submit" name="import" value="1" class="btn btn-primary">
							Import
						</button>
					<?= form_close() ?>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
	speedTest();	
	function speedTest() {
		setTimeout(function() {
			// document.getElementById("speed-test").innerHTML = speedTest();
			var _speedconstant = 1.15600e-8; //if speed=(c*a)/t, then constant=(s*t)/a and time=(a*c)/s
			var d = new Date();
			var amount = 150000000;
			var estprocessor = 1.7; //average processor speed, in GHZ
			for (var i = amount; i>0; i--) {} 
			var newd = new Date();
			var accnewd = Number(String(newd.getSeconds())+"."+String(newd.getMilliseconds()));
			var accd = Number(String(d.getSeconds())+"."+String(d.getMilliseconds())); 
			var di = accnewd-accd;
			if (d.getMinutes() != newd.getMinutes()) {
			di = (60*(newd.getMinutes()-d.getMinutes()))+di}
			spd = ((_speedconstant*amount)/di);
			html = Math.round(spd*1000)/1000+"GHZ";
			// Update DOM
			document.getElementById("speed-test").innerHTML = html;
			// Recursive loop this function.
			speedTest();
		}, 1000); // updates every second.
	}
</script>

<?php $this->load->view('includes/footer'); ?>