<?php $this->load->view('includes/header', [
	'title' => 'Settings',
	'pageTitle' => 'Settings',
	'breadcrumb' => [
		0 => ['name'=> 'settings', 'link'=>FALSE]
    ]
]); ?>

<h4 class="py-4">Settings</h4>

<?php echo form_open() ?>
	<strong>Authentication</strong>
	<div class="custom-control custom-checkbox mb-3">
		<input name="authentication" type="checkbox" class="custom-control-input" id="check-auth"
		<?php echo ($settings['authentication']) ? 'checked="checked"' : null ?>>
		<label class="custom-control-label text-muted" for="check-auth">
			Allow dashman to handle user authentication. If left uchecked,
			you will have to cofingure your database server details in the file;
			<code>appplication/config/database.php</code> 
		</label>
	</div>

	<strong>Page Limit</strong>
	<div class="form-group mb-3">
		<div class="text-muted mb-1">
			How many records should be displayed per page.
		</div>
		<div class="form-inline">
			<input name="page_limit" type="number" class="form-control form-control-sm" value="<?php echo $settings['page_limit'] ?>">
		</div>
	</div>

	<strong>Upload File Path</strong>
	<div class="form-group mb-3">
		<div class="text-muted mb-1">
			The path for the uploaded files. The path must be writable and relavite and with a trailing slash.
		</div>
		<div class="form-inline">
			<input name="upload_file_path" type="text" class="form-control form-control-sm" value="<?php echo $settings['upload_file_path'] ?>">
		</div>
	</div>

	<strong>Column Types</strong>
	<div class="text-muted mb-1">
		Define the type of a field based on its name. These definitions are used by the script to more accurately guess
		the type of a column.
	</div>
	<table class="table table-borderless mb-3">
		<tr>
			<td class="pl-0" style="width:100px">
				Images
			</td>
			<td class="">
				<textarea name="column_name_types[image]" type="text" class="form-control form-control-sm"><?php
					foreach ($settings['column_name_types']['image'] as $key => $value) {
						echo (($key !== 0) ? ', ' : null).$value;
					}
				?></textarea>
			</td>
		</tr>
	</table>

	<strong>Icons</strong>
	<div class="text-muted my-1">
		Default Icon. This icon will be shown along tables whose icons have not been explicitly defined.
	</div>
	<div class="mb-2 input-group">
		<div class="input-group-prepend">
			<div class="input-group-text text-muted"><?php echo $settings['default_icon'] ?></div>
		</div>
		<input name="default_icon" type="text"
		class="form-control form-control-sm <?= form_error('default_icon') ? 'is-invalid' : '' ?>"
		value="<?php echo set_value('default_icon') ? set_value('default_icon') : htmlentities($settings['default_icon']) ?>">
		<div class="invalid-feedback"><?= form_error('default_icon') ?></div>
	</div>

	<div class="text-muted mt-3 mb-1">
		Icons along with the table names that the represent.
	</div>
	<table class="table table-borderless" id="icon-table">
		<?php foreach ($settings['icons'] as $key => $data): ?>
		<tr>
			<td class="pl-0 pt-4" style="width:50px">
				<?php echo $key ?>
			</td>
			<td class="">
				<?php echo form_hidden('icon_keys[]', $key) ?>
				<textarea name="icons[]" type="text" class="form-control form-control-sm"><?php
					foreach ($data as $index => $value) {
						echo (($index !== 0) ? ', ' : null).$value;
					}
				?></textarea>
			</td>
			<td>
				<button type="button" class="btn btn-sm btn-danger mt-2 remove-icon">&times</button>
			</td>
		</tr>
		<?php endforeach ?>
	</table>
	<button type="button" class="btn btn-sm btn-secondary mb-3" data-toggle="modal" data-target="#modal-addNew">Add an Icon</button>
	
	<div class="form-group">
		<hr>
		<button type="submit" name="save" value="1" class="btn btn-lg btn-primary">Save Changes</button>
	</div>
<?php echo form_close() ?>


<?php echo form_open() ?>
<div class="modal fade" tabindex="-1" role="dialog" id="modal-addNew" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form class="needs-validation" novalidate>
				<div class="modal-header">
					<h5 class="modal-title">Add a new Icon</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					Icon
					<div class="form-group mb-3">
						<label for="addIcon-icon" class="small text-muted mb-1">
							Icon HTML markup. Dashman uses font-awesome icons
						</label>
						<input type="text" id="addIcon-icon" class="form-control" placeholder="example: <i class=''fa fa-user''></i>" required>
						<div class="invalid-feedback">
							Please provide icon HTML markup.
						</div>
					</div>

					Keywords
					<label for="addIcon-keywords" class="small text-muted mb-1">
						Define the table names this icon applies to. Separate each keyword with a comma
					</label>
					<textarea id="addIcon-keywords" rows="5" class="form-control" required></textarea>
					<div class="invalid-feedback">
						Please provide table names to match the icon to.
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" id="addIcon">Submit</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?php echo form_close() ?>

<script>
document.addEventListener("DOMContentLoaded", init);
var changes = 0;

function init() {
	$('.remove-icon').click(function() {
		var row = $(this).closest('tr');
		row.addClass('fade');
		setTimeout(() => {row.remove()}, 300);
		changes++;
		alertChanges()
	})

	var icon = $('#addIcon-icon')
	var words = $('#addIcon-keywords')
	$('#modal-addNew').on('shown.bs.modal', function (e) {
		icon.focus()
	})
	$('#addIcon').click(function() {
		if (icon.val().trim() !== '' && words.val().trim() !== '') {
			var iconHtml = icon.val().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
			var template = `
				<tr>
					<td class="pl-0 pt-4" style="width:50px">`+icon.val()+`</td>
					<td class="">
						<input type="hidden" name="icon_keys[]" value="`+iconHtml+`">
						<textarea name="icons[]" type="text" class="form-control form-control-sm">`+words.val()+`</textarea>
					</td>
					<td>
						<button type="button" class="btn btn-sm btn-danger mt-2 remove-icon">&times</button>
					</td>
				</tr>
			`;
			$('#icon-table').append(template);
			// Reset form
			icon.val('')
			words.val('')
			// Hide modal
			$('#modal-addNew').modal('hide')
			changes++;
		} else {
			// Form validation failed
			if(icon.val().trim() == '') icon.addClass('is-invalid');
			if(words.val().trim() == '') words.addClass('is-invalid');
		}
	})
	$('#modal-addNew').on('hidden.bs.modal', function (e) {
		if (changes > 0 && changes < 2) {
			alertChanges()
		}
	})

	function alertChanges() {
		$(`
			<div class="alert fixed-top bg-warning container">
				Remember to save the changes you have made.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
		`).insertAfter('#icon-table')
	}
}
</script>

<?php $this->load->view('includes/footer'); ?>