<?php $this->load->view('includes/header', array(
	'title' => "$table - $page_title",
	'pageTitle' => $this->dashman->icon($table)." <span class=\"ml-2\">$page_title</span>",
	'breadcrumb' => array(
		0 => array('name'=>$table, 'link'=>$table),
		1 => array('name'=>$page_title, 'link'=>false),
		1 => array('name'=>$active_tab, 'link'=>false),
	),
    'styles' => array(
        '<link rel="stylesheet" href="'.base_url('assets/css/cropper.min.css').'">',
        '<link rel="stylesheet" href="'.base_url('assets/css/jasny-bootstrap.min.css').'">'
        )
)); ?>

<?php $this->load->view('table_data_header'); ?>
insert_fields


<?php $this->load->view('includes/footer', array(
	'scripts' => array(
		'<script src="'.base_url('assets/js/jasny-bootstrap.min.js').'"></script>',
		'<script src="'.base_url('assets/js/cropper.min.js').'"></script>',
    ),
)) ?>