<?php $this->load->view('includes/header') ?>

<?php $this->load->view('table/table_header', array(
	'active' => 'columns',
)); ?>

<?php if (validation_errors()): ?>
    <div class="alert alert-danger">Check your entries and try again.</div>
<?php endif ?>

<?= form_open(current_url()) ?>
    <div class="d-flex justify-content-between">
        <div class="mb-1 text-right">
            <span class="badge badge-pill badge-secondary"><?php echo count($columns) ?></span> Columns
        </div>

        <div class="mb-3">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-column">
            <i class="fa fa-plus"></i> Column
        </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Length</th>
                    <th>Default</th>
                    <th class="text-center">Null</th>
                    <th class="text-center">Unsigned</th>
                    <th class="text-center" title="auto increment">Auto</th>
                    <th class="text-center"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($columns as $column): ?>
                <tr>
                    <td>
                        <?=
                        form_input(array(
                            'name'  => 'update['.$column->name.'][name]',
                            'value' => (set_value('update['.$column->name.'][name]')) ? set_value('update['.$column->name.'][name]') : $column->name,
                            'class' => "form-control"
                        )); ?>
                    </td>
                    <td>
                        <?=
                        form_dropdown('update['.$column->name.'][type]',
                        $this->dashman->field_types(),
                        (set_value('update['.$column->name.'][type]')) ? set_value('update['.$column->name.'][type]') : strtoupper($column->type),
                        array("class"=> "form-control"))
                        ?>
                    </td>
                    <td>
                        <?= form_input(array(
                            "name"      => 'update['.$column->name.'][length]',
                            "class"     => 'form-control '.(form_error('update['.$column->name.'][length]') ? 'is_invalid' : ''),
                            "type"      => 'number',
                            "value"     => (set_value('update['.$column->name.'][length]')) ? set_value('update['.$column->name.'][length]') : $column->max_length,
                            "maxlength" => 220,
                            "style"     => "width:80px"
                        )); ?>
                        <div class="invalid-feedback"><?php echo form_error('update['.$column->name.'][length]') ?></div>
                    </td>
                    <td>
                        <?=
                        form_input(array(
                            'name'  => 'update['.$column->name.'][default]',
                            'value' => (set_value('update['.$column->name.'][default]')) ? set_value('update['.$column->name.'][default]') : $column->default,
                            'class' => "form-control"
                        ));
                        ?>
                    </td>
                    <td class="text-center">
                        <div class="custom-control custom-checkbox">
                            <?=
                            form_checkbox(array(
                                'class' => "custom-control-input",
                                'id' => 'check-null-'.$column->name,
                                'name'  => 'update['.$column->name.'][null]',
                                'checked' => $column->null ? 'checked' : '',
                            ), $value = 1, set_checkbox('update['.$column->name.'][null]'));
                            ?>
                            <label class="custom-control-label" for="check-null-<?php echo $column->name ?>"></label>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="custom-control custom-checkbox">
                            <?=
                            form_checkbox(array(
                                'class' => "custom-control-input",
                                'id' => 'check-unsigned-'.$column->name,
                                'name'  => 'update['.$column->name.'][unsigned]',
                                'checked' => $column->unsigned ? 'checked' : '',
                            ), $value = 1, set_checkbox('update['.$column->name.'][unsigned]'));
                            ?>
                            <label class="custom-control-label" for="check-unsigned-<?php echo $column->name ?>"></label>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="custom-control custom-checkbox">
                            <?=
                            form_checkbox(array(
                                'class' => "custom-control-input",
                                'id' => 'check-auto-'.$column->name,
                                'name'  => 'update['.$column->name.'][auto]',
                                'checked' => $column->auto ? 'checked' : '',
                            ), $value = 1, set_checkbox('update['.$column->name.'][auto]'));
                            ?>
                            <label class="custom-control-label" for="check-auto-<?php echo $column->name ?>"></label>
                        </div>
                    </td>
                    <td class="text-center">
                        <button type="submit" name="delete_column" value="<?= $column->name ?>" class="close float-none">
                            &times
                        </button>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <div class="text-right">
        <a data-toggle="modal" href="#addKey-modal">Create a key columns</a>
    </div>

    <button class="btn btn-success btn-labeled" name="update_columns" value="1">
        <span class="btn-label"><i class="fa fa-edit"></i></span> Save
    </button>
<?= form_close() ?>

<?= form_open(current_url()) ?>
    <div class="modal fade" id="add-column">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Add a new column</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
                <div class="modal-body">
                    <div class="form-group <?php echo form_error('name') ? 'has-error' : '' ?>">
                        <label for="name">Name</label>
                        <?=
                        form_input('name',
                        (set_value('name')) ? set_value('name') : '',
                        array("class"=> "form-control"))
                        ?>
                        <?php echo form_error('name'); ?>
                    </div>

                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#genAuto" role="tab" aria-controls="genAuto" aria-selected="false">
                                Constraints
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#genData" role="tab" aria-controls="genData" aria-selected="true">
                                Foreign Key
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content pt-2">
                        <div class="tab-pane active" id="genAuto" role="tabpanel" aria-labelledby="genAuto-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group <?php echo form_error('type') ? 'has-error' : '' ?>">
                                        <label for="type">Type</label>
                                        <?=
                                        form_dropdown('type',
                                        $this->dashman->field_types(),
                                        (set_value('type')) ? set_value('type') : 'INT',
                                        array("class"=> "form-control"))
                                        ?>
                                        <?php echo form_error('type'); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group <?php echo form_error('length') ? 'has-error' : '' ?>">
                                        <label for="length">Length</label>
                                        <?= form_input(array(
                                            "name"      => 'length',
                                            "class"     => "form-control",
                                            "type"      => "number",
                                            "value"     => (set_value('length')) ? set_value('length') : '40',
                                            "maxlength" => "220",
                                        )); ?>
                                        <?php echo form_error('length') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('default') ? 'has-error' : '' ?>">
                                <label for="default">Default</label>
                                <?=
                                form_input('default',
                                (set_value('default')) ? set_value('default') : '',
                                array("class"=> "form-control"))
                                ?>
                                <?php echo form_error('default') ?>
                            </div>
                            <div class="form-group <?php echo form_error('index') ? 'has-error' : '' ?>">
                                <label for="index">Index</label>
                                <?=
                                form_dropdown('index',
                                array("" => "", "primary" => "PRIMARY", "unique" => "UNIQUE"),
                                (set_value('index')) ? set_value('index') : '',
                                array("class"=> "form-control", "style"=> "min-width:100px"))
                                ?>
                                <?php echo form_error('index') ?>
                            </div>
                            <div class="form-group <?php echo form_error('null') ? 'has-error' : '' ?>">
                                <?=
                                form_checkbox('null',
                                "NULL", (set_value('null')) ? TRUE : FALSE)
                                ?>
                                Nullable
                                <?php echo form_error('null') ?>
                            </div>
                            <div class="form-group <?php echo form_error('auto') ? 'has-error' : '' ?>">
                                <?=
                                form_checkbox('auto',
                                "AUTO", (set_value('auto')) ? TRUE : FALSE)
                                ?>
                                Auto Increment
                                <?php echo form_error('auto') ?>
                            </div>
                            <div class="form-group <?php echo form_error('unsigned') ? 'has-error' : '' ?>">
                                <?=
                                form_checkbox('unsigned',
                                "Unsigned", (set_value('unsigned')) ? TRUE : FALSE)
                                ?>
                                Unsigned
                                <?php echo form_error('unsigned') ?>
                            </div>
                        </div>
                        <div class="tab-pane" id="genData" role="tabpanel" aria-labelledby="genData-tab">
                            <p class="text-muted">
                                Index this column as a foreign key.
                                Contraints will be auto generated for a foreign key.
                            </p>
                            <div class="form-group">
                            <?=
                                form_dropdown('foreign_key',
                                $refereceables,
                                (set_value('foreign_key')) ? set_value('foreign_key') : '',
                                array("class"=> "form-control", "style"=> "min-width:100px"))
                                ?>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="on_update" style="margin:0">On update</label>
                                    <?=
                                    form_dropdown('on_update',
                                    array('NO ACTION'=>'NO ACTION','CASCADE'=>'CASCADE'),
                                    set_value('on_update') ? set_value('on_update') : '',
                                    array("class"=> "form-control", "style"=> "min-width:100px"))
                                    ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="on_delete" style="margin:0">On delete</label>
                                    <?=
                                    form_dropdown('on_delete',
                                    array('NO ACTION'=>'NO ACTION','CASCADE'=>'CASCADE'),
                                    set_value('on_delete') ? set_value('on_delete') : '',
                                    array("class"=> "form-control", "style"=> "min-width:100px"))
                                    ?>
                                </div>
                            </div>
                            <?php echo form_error('foreign_key') ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_column" value="add" class="btn btn-primary">Insert</button>
                </div>
			</div>
		</div>
    </div>
<?= form_close() ?>

<?= form_open(current_url()) ?>
    <div class="modal fade" id="addKey-modal">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add a new index</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="column">Column</label>
                        <select class="form-control <?php echo (form_error('column')) ? 'is-invalid' : '' ?>" name="column" required>
                            <?php foreach($columns as $column): ?>
                            <option value="<?= $column->name ?>" <?= set_select('column', $column->name) ?>><?= $column->name ?></option>
                            <?php endforeach ?>
                        </select>
                        <?php if (form_error('column')): ?>
                            <div class="invalid-feedback"><?php echo form_error('column') ?></div>
                        <?php endif ?>
                    </div>

                    <div class="form-group">
                        <label for="key">Key</label>
                        <select class="form-control <?php echo (form_error('key')) ? 'is-invalid' : '' ?>" name="key" required>
                            <?php foreach($indexKeys as $val): ?>
                            <option value="<?= $val ?>" <?= set_select('key', $val) ?>><?= $val ?></option>
                            <?php endforeach ?>
                        </select>
                        <?php if (form_error('key')): ?>
                            <div class="invalid-feedback"><?php echo form_error('key') ?></div>
                        <?php endif ?>
                    </div>
                    
                    <div id="foreignRefSelect" style="display:none">
                    <?php if (!empty($refereceables)): ?>
                        <label for="reference">Reference</label>
                        <select class="form-control <?php echo (form_error('reference')) ? 'is-invalid' : '' ?>" name="reference" required>
                            <?php foreach($refereceables as $val): ?>
                            <option value="<?= $val ?>" <?= set_select('reference', $val) ?>><?= $val ?></option>
                            <?php endforeach ?>
                        </select>
                        <?php if (form_error('reference')): ?>
                            <div class="invalid-feedback"><?php echo form_error('reference') ?></div>
                        <?php endif ?>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="on_delete">On delete</label>
                                <select class="form-control <?php echo (form_error('on_delete')) ? 'is-invalid' : '' ?>" name="on_delete" required>
                                    <?php foreach($refOptions as $val): ?>
                                    <option value="<?= $val ?>" <?= set_select('on_delete', $val) ?>><?= $val ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="on_update">On update</label>
                                <select class="form-control <?php echo (form_error('on_update')) ? 'is-invalid' : '' ?>" name="on_update" required>
                                    <?php foreach($refOptions as $val): ?>
                                    <option value="<?= $val ?>" <?= set_select('on_update', $val) ?>><?= $val ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                    <?php else: ?>
                        <label for="reference">Reference</label>
                        <div class="text-muted">
                            No referenceable columns have been found in your tables.
                            A referenceable column must be a primary key or a unique key.
                        </div>
                    <?php endif ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_key" value="1" class="btn text-primary">Create</button>
                </div>
            </div>
        </div>
    </div>
<?= form_close() ?>

<script>
    $(document).ready(function() {
        if ($('select[name="key"]').val() == 'foreign') {
            $('#foreignRefSelect').show()
        }
        // Toggle refernce select field when foreign key is selected
        $('select[name="key"]').on('change', function () {
            if (this.value == 'foreign') {
                $('#foreignRefSelect').show()
            } else {
                $('#foreignRefSelect').hide()
            }
        })
        <?php if ($this->input->post('add_key')): ?>
            // Toggle modal to display form errors
            $('#addKey-modal').modal('show')
        <?php endif ?>
    })
</script>

<?php $this->load->view('includes/footer') ?>