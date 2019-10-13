<?php $this->load->view('includes/header', array(
	'title' => "$table - $page_title",
	'pageTitle' => $this->dashman->icon($table)." <span class=\"ml-2\">$page_title</span>",
	'breadcrumb' => $breadcrumbs,
	'styles' => array(
		'<link rel="stylesheet" href="'.base_url('assets/css/jasny-bootstrap.min.css').'">',
		'<link rel="stylesheet" href="'.base_url('assets/css/cropper.min.css').'">',
		'<link rel="stylesheet" href="'.base_url('assets/js/multiselect/bootstrap-select.min.css').'">',
		)
)); ?>

<?php if(empty($row) AND !$ref_table): ?>
	<div class="my-5 text-muted">
		We could not find what you are looking for.
		This entry could have been deleted or altered.
    </div>
<?php else: ?>

<?php $this->load->view('table_data_header'); ?>

<div class="card-columns cards-2">
	
	<?php echo form_open(current_url()) ?>
	<div class="card border-0">
		<?php if(isset($reference_column) && isset($reference_value)) echo form_hidden($reference_column, $reference_value) ?>
		<p class="py-2 text-muted">
			Fields marked <span class="text-danger">*</span> are required and must not be left blank.
		</p>
		<?php $this->load->view('form_fields', ['fields' => $fields]); ?>
	
		<div class="py-4">
			<button type="submit" name="update" class="btn btn-flat btn-primary" value="update">
				Update
			</button>
		</div>
	</div>

	<div class="card border-0">
		<div class="mt-4">
			<?php foreach($pivot_references as $key => $ref): ?>
			<div class="form-group">
				<label class="label row">
					<div class="col-6"><?= $ref['name'] ?></div>
					<a href="<?= site_url($ref['name']) ?>" class="col-6 text-right small" target="_blank">
						view all
					</a>
					<div class="col-12 small text-muted">Choose from list below. Multiple select is enabled.</div>
				</label>
				<?php echo form_dropdown($ref['name'].'[]', $ref['options'], $ref['selected'], [
				'class' => "form-control multiselect",
				'multiple' => "multiple"]) ?>
			</div>
			<?php endforeach ?>
		</div>
	</div>
	<?php echo form_close() ?>
	
	<div class="card border-0">
	<?php foreach($image_fields as $key => $image_field): ?>
		<?php echo form_open_multipart(current_url()) ?>
		<?php if(isset($reference_column) && isset($reference_value)) echo form_hidden($reference_column, $reference_value) ?>
		<div class="form-group mb-4">
			<label class="m-0" for=""><?php echo $image_field->name ?></label>
			<?php if($image_field->default): ?>
				<img src="<?php echo $image_field->default ?>" class="d-block mt-2 img-fluid img-thumbnail">
			<?php endif ?>
			<button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#edit-image-<?=$key?>">
				<?= ($image_field->default) ? 'Change' : 'Upload' ?>
			</button>

			<div class="modal" id="edit-image-<?=$key?>">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Change image</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">

							<?php if (validation_errors() && $this->input->post('upload_image')): // This form triggered validation errors ?>
							<div class="alert alert-danger" data-trigger-modal="#edit-image-<?=$key?>">
								This form has errors. Correct them and try again.
							</div>
							<?php endif ?>
							<?php echo form_hidden('field_name', $image_field->name) // Image field name	 ?>
							<?php $this->load->view('form_fields', ['fields' => [$image_field]]) // Load an image form widget ?>
						</div>
						<div class="modal-footer">
							<button type="submit" name="upload_image" value="1" class="btn btn-primary">Submit</button>
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php echo form_close() ?>
	<?php endforeach ?>
	</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", init);

function init() {
	$(".multiselect").selectpicker({
		liveSearch: true,
		style: "border",
	});

	// Set Counter
	$('.card').on('change', 'input[type="checkbox"]', function() {
		checkBoxCounter(this);
	});
}

function checkBoxCounter(el) {
	var card  = $(el).closest('.card');
	var count = card.find('input[type="checkbox"]').filter(':checked').length;
	var counter = card.find('.counter');
	
	counter.html(count).show();
	if (count == 0) {
		counter.hide();
	}
}

function toggleIcon(icon) {
	$('#accordion').find('[data-toggle="collapse"]').find('.fa').each(function(){
		$(this).removeClass('fa-minus').addClass('fa-plus');
	});
	
	icon.toggleClass('fa-plus');
	icon.toggleClass('fa-minus');
	return icon.hasClass('fa-minus');
}
</script>

<?php endif ?>

<?php $this->load->view('includes/footer', array(
	'scripts' => array(
		'<script type="text/javascript" src="'.base_url('assets/js/jasny-bootstrap.min.js').'"></script>',
		'<script type="text/javascript" src="'.base_url('assets/js/cropper.min.js').'"></script>',
		'<script type="text/javascript" src="'.base_url('assets/js/multiselect/bootstrap-select.min.js').'"></script>',
	)
)) ?>