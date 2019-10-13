<?php $this->load->view('includes/header', array(
	'title' => $table,
	'pageTitle' => $table,
	'breadcrumb' => array(
		0 => array('name'=>$table, 'link'=>FALSE)
    ),
	'styles' => array(
        '<link rel="stylesheet" href="'.base_url('assets/css/cropper.min.css').'">',
        '<link rel="stylesheet" href="'.base_url('assets/css/jasny-bootstrap.min.css').'">',
        '<link rel="stylesheet" href="'.base_url('assets/vendor/datatables/datatables.min.css').'">',
        '<link rel="stylesheet" href="'.base_url('assets/vendor/datatables/dataTables.checkboxes.css').'">',
    )
)); ?>

<div class="card border-danger mb-5">
    <div class="card-header bg-danger text-white">Problem reading from the table</div>
    <div class="card-body">
        We could not properly read data from this table because we could not render its columns.
        <br>
        This could be because of a number of reasons;
        <ol>
            <li>The table's columns are marked as "Blacklisted Columns" in the <?php echo anchor('dashman-settings', 'Settings') ?></li>
            <li>The table only has foreign key columns. This is especially true for pivot or mapping tables</li>
            <li>The table has too few columns</li>
        </ol>

        <div class="text-right">
            <a href="<?php echo site_url('docs').'#dash-table' ?>" target="_blank">Get more help</a>
        </div>
    </div>

    <div class="card-footer alert alert-danger m-0 rounded-0 small">
        <div class="font-weight-bold text-uppercase">Database error</div>
        <?php echo $message; ?>
    </div>
    
</div>

<?php $this->load->view('includes/footer') ?>