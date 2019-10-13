<div class="row">
	<?php foreach ($fields as $key => $field_data):
		$is_auto = ($field_data->extra === 'auto_increment') ? true : false;
		// Setting defaults
		$inputCol   = 'col-md-12';
		$inputHelp  =  $is_auto ? 'This field is set to automatically increment' : '';
		$input_attrs  =  $is_auto ? 'disabled 	' : '';
		
		$attrs = isset($field_data->attr) ? $field_data->attr : ['class' => 'form-control'];
		
		if (form_error($field_data->name)) {
			// Adding BS4 error validation class for inputs with errors
			$attrs['class'] = (isset($attrs['class']) ? $attrs['class'] : '').' is-invalid';
		}
		
		// Format attributes array to a string.
		foreach ($attrs as $attr => $value) {
			if (trim($value) !== '') $input_attrs .= ($attr.'="'.$value.'"');
		}

		if (!isset($field_data->default)) $field_data->default = '';
		$field_data->default = set_value($field_data->name, $field_data->default);

		if (isset($field_data->foreign_key) && $field_data->foreign_key) {
			// Set a special help message for foreign key fields
			$count = $this->db->count_all($field_data->foreign_key->table);
			if ($count == 0 && $field_data->foreign_key->table !== $table)
				$inputHelp = '<span class="text-danger">Please enter data in the '.anchor($field_data->foreign_key->table, $field_data->foreign_key->table).' table first.</span>';
		}

		?>

		<div class="<?= $inputCol // BS4 Columns for input widths ?>">
			<div class="form-group">
				<?php if($field_data->type !== 'boolean' && $field_data->type !== 'hidden') {
					// Input Label
					if (isset($field_data->label)) {
						if (!isset($field_data->nolabel)) echo form_label($field_data->label, $field_data->label, ['class'=>'control-label mb-1']);
					} else {
						if (!isset($field_data->nolabel)) echo form_label($field_data->name, $field_data->name, ['class'=>'control-label mb-1']);
					}
					// Input is required
					if ($field_data->null == false) echo '<span class="text-danger font-weight-bold" data-toggle="tooltip" title="required"> *</span>';
					
					// Input Help Text
					echo $inputHelp ? '<div class="form-text text-muted mt-0 mb-1">'.$inputHelp.'</div>' : '';
				} ?>

				<?php
				// Generate different form inputs depending on field types
				switch ($field_data->type):
				case 'upload': // Upload fragment ?>
					<div class="custom-file">
						<input type="file" class="custom-file-input" id="customFile">
						<label class="custom-file-label" for="customFile">Choose file</label>
					</div>
				<?php break; ?>

				<?php case 'image': // Image upload fragment ?>
					<?php // Javascript Cropper will write into these fields
					
					if (is_array($field_data->default)) {

						$field_data->default = set_value("$field_data->name[file]", isset($field_data->default['file']) ? $field_data->default['file'] : '');
					}

					echo form_hidden("$field_data->name[crop_x]", '');
					echo form_hidden("$field_data->name[crop_y]", '');
					echo form_hidden("$field_data->name[crop_width]", '');
					echo form_hidden("$field_data->name[crop_height]", '');
					?>
					<script>
						document.addEventListener("DOMContentLoaded", function() {
							$('.fileinput').on('change.bs.fileinput', function (e) {
								$('.fileinput-preview img').cropper({
									crop: function(e) {
										$('input[name=\"<?= $field_data->name ?>[crop_width]\"]').val(e.width);
										$('input[name=\"<?= $field_data->name ?>[crop_height]\"]').val(e.height);
										$('input[name=\"<?= $field_data->name ?>[crop_x]\"]').val(e.x);
										$('input[name=\"<?= $field_data->name ?>[crop_y]\"]').val(e.y);
									}
								});
							})
						});
					</script>

					<div class="card border-secondary">
						<div class="card-header">
							<div class="row">
								<div class="col-4">
									<h5 class="card-title m-0"><?= $field_data->name ?></h5>
								</div>
								<div class="col-8">
									<ul class="nav nav-tabs card-header-tabs justify-content-end" role="tablist">
										<li role="presentation" class="nav-item">
											<a class="nav-link active" href="#<?= $field_data->name ?>-file-upload" role="tab" data-toggle="tab">Upload</a>
										</li>
										<li role="presentation" class="nav-item">
											<a  class="nav-link" href="#<?= $field_data->name ?>-file-input" role="tab" data-toggle="tab">URL</a>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="card-body tab-content">
							<div role="tabpanel" class="tab-pane active" id="<?= $field_data->name ?>-file-upload">
								<div class="fileinput fileinput-new card m-auto border-0" data-provides="fileinput" style="width:100%">
									<div class="fileinput-new thumbnail text-muted">
										<?php if ($field_data->default): ?>
											<img src="<?php echo $field_data->default ?>">
										<?php else: ?>
											<div class="mx-2 my-4">No image selected</div>
										<?php endif ?>
									</div>
									<div class="fileinput-preview fileinput-exists thumbnail"></div>
									<div class="card-footer p-0">
										<div class="btn-group btn-block">
											<div class="btn btn-sm btn-secondary btn-file">
												<span class="fileinput-new">Select</span>
												<span class="fileinput-exists">Change</span>
												<input type="file" name="<?= $field_data->name."\$file$" ?>">
											</div>
											<a href="#" class="btn btn-sm btn-danger fileinput-exists" data-dismiss="fileinput">Remove</a>
										</div>
									</div>
								</div>
								<p class="text-muted my-2 font-weight-bold">Resize Image</p>
								<div class="row mb-2">
									<div class="col-6">
										<input type="text" name="<?= "$field_data->name[resize][width]" ?>" value="<?= set_value($field_data->name.'[resize][width]') ?>" <?= $input_attrs ?>>
									</div>
									<div class="col-6">
										<input type="text" name="<?= "$field_data->name[resize][hight]" ?>" value="<?= set_value($field_data->name.'[resize][hight]') ?>" <?= $input_attrs ?>>
									</div>
								</div>
								<?php $is_checked = set_checkbox($field_data->name, '1') ? 'checked="checked"' : null; ?>
							</div>
							<div role="tabpanel" class="tab-pane" id="<?= $field_data->name ?>-file-input">
								<p class="text-muted mb-2">Enter image URL link</p>
								<input type="text" name="<?= "$field_data->name[file]" ?>"  value="<?= $field_data->default ?>" <?= $input_attrs ?>>
							</div>
						</div>
					</div>
					<?php break; ?>
						
				<?php case 'boolean': // Checkbox fragment ?>
					<?php $is_checked = ($field_data->default || set_checkbox($field_data->name, '1')) ? 'checked="checked"' : null; ?>
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input" name="<?= $field_data->name ?>" id="<?= "check_$field_data->name" ?>" <?= $is_checked ?>>

						<?php if (isset($field_data->label)): // Input Label ?>
							<label for="<?= "check_$field_data->name" ?>" class="custom-control-label"><?= $field_data->label ?></label>
						<?php else: ?>
							<label for="<?= "check_$field_data->name" ?>" class="custom-control-label"><?= $field_data->name ?></label>
						<?php endif ?>

						<?php if ($field_data->null == false): // Input is required ?>
							<span class="text-danger font-weight-bold"> *</span>
						<?php endif ?>
					</div>
					<?php break; ?>
				<?php case 'select': // Select input fragment ?>
					<?php echo form_dropdown($field_data->name, $field_data->options, $field_data->selected, $input_attrs); ?>
					<?php break; ?>

				<?php case 'hidden': // Hidden input fragment ?>
					<input type="hidden" name="<?= $field_data->name ?>" value="<?= $field_data->default ?>" <?= $input_attrs ?>>
					<?php break; ?>

				<?php  case 'password': // Password input fragment ?>
					<input type="password" name="<?= $field_data->name ?>" value="<?= $field_data->default ?>" <?= $input_attrs ?>>
					<?php break; ?>

				<?php  case 'textarea': ?>
					<textarea name="<?= $field_data->name ?>" <?= $input_attrs ?>><?= $field_data->default ?></textarea>
					<?php break; ?>
		
				<?php  default: // Default input fragment ?>
					<input type="text" name="<?= $field_data->name ?>" value="<?= $field_data->default ?>" <?= $input_attrs?>>
					<?php break; ?>
				<?php endswitch ?>
				
				<?php if (form_error($field_data->name)): // Validation error feedback ?>
					<div class="text-danger small"><?= form_error($field_data->name) ?></div>
				<?php endif ?>
			</div>
		</div>
	<?php endforeach ?>
</div>