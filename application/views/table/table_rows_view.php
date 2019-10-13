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

<?php $this->load->view('table/table_header', array(
	'active' => 'rows',
)); ?>

<nav class="mt-2">
	<div class="d-flex justify-content-between">
		<div class="btn-group">
			<span id="table-toggle-cols"></span>
			<div class="dropdown mx-1">
				<button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					Generate
				</button>
				<div id="table-imports" class="dropdown-menu" aria-labelledby="dropdownMenuButton">
				</div>
			</div>
			<div class="dropdown">
				<button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					export
				</button>
				<div id="table-exports" class="dropdown-menu" aria-labelledby="dropdownMenuButton">
				</div>
			</div>
			<div class="dropdown ml-1">
				<button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					Schema
				</button>
				<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
					<a class="nav-link" href="<?php echo site_url($table.'/columns') ?>">
						<i class="fa fa-columns mr-2"></i> Columns
					</a>
					<a class="nav-link" href="<?php echo site_url($table.'/foreign_keys') ?>">
						<i class="fa fa-key mr-2"></i> Foreign Keys
					</a>
					<a class="nav-link" href="<?php echo site_url($table.'/indexes') ?>">
						<i class="fa fa-bars mr-2"></i> Indexes
					</a>
				</div>
			</div>
		</div>
		<div class="dropdown">
			<button class="btn btn-outline-danger dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				Remove
			</button>
			<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
				<span id="table-removals"></span>
				<div class="dropdown-divider"></div>
				<a class="dropdown-item" href="#deltbl-modal" data-toggle="modal" aria-label="Delete table groups" title="Remove the entire table including all its data">
					<i class="fa fa-exclamation-circle mr-2"></i> Remove table
				</a>
			</div>
		</div>
	</div>
</nav>

<section class="collapse <?php if (validation_errors() AND $this->input->post('generate')) echo "show";  ?>" id="generate-section">
	<div class="card card-body bg-light mt-3">
		<?php if (isset($insert_fail)): ?>
		<div class="modal-body">
			<div class="alert alert-warning">
				<p style="margin-bottom:10px">
					<strong>Foreign key contraint!</strong>
					you must first generate data for referenced table(s)
				</p>

				<ol class="list-group">
					<?php foreach ($insert_fail as $field): ?>
					<li class="list-group-item">
						<?php echo anchor('database/'.$database.'/'.$field, $field) ?>
					</li>
					<?php endforeach ?>
				</ol>
			</div>
		</div>
		<?php else: ?>
		<ul class="nav nav-tabs" id="myTab" role="tablist">
			<li class="nav-item">
				<a class="nav-link active" data-toggle="tab" href="#genAuto" role="tab" aria-controls="genAuto" aria-selected="false">
					Auto
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" data-toggle="tab" href="#genData" role="tab" aria-controls="genData" aria-selected="true">
					Choose Data
				</a>
			</li>
		</ul>
		<div class="tab-content pt-2">
			<div class="tab-pane active" id="genAuto" role="tabpanel" aria-labelledby="genAuto-tab">
				<p class="text-muted form-text">Let the system choose data to be generated for each field.</p>
				<?php echo form_open(current_url()) ?>
					<div class="form-group">
						<label for="rowsNo">Rows</label>
						<input type="number" class="form-control <?php echo (form_error('rowsNo')) ? 'is-invalid' : '' ?>" name="rowsNo" value="<?= set_value('rowsNo') ? set_value('rowsNo') : 10 ?>" required>
						<small class="form-text text-muted">How many rows do you want generated.</small>
						<?php if (form_error('rowsNo')): ?>
							<div class="invalid-feedback"><?php echo form_error('rowsNo') ?></div>
						<?php endif ?>
					</div>
					
					<div class="d-flex justify-content-between">
						<input type="submit" name="auto_generate" value="Generate" class="btn btn-primary">
						<input type="reset" class="btn btn-secondary" value="cancel" data-toggle="collapse" data-target="#generate-section">
					</div>
				<?php echo form_close() ?>
			</div>
			<div class="tab-pane" id="genData" role="tabpanel" aria-labelledby="home-tab">
				<?php echo form_open(current_url()) ?>
					<?php if (validation_errors() AND $this->input->post('generate')): ?>
						<div class="alert alert-danger">Check your entries and try again.</div>
					<?php endif ?>

					<p class="text-muted form-text">Choose the type of data to generate for each field.</p>
					<table class="table table-striped m-0">
						<?php // Table header with column/field names. ?>
						<thead class="thead-dark">
							<tr>
								<th>FIELD</th>
								<th>DATA</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($fields as $field): ?>
							<?php
							// Only show fields that are not auto incremented.
							if($field->extra !== 'auto_increment'):
							?>
							<tr>
								<th>
									<?php echo $field->name ?>
									<?php if ($field->foreign_key): ?>
										<span class="badge badge-warning"><i class="fa fa-key"></i></span>
									<?php endif ?>
								</th>
								<td>
									<?php if (isset($references[$field->name])): ?>
										<input type="input" class="form-control" value="<?=$references[$field->name]?>" disabled="disabled">
										<small class="form-text text-muted">
											The referenced column will be used to generate data.
										</small>
									<?php else: ?>
									<input
										type="text"
										class="form-control form-control-sm  <?php echo (form_error("insert[$field->name]")) ? 'is-invalid' : '' ?>"
										name="<?php echo "insert[$field->name]" ?>"
										value="<?php echo set_value("insert[$field->name]") ?>"
										data-toggle="selector">
									<div class="text-muted" data-example></div>
									<?php if (form_error('rows')): ?>
										<div class="invalid-feedback"><?php echo form_error("insert[$field->name]") ?></div>
									<?php endif ?>
									<?php endif ?>
								</td>
							</tr>
							<?php endif ?>
							<?php endforeach ?>
						</tbody>
					</table>

					<div class="form-group">
						<label for="rows">Rows</label>
						<input type="number" class="form-control <?php echo (form_error('rows')) ? 'is-invalid' : '' ?>" name="rows" value="<?= set_value('rows') ? set_value('rows') : 10 ?>" >
						<small class="form-text text-muted">How many rows do you want generated.</small>
						<?php if (form_error('rows')): ?>
							<div class="invalid-feedback"><?php echo form_error('rows') ?></div>
						<?php endif ?>
					</div>
					<div class="d-flex justify-content-between">
						<input type="submit" name="generate" value="Generate" class="btn btn-primary">
						<input type="reset" class="btn btn-secondary" value="cancel" data-toggle="collapse" data-target="#generate-section">
					</div>
				<?php echo form_close() ?>
			</div>
		</div>
		<?php endif ?>
	</div>
</section>

<section class="table-responsive mt-3" id="table-container">
	<input type="text" class="form-control form-control-lg mb-3" placeholder="type here to search table..." id="table-search">
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
                <th class="text-center"></th>
            </tr>
        </thead>
        <tbody class="text-truncate"></tbody>
    </table>
</section>

<div class="modal fade" id="modal-selectData" style="background: rgba(0, 0, 0, 0.84);">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">
					Select data type
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<nav class="nav nav-pills horizontal-scroll" data-navigation></nav>
				<div data-content>
					<p data-title class="mt-3 text-muted"></p>
					<div data-formatters></div>
				</div>
			</div>
		</div>
	</div>
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
	<?php if (validation_errors() AND $this->input->post('auto_generate')): ?>
	$(document).ready(function() {$('#generate-modal').dropdown('show')})
	<?php endif ?>
	

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
    
    function alertTemplate(message, type = "warning") {
        return `<div class="alert bg-`+type+` alert-dismissible" role="alert">
            `+message+`
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>`;
    }

	// Define new create item modal
	var create_modal = $('#user-create-modal');
	
	// Define datatable
	var table = $('#item-table').DataTable({
		"processing": true,
		"serverSide": true,
		"rowId": '_position_',
		"pageLength": <?php echo $page_limit ?>,
		"ajax": "<?php echo site_url('api/table/fetch/'.$table) ?>",
		dom: 'Bfrtip',
		searching: false,
		language: { search: '', searchPlaceholder: "Search table" },
		buttons: [],
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
            { data: "action", className: "text-center", },
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
		order: [[0, 'asc']],
		"drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-sm');
        },
    });

	new $.fn.dataTable.Buttons(table, {
		buttons: [
			{
				extend: "colvis",
				className: "btn-outline-secondary",
				titleAttr: 'Toggle visible columns',
				text: '<i class="fa fa-th-large"></i>',
				init: function(api, node, config) {
					$(node).removeClass('btn-secondary')
				}
			},
		]
	});
	table.buttons( 1, null).container().appendTo(
		$('#table-toggle-cols').append()
	);

	new $.fn.dataTable.Buttons(table, {
		buttons: [
			{
				extend: "print",
				className: "dropdown-item",
				titleAttr: 'Print table',
				text: '<i class="fa fa-print mr-2"></i> Print',
				init: function(api, node, config) {
					$(node).removeClass('btn-secondary')
					$(node).removeClass('btn')
				}
			},
			{
				extend: "excel",
				titleAttr: 'Export in Excel',
				className: "dropdown-item",
				text: '<i class="fa fa-file-excel-o mr-2"></i> Download as Excel',
				init: function(api, node, config) {
					$(node).removeClass('btn')
					$(node).removeClass('btn-secondary')
				}
			},
			{
				extend: "pdf",
				className: "dropdown-item",
				titleAttr: 'Export in PDF',
				text: '<i class="fa fa-file-pdf-o mr-2"></i> Download as PDF',
				init: function(api, node, config) {
					$(node).removeClass('btn-secondary')
					$(node).removeClass('btn')
				}
			},
			{
				className: "dropdown-item",
				titleAttr: 'Export in JSON',
				text: '<i class="fa fa-file-o mr-2"></i> Download as JSON',
				init: function(api, node, config) {
					$(node).removeClass('btn-secondary')
					$(node).removeClass('btn')
				},
				action: function ( e, dt, button, config ) {
					var data = dt.buttons.exportData();

					$.fn.dataTable.fileSave(
						new Blob( [ JSON.stringify( data ) ] ),
						'<?php echo $this->db->database." - ".$table ?>.json'
					);
				}
			},
		]
	});
	table.buttons( 2, null).container().appendTo(
		$('#table-exports').append()
	);

	new $.fn.dataTable.Buttons(table, {
		buttons: [
			{
				titleAttr: 'Add a row to the table',
				className: "dropdown-item",
				text: '<i class="fa fa-plus-circle mr-2"></i> Insert a row',
				init: function(api, node, config) {
					$(node).removeClass('btn')
					$(node).removeClass('btn-secondary')
				},
				action: function ( e, dt, node, config ) {
					$('#modal-addNew').modal('show')
				},
			},
			{
				titleAttr: 'Generate rows',
				className: "dropdown-item",
				text: '<i class="fa fa-download mr-2"></i> Generate fake data',
				init: function(api, node, config) {
					$(node).removeClass('btn')
					$(node).removeClass('btn-secondary')
				},
				action: function ( e, dt, node, config ) {
					$('#generate-section').toggleClass('show')
				},
			},
		]
	});
	table.buttons( 3, null).container().appendTo(
		$('#table-imports').append()
	);

	new $.fn.dataTable.Buttons(table, {
		buttons: [
			{
				extend: 'selected',
				titleAttr: 'Remove selected rows',
				className: "dropdown-item",
				text: '<i class="fa fa-times-circle mr-2"></i> Remove selected rows',
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
				},
				init: function(api, node, config) {
					$(node).removeClass('btn')
					$(node).removeClass('btn-secondary')
				}
			},
			{
				titleAttr: 'Remove all row from the table',
				className: "dropdown-item",
				text: '<i class="fa fa-minus-circle mr-2"></i> Empty the table',
				action: function ( e, dt, node, config ) {
					e.preventDefault();
					$.ajax({
						url: '<?php echo base_url('api/table/empty/'.$table) ?>',
						type: 'DELETE',
						success: function(response) {
							if (response.error) {
								$('body').append(errorTemplate(response.message));
							}else {
								$("#notifications").append(alertTemplate(response.message, 'success'));
								table.draw();
							}
							console.log(response)
						},
						error: function(xhr, status, error) {
								console.log(error)
							$('body').append(errorTemplate(status));
						},
					});
				},
				init: function(api, node, config) {
					$(node).removeClass('btn')
					$(node).removeClass('btn-secondary')
				}
			},
		]
	});
	table.buttons( 4, null).container().appendTo(
		$('#table-removals').append()
	);

	const tbl_search_input = document.getElementById('table-search');
	const getUrlParam = function getUrlParam(sParam) {
        let sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    };
	if(getUrlParam("search")) {
		// use url search parameter value
		table.search(getUrlParam("search")).draw();
		tbl_search_input.value = getUrlParam("search");
		window.history.replaceState(null, null, window.location.pathname);
	}
	
	tbl_search_input.addEventListener('keyup', e => {
		table.search(tbl_search_input.value).draw();
	});

    // Delete a record
    $('#item-table').on('click', '.row_remove', function (e){
        e.preventDefault();
        var row = $(this).closest('tr');
        var row_id = row.attr('id');

		// Abort if users cancels the dialog. This gets annoying
        // if (! confirm("Are sure you want to delete row " + row_id)) { return null; }
		
		$.ajax({
			url: '<?php echo base_url('api/table/delete/'.$table.'/') ?>' + row_id,
			type: 'DELETE',
			success: function(result){
				table.row(row).deselect().remove().draw();
			},
		});
        table.row(row).deselect();
    });

	// Prevent user from click button more than once.
	$('input[name="auto_generate"]').click(function(){
		var btn = $(this); btn.val('Please Wait...')
		setTimeout(function(){btn.attr('disabled', 'disabled')}, 1000)
	})
   
    var valueOld;
    var valueNew;
    $('#item-table').on('dblclick', '.inline-content', function(){
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

	$(document).on('hidden.bs.modal', function (event) {
		if ($('.modal:visible').length) {
			$('body').addClass('modal-open');
		}
	});

	var appState = {
		navs: []
	};

	var dataTypes = <?php echo $providerJson ?>;

	function renderContent(link, index, links) {
		var provider = dataTypes[link.dataset.key];
		// Set the active class
		links.forEach(function(l){ l.classList.remove('active') });
		link.classList.add('active');
		
		appTitle.innerHTML = provider.text ? provider.text : '';

		// element to hold list of formatters
		var formattersListEl = document.createElement('div');
		formattersListEl.className = "list-group list-group-flush";

		appSection.innerHTML = "";
		
		if(provider.formatters) {
			provider.formatters.forEach(function(formatter) {
				// Create a child element for every formatter
				var formatterEl = document.createElement('a');
				formatterEl.href = "#";
				formatterEl.className = "list-group-item text-dark";
				formatterEl.appendChild(document.createTextNode(formatter.name));
				formattersListEl.appendChild(formatterEl);

				if(formatter.params) {
					// For formatters that take parameters, indicate that on the UI
					formatterEl.classList.add("dropdown-toggle-right");
					// Add a click listener that generates a form for parameters
					formatterEl.addEventListener('click', function(e) {
						e.preventDefault();
						renderParameters(formatter);
					});
				} else {
					var helpEl = document.createElement('div');
					helpEl.innerHTML = 'e.g. '+formatter.example;
					helpEl.className = "small text-muted";
					formatterEl.appendChild(helpEl);
					formatterEl.addEventListener('click', function(e) {
						e.preventDefault();
						handleSelect(formatter);
					});
				}
			});
			appSection.appendChild(formattersListEl);
		}

		// Save the DOM tree of formatters
		appState.formattersListEl = formattersListEl;
	}

	function renderParameters(formatter) {
		var container = document.createElement('div');
		var nav = document.createElement('nav');
		var backBtn = document.createElement('button');
		var helpInfo = document.createElement('p');
		var enterBtn = document.createElement('button');
		var form = document.createElement('form');
		// Build the enter button element
		enterBtn.className = "btn btn-dark btn-block mt-2";
		enterBtn.innerHTML = "Select";
		enterBtn.type = "button";
		enterBtn.addEventListener('click', function(){
			handleSelect(formatter, form)
		});
		// Build the back button element
		backBtn.className = "btn btn-sm btn-secondary float-right";
		backBtn.innerHTML = "Back";
		backBtn.type = "button";
		backBtn.addEventListener('click', function(){ handleBack()});
		// Build the help info element
		helpInfo.className = "text-warning";
		helpInfo.innerHTML = "All formatters are optional and can be left blank";
		// Build the navigation element
		nav.className = "clearfix font-weight-bold";
		nav.appendChild(document.createTextNode("Select "+formatter.name+" formatters"));
		nav.appendChild(backBtn);
		nav.appendChild(helpInfo);
		// Build the form element
		form.className = "row";
		var firstInput = null;
		formatter.params.forEach(function(param, index) {
			var input = renderInput(param);
			// Create div elements to wrap around inputs
			var inputWrapper = document.createElement('div');
			inputWrapper.className = "col-6 mt-2";
			
			if(formatter.params.length % 2 && index === formatter.params.length-1) {
				inputWrapper.className = "col-12 mt-2";
			}
			// Create input elements
			inputWrapper.appendChild(input);
			form.appendChild(inputWrapper);

			if(index === 0) firstInput = input;
		})
		
		
		container.appendChild(nav);
		container.appendChild(form);
		container.appendChild(enterBtn);
		appSection.innerHTML = '';
		appSection.appendChild(container);

		// Focus the first input element
		if(firstInput) firstInput.focus();
	}

	function renderInput(param) {
		var input = document.createElement('input');
		var label = document.createElement('label');
		input.className = "form-control";
		input.placeholder = param.name;
		input.title = param.name;
		switch (param.type) {
			case 'radio':
				var html = document.createElement('div');
				html.className = "custom-control custom-radio";
				var time = new Date();
				var uniqueId = Math.random();
				input.id = uniqueId;
				input.name = param.category;
				input.value = param.value;
				input.className = "custom-control-input";
				input.type = "radio";
				label.className = "custom-control-label";
				label.setAttribute('for', uniqueId);
				label.innerHTML = param.name;
				html.appendChild(input);
				html.appendChild(label);
				return html;
				break;
			case 'checkbox':
				var html = document.createElement('div');
				html.className = "custom-control custom-checkbox";
				var time = new Date();
				var uniqueId = Math.random();
				input.id = uniqueId;
				input.name = param.category;
				input.value = param.value;
				input.className = "custom-control-input";
				input.type = "checkbox";
				label.className = "custom-control-label";
				label.setAttribute('for', uniqueId);
				label.innerHTML = param.name;
				html.appendChild(input);
				html.appendChild(label);
				return html;
				break;
			case 'number':
				input.type = "number";
				break;
			default:
				input.type = "text";
				break;
		}

		return input;
	}

	function handleBack() {
		appSection.innerHTML = "";
		appSection.appendChild(appState.formattersListEl);
	}

	function handleSelect(formatter, form) {
		let data = ''+formatter.func;

		if (form) {
			let inputs = form.querySelectorAll("input, select, textarea, radio, checkbox");
			inputs.forEach(function(input) {
				if (input.type === 'radio' || input.type === 'checkbox') {
					if (input.value && input.checked) data = data+(input.value ? '.'+input.value : '');
				} else {
					data = data+(input.value ? '.'+input.value : '');
				}
			});
		}
		appState.selectedInput.value = data;
		appState.selectedInput.nextElementSibling.innerHTML = '<small class="text-muted">example: '+formatter.example+'</small>';
		
		selectModal.modal('hide')
	}

	function startSelector() {
		if(appState.navs.length === 0) {
			for (var i = 0; i < dataTypes.length; i++) {
				var a = document.createElement('a');
				a.appendChild(document.createTextNode(dataTypes[i].name));
				a.href = "#";
				a.className = "nav-item nav-link scroll-item";
				a.setAttribute("data-key", i);
				appNav.appendChild(a);
				appState.navs = [...(appState.navs ? appState.navs : []), a];
			}
			appState.navs.forEach(function(link, index, links) {
				link.addEventListener('click', function(event) {
					renderContent(link, index, links);
				});
			});
		}
		// Render the first provider
		renderContent(appState.navs[0], 0, appState.navs);
	}

	var appNav = document.querySelector('[data-navigation]');
	var appTitle = document.querySelector('[data-title]');
	var appContent = document.querySelector('[data-content]');
	var appSection = document.querySelector('[data-formatters]');
	var selectedInputs = document.querySelectorAll('[data-toggle="selector"]');
	var selectModal = $('#modal-selectData');

	appState.selectedInputs = selectedInputs;
	
	appState.selectedInputs.forEach(function(input, index, inputs) {
		input.addEventListener('click', function(event) {
			event.preventDefault();
			selectModal.modal('show');
			startSelector();
			appState.selectedInput = input;
		});
	});
});
</script>

<?php $this->load->view('includes/footer', array(
	'scripts' => array(
		'<script type="text/javascript" src="'.base_url('assets/vendor/datatables/datatables.min.js').'"></script>',
		'<script type="text/javascript" src="'.base_url('assets/vendor/datatables/dataTables.checkboxes.min.js').'"></script>',
	)
)) ?>