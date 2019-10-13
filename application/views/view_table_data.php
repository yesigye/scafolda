<?php $this->load->view('includes/header', array(
	'title' => $table,
	'pageTitle' => $table.' <button type="button" class="btn btn-sm btn-primary ml-2" data-toggle="modal" data-target="#modal-addNew">
            <i class="fa fa-plus mr-1"></i> New
        </button>',
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

<?php $this->load->view('table_data_header', array(
	'active' => 'rows',
	'position' => 'rows',
)); ?>

<div class="table-responsive mt-3" id="table-container">
    <table class="table" id="item-table">
        <thead>
            <tr>
                <th>Id</th>
                <?php foreach ($fields as $field): $field_type = $this->dashman->guessField($field); ?>
                <th
                class="form-group 
                <?php echo ($field_type == 'boolean' || $field_type == 'image') ? 'text-center' : null // center images ?>
                <?php echo ($field_type == 'int' || $field_type == 'double') ? 'text-right' : null // right alight figures ?>
                "
                ><?php echo $field->name ?></th>
                <?php endforeach ?>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody class="text-truncate"></tbody>
    </table>
</div>

<?php echo form_open_multipart() // Form to add new record ?>
    <div class="modal fade" tabindex="-1" role="dialog" id="modal-addNew" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><?= $table ?> - Add a new record</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">

                    <?php if (validation_errors() && $this->input->post('create')): // This form triggered validation errors ?>
                    <div class="alert alert-danger" data-trigger-modal="#modal-addNew">
                        This form has errors. Correct them and try again.
                    </div>
                    <?php endif ?>

					<p class="py-2 text-muted">
                        Fields marked <span class="text-danger">*</span> are required and must not be left blank.
                    </p>
                    <?php $this->load->view('form_fields', ['fields' => $form_fields]) // Load a view with form fields ?>
				</div>
				<div class="modal-footer">
					<button type="submit" name="create" value="create" class="btn btn-primary">Submit</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>
<?php echo form_close() ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
	// Define new create item modal
	var create_modal = $('#user-create-modal');
 
    // Delete a record
    $('#item-table').on('click', '.row_remove', function (e){
        e.preventDefault();
        var row = $(this).closest('tr');
        var row_id = row.attr('id');

        if (confirm("Are sure you want to delete row " + row_id)){
            $.ajax({
                url: '<?php echo base_url('api/table/delete/'.$table.'/') ?>' + row_id,
                type: 'DELETE',
                success: function(result){
                    table.row(row).deselect().remove().draw();
                },
            });
        }
        table.row(row).deselect();
    });
	
	// Define datatable
	var table = $('#item-table').DataTable({
		"processing": true,
		"serverSide": true,
		"rowId": '_position_',
		"pageLength": <?php echo $page_limit ?>,
		"ajax": "<?php echo site_url('api/table/fetch/'.$table) ?>",
		dom: 'Bfrtip',
		buttons: [
			{
				extend: "colvis",
				className: "btn-sm btn-secondary",
				titleAttr: 'Toggle visible columns',
				text: 'Columns'
			},
			{
				extend: "copy",
				className: "btn-sm btn-secondary",
				titleAttr: 'Copy to clipboard',
				text: 'Copy'
			},
			{
				extend: "excel",
				className: "btn-sm btn-secondary",
				titleAttr: 'Export in Excel',
				text: 'Excel'
			},
			{
				extend: "pdf",
				className: "btn-sm btn-secondary",
				titleAttr: 'Export in PDF',
				text: 'PDF'
			},
			{
				extend: 'selected',
				text: 'Delete selected',
				className: "btn-sm btn-danger",
				action: function ( e, dt, button, config ) {
					// Delete multiple selected items
					$.ajax({
						url: '<?php echo base_url('api/table/delete/'.$table) ?>',
						type: 'POST',
						data: {
							ids: table.columns().checkboxes.selected()[0],
							'<?php echo $this->security->get_csrf_token_name() ?>':'<?php echo $this->security->get_csrf_hash() ?>'
						},
						success: function(result) {
                            console.log(result)
							table.rows('.selected').deselect().draw();
						},
					});
				}
			}
		],
		columns: [
            { data: "_position_" },
            <?php foreach ($fields as $field): $field_type = $this->dashman->guessField($field); ?>
            {
                data: "<?php echo $field->name ?>",

                // Center align and format images
                <?php if($field_type == 'image'): ?>
                className: "text-center",
                render: function (data, type, row) {
					return '<img src="'+data+'" style="width:30px">';
				},
                <?php endif ?>

                // Center align and format booleans
                <?php if($field_type == 'boolean'): ?>
                className: "text-center",
                <?php endif ?>

                // Right align figures
                <?php if($field_type == 'int' || $field_type == 'double'): ?>
                className: "text-center",
                <?php endif ?>
            },
            <?php endforeach ?>
            {
                data: null,
                className: "text-center",
                defaultContent: '<span class="close row_remove font-weight-light float-none" aria-label="Close">×</span>'
            }
        ],
		columnDefs: [{
			targets: 0,
			checkboxes: {
				selectRow: true,
			}
		}],
		select: {
			style: 'multi'
		},
		order: [[1, 'asc']],
		"drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-sm');
        },
    });
});
</script>

<script>
window.addEventListener("load", function() {
    var valueOld;
    var valueNew;
    $('#item-table').on('click', '.inline-content', function(){
        var el_input = $(this).closest('.inline-editor').find('.inline-field');
        valueOld = el_input.val();
        $(this).hide()
        el_input.val('').show().focus().val(valueOld)
    }).on('blur', '.inline-field', function() {
        valueNew = $(this).val();
        $(this).hide();
        var div = $(this).closest('.inline-editor').find('.inline-content')
        div.show()
        if (handleUpdate($(this), div)) {
            el_input.val('').show().focus().val(valueOld)
        }
    }).on('keyup', '.inline-field', function(e) {
        valueNew = $(this).val();
        if (e.which == 13) {
            e.preventDefault();
            handleUpdate($(this));
            $(this).blur();
            return false;
        } else {
            var el_content = $(this).closest('.inline-editor').find('.inline-content');
            var el_input = $(this).closest('.inline-editor').find('.inline-field');
            el_content.html(valueNew)
        }
    });
    
    $('#item-table').on('change', '.inline-checkbox', function() {
        valueOld = ($(this).is(':checked')) ? 0 : 1;
        handleUpdate($(this));   
    });
    
    function handleUpdate(input, el) {
        if(input.attr('type') == 'checkbox') {
            var valueNew = (input.is(':checked')) ? 1 : 0;
        } else {
            var valueNew = input.val();
        }
            
        var key = input.attr('data-id');
        var col = input.attr('data-col');
        
        if (valueOld !== valueNew) {
            $.ajax({
                url: "<?= site_url('api/table/edit/'.$table) ?>",
                method: 'POST',
                dataType: 'json',
                data: {
                    <?= $this->security->get_csrf_token_name() ?> : "<?= $this->security->get_csrf_hash() ?>",
                    position: key,
                    data: { [col]: valueNew }
                },
                success: function(response) {
                    if (response.error) {
                        $('body').append(errorTemplate(response.message));
                        $('#alertModal').modal('show').on("hidden.bs.modal", function (e) {
                            $(this).data("bs.modal", null).remove();
                        });
                        if(input.attr('type') == 'checkbox') {
                            valueNew ? input.prop('checked', 0) : input.prop('checked', 1);
                        } else {
                            el.html(valueOld)
                            input.val(valueOld);
                        }
                    }else {
                        return true;
                    }
                },
                error: function(xhr, status, error) {
                    $('body').append(errorTemplate(status));
                    $('#alertModal').modal('show').on("hidden.bs.modal", function (e) {
                        $(this).data("bs.modal", null).remove();
                    });
                    if(input.attr('type') == 'checkbox') {
                        valueNew ? input.prop('checked', 0) : input.prop('checked', 1);
                    } else {
                        el.html(valueOld)
                        input.val(valueOld);
                    }
                },
            });
        }
    }

    function errorTemplate(message) {
        return `<div class="ajax-modal modal" id="alertModal">
            <div class="vertical-alignment-helper">
                <div class="modal-dialog modal-sm vertical-align-center">
                    <div class="modal-content">
                        <div class="alert alert-danger m-0">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            `+message+`
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    }
    
    function warnTemplate(message) {
        return `<div class="alert bg-warning alert-dismissible fade show fixed-top mx-auto" style="width:fit-content;" role="alert">
            `+message+`
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>`;
    }

});
</script>

<?php $this->load->view('includes/footer', array(
	'scripts' => array(
		'<script type="text/javascript" src="'.base_url('assets/vendor/datatables/datatables.min.js').'"></script>',
		'<script type="text/javascript" src="'.base_url('assets/vendor/datatables/dataTables.checkboxes.min.js').'"></script>',
	)
)) ?>