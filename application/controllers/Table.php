<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Table controller functions.
 *
 * @category Controller
 * @package  Dashman
 * @author   Ignatius Yesigye <ignatiusyesigye@gmail.com>
 * @license  MIT <http://opensource.org/licenses/MIT>
 * @link     null
 */
class Table extends CI_Controller
{
    /**
	 * Display table data
	 *
	 * @access	public
	 * @param	$table 	Table name
	 * 
	 * @return	response
	 */
	public function view($table)
	{
		$this->table->set($table);
		$this->load->helper('text');
		
		$fields = $this->map->tableFields($table, ['references' => true]);
		
		if($fields) {
			$fields = $this->map->orderFields($fields);
			$form_fields = $this->map->tableFields($table, ['references' => true]);
	
			if ($this->input->post('create')) $this->_handleInsert($table, $form_fields);

			// User posted a rename request.
			if ($this->input->post('table_rename')) {
				// Validate submitted form.
				$this->form_validation->set_rules('new_name', 'New Name', 'trim|required|differs[old_name]');

				if ($this->form_validation->run()) {
					// Attempt to rename the table.
					if ($this->table->rename($this->input->post('new_name'))) {
						// success.
						$this->dashman->set_message($this->lang->line('success_table_rename'));
						redirect($database.'/'.$this->input->post('new_name'));
					} else {
						// fail.
						$this->dashman->set_message($this->lang->line('error_table_rename'), 'error');
						redirect(current_url());
					}
				}
			}
			
			// User posted a auto generate request.
			if ($this->input->post('auto_generate')) {                                                  
				// Validate submitted form.
				$this->form_validation->set_rules('rowsNo', 'Rows', 'trim|required');
				
				if ($this->form_validation->run()) {
					// Attempt to generate data.
					if ($this->table->auto_generate($this->input->post('rowsNo'))) {
						// success
						$this->dashman->set_message($this->table->rows.' rows were generated');
					} else {
						// fail
						$this->dashman->set_message($this->table->error(), 'error');
					}
					redirect(current_url());
				}
			}
			
			// meta data for fields in this table
			$fieldsMeta = $this->table->columnsData();

			// User posted a generate request.
			if ($this->input->post('generate')) {
				// Validate submitted form.
				$this->form_validation->set_rules('rows', 'Number of rows', 'trim|required');
				
				foreach($this->input->post('insert') as $field => $row) {
					// Setting validation rules.
					if (!$fieldsMeta[$field]->null) {
						$this->form_validation->set_rules('insert['.$field.']', $field, 'required');
					}
				}
				
				if ($this->form_validation->run()) {
					// Attempt to generate data.
					if ($this->table->generate($this->input->post('insert'), $this->input->post('rows'))) {
						// success
						$this->dashman->set_message($this->table->rows.' rows were generated');
					} else {
						// fail
						$this->dashman->set_message($this->table->error(), 'error');
					}
					redirect(current_url());
				}
			}
	
			foreach ($form_fields as $key => $field) {
				if (is_object($field->foreign_key)) {
					// Import foreign table values for select input
					$options = $this->_generateOptions($field->foreign_key->table, $field->foreign_key->field);
					$selected = [];
					$field->attr = ['class' => "form-control multiselect"];
					$field->type = 'select';
					$field->options = $options;
					$field->selected = $selected;
				}
	
				if ($field->type == 'image') {
					unset($form_fields[$key]);
				}
			}
			
			// Define the number of items per page.
			// Found in the application/config/dashman.php file
			$this->load->config('dashman');
			$page_limit = $this->config->item('page_limit');
			
			// Define view page's data
			$this->data['active'] = $table;
			$this->data['table'] = $table;
			$this->data['database'] = $this->session->userdata('database');
			$this->data['fields'] = $fields;
			$this->data['form_fields'] = $form_fields;
			$this->data['primary'] = $this->guess->uniqueKey($table);
			$this->data['page_limit'] = $page_limit;
			$this->config->load('faker');
			$providers =  $this->config->item('providers');
			$this->data['providerJson'] = json_encode($providers);
			
			$this->load->view('table/table_rows_view', $this->data);
		} else {
			$this->data['active'] = $table;
			$this->data['table'] = $table;
			$this->data['message'] = "Table cannot be edited directly";
			$this->load->view('view_table_error', $this->data);
		}
	}
	
	/**
	 * Display row data
	 *
	 * @access	public
	 * @param	$table 	Table name
	 * @param	$key 	row identifier
	 * 
	 * @return	response
	 */
	public function row($table, $position)
	{
		$this->table->set($table);

		// Check for multiple delete form
		if ($this->input->post('delete')) {
			$this->_handleDeleteMultiple($table, [$position]);
		}

		$table_fields = $this->map->tableFields($table, ['references' => true]);

		if ($this->input->post('update')) {
			
			if ($this->table->updateEntity($table, $position, $table_fields)) {
				$this->dashman->set_message('Data updated successful');
			} else {
				$this->dashman->set_message('Data could not be updated'.
				(validation_errors() ? ' because of form errors' : null ), 'error');
			}

			// Reload page if there are no validation errors.
			if (!validation_errors()) redirect(current_url());
		}

		if ($this->input->post('upload_image')) {
			
			if ($this->dash->uploadImage($table, $position, $this->input->post('field_name'))) {
				$this->dashman->set_message('Data updated successful');
			} else {
				$this->dashman->set_message('Data could not be updated'.
				(validation_errors() ? ' because of form errors' : null ), 'error');
			}

			// Reload page if there are no validation errors.
			if (!validation_errors()) redirect(current_url());
		}
		
		$table_fields = $this->map->orderFields($table_fields);
		
		$fields = [];
		$select_fields = [];
		$image_fields = [];
		foreach ($table_fields as $key => $field) {
			if ($field->type === 'image') {
				array_push($image_fields, $field);
				array_push($select_fields, $field->name);
			} else {
				array_push($fields, $field);
				array_push($select_fields, $field->name);
			}
		}

		$references = $this->map->tableReferences($table);
		$row_data = $this->table->getRowData($table, $position, $select_fields);

		$page_title = '';
		
		if (!empty($row_data)) {
			
			$screen_column = '';

			foreach ($fields as $key => $value) {
				// Setting default value as actual row data
				// This value will be set in the form inputs
				if(isset($row_data[$value->name])) $value->default = $row_data[$value->name];
			}

			foreach ($image_fields as $key => $value) {
				// Setting default value as actual row data
				// This value will be set in the form inputs
				if(isset($row_data[$value->name])) $value->default = $row_data[$value->name];
			}
			
			if ($screen_column = $this->guess->screenName($table))  {
				$page_title = $row_data[$screen_column];
			}
		}

		$foreign_selects = [];
		
		foreach ($references['pivoted'] as $ref_table) {
			$data = $this->dash->table_data($ref_table, [
				'pivot' => $table,
				'fields_limit' => 2,
			]);
			
			$columns = [];
			if ( ! empty($data)) {
				foreach ($data[0] as $key => $value) {
					if($key !== '_position_') array_push($columns, $key);
				}
			}
			$selected = $this->dash->getReferenceData($table, $ref_table, $position);
			
			$options = [];
			
			foreach ($data as $key => $row) {
				foreach ($columns as $column) {
					if ($column == '_ref_') continue;
					if (isset($row['_ref_'])) {
						$pos = $row['_ref_'];
						$options[$pos] = $row[$column];
					}
				}
			}
			
			array_push($foreign_selects, [
				'name' => $ref_table,
				'options' => $options,
				'selected' => $selected,
			]);
		}
		
		$breadcrumbs = [
			0 => array('name'=>$table, 'link'=>$table),
			1 => array('name'=>$page_title, 'link'=>false),
		];
		
		$this->data['table'] = $table;
		$this->data['ref_table'] = null;
		$this->data['breadcrumbs'] = $breadcrumbs;
		$this->data['pivot_references'] = $foreign_selects;
		$this->data['direct_references'] = $references['direct'];
		$this->data['fields'] = $fields;
		$this->data['image_fields'] = $image_fields;
		$this->data['row'] = $row_data;
		$this->data['page_title'] = $page_title;
		$this->data['position'] = $position;

		// // Load the view page.
		$this->load->view('view_row_data', $this->data);
	}

	/**
	 * Display row data of a referencing table
	 *
	 * @access	public
	 * @param	$table 	   Table name
	 * @param	$position  row identifier
	 * @param	$ref_table referencing table
	 * 
	 * @return	response
	 */
	public function rowRef($table, $position, $ref_table)
	{
		// Check for multiple delete form
		if ($this->input->post('delete')) {
			$this->_handleDeleteMultiple($table, [$position]);
		}
		
		$row_data = $this->dash->getReferencedData($table, $ref_table, $position);
		
		if ($this->input->post('upload_image')) {
			
			if ($this->dash->uploadImage($ref_table, $row_data['_position_'], $this->input->post('field_name'))) {
				$this->dashman->set_message('Data updated successful');
			} else {
				$this->dashman->set_message('Data could not be updated'.
				(validation_errors() ? ' because of form errors' : null ), 'error');
			}
			
			// Reload page if there are no validation errors.
			if (!validation_errors()) redirect(current_url());
		}

		$table_fields = $this->map->tableFields($ref_table, ['references' => true]);
		
		if ($this->input->post('update')) {
			
			if (empty($row_data)) {
				$is_updated = $this->dash->addEntity($ref_table, $table_fields);
			} else {
				$is_updated = $this->dash->updateEntity($ref_table, $position, $table_fields);
			}

			if ($is_updated) {
				$this->dashman->set_message('Data updated successful');
			} else {
				$this->dashman->set_message('Data could not be updated'.
				(validation_errors() ? ' because of form errors' : null ), 'error');
			}
			
			// Reload page if there are no validation errors.
			if (!validation_errors()) redirect(current_url());
		}
		
		$ref_data = $this->map->refTable($table, $ref_table);
		$parent_data = $this->dash->getRowData($table, $position, [
			$ref_data['referenced_column_name'].' AS ref_value',
			$this->guess->screenName($table).' AS page_title'
		]);
		$parent_references = $this->map->tableReferences($table);
		$references = $this->map->tableReferences($ref_table);
		
		$fields = [];
		$image_fields = [];
		foreach ($table_fields as $key => $field) {
			$field_name = $field->name;
			$foreign_key = $field->foreign_key;
			// Setting default value as actual row data. To be set in the form inputs
			if(isset($row_data[$field->name])) $field->default = $row_data[$field->name];
			// Hide referencing column
			if($ref_data['column_name'] == $field->name) {
				$field->type = 'hidden';
				continue;
			}

			
			if (is_object($field->foreign_key)) {
				// Import foreign table values for select input
				$options = $this->_generateOptions($field->foreign_key->table, $field->foreign_key->field);
				$selected = [];
				
				$field = new StdClass();
				$field->type = 'select';
				$field->name = $field_name;
				$field->null = true;
				$field->attr = ['class' => "form-control multiselect"];
				$field->options = $options;
				$field->selected = $selected;
			}
			
			if ($field->type === 'image') {
				array_push($image_fields, $field);
			} else {
				array_push($fields, $field);
			}
		}

		$foreign_selects = [];
		foreach ($references['pivoted'] as $pivot_ref_table) {
			$data = $this->dash->table_data($pivot_ref_table, [
				'parent' => $table,
				'fields_limit' => 2,
			]);
			
			$columns = [];
			if ( ! empty($data)) {
				foreach ($data[0] as $key => $value) {
					array_push($columns, $key);
				}
			}
			$selected = $this->dash->getReferenceData($table, $ref_table, $position);
			
			$options = [];
			foreach ($data as $key => $row) {
				foreach ($columns as $column) {
					if ($column == '_ref_') continue;
					$pos = $row['_ref_'];
					$options[$pos] = $row[$column];
				}
			}
			
			$field = new StdClass();
			$field->type = 'select';
			$field->name = $ref_table.'[]';
			$field->null = true;
			$field->label = $ref_table;
			$field->attr = ['class' => "form-control multiselect", 'multiple' => "multiple"];
			$field->options = $options;
			$field->selected = $selected;

			array_push($foreign_selects, $this->load->view('form_fields', ['fields' => [0 => $field]], true));
		}
		
		$breadcrumbs = [
			0 => array('name'=>$table, 'link'=>$table),
			1 => array('name'=>$parent_data['page_title'], 'link'=>false),
		];
		if ($ref_table) $breadcrumbs[2] = ['name'=>$ref_table, 'link'=>false];

		// Define view page's data
		$this->data['page_title'] = $parent_data['page_title'];
		$this->data['reference_value'] = $parent_data['ref_value'];
		$this->data['reference_column'] = $ref_data['column_name'];

		$this->data['active'] = $table;
		$this->data['position'] = $position;
		$this->data['breadcrumbs'] = $breadcrumbs;
		$this->data['ref_table'] = $ref_table;
		$this->data['table'] = $table;
		$this->data['fields'] = $fields;
		$this->data['image_fields'] = $image_fields;
		$this->data['pivot_references'] = $foreign_selects;
		$this->data['ref_data'] = $ref_data;
		$this->data['row'] = $row_data;
		$this->data['direct_references'] = $parent_references['direct'];
		
		$this->load->view('view_row_data', $this->data);
	}

	/**
	 * View table foreign keys.
     * 
     * @param $database database name
     * @param $table    table name
     * 
     * @return response
	*/
	public function indexes($table)
	{
		$this->table->set($table);

		// User posted a delete key request.
		if ($key = $this->input->post('delete_key')) {

			if ($this->table->deleteIndex($key)) {
				// success
				$this->dashman->set_message($this->lang->line('success_index_delete', $key));
			} else {
				// fail
				$error = $this->table->error();
				$this->dashman->set_message($error['message'], 'error');
			}
			redirect(current_url());
		}

		// Define template data.
		$this->data['table'] = $table;
		$this->data['meta'] = $this->table->getIndexes();
		// Load view tempate.
		$this->load->view('table/table_indexes_view', $this->data);
	}

	/**
	 * View table foreign keys.
     * 
     * @param $database database name
     * @param $table    table name
     * 
     * @return response
	*/
	public function foreign_keys($table)
	{
		$this->table->set($table);

		// User posted a delete key request.
		if ($key = $this->input->post('delete_key')) {

			if ($this->table->deleteForeignKey($this->input->post('column'), $key)) {
				// success
				$this->dashman->set_message($key.' was deleted successfully');
			} else {
				// fail
				$this->dashman->set_message('key colud not be added', 'error');
			}
			redirect(current_url());
		}

		// Define template data.
		$this->data['table'] = $table;
		$this->data['meta'] = $this->table->referenceMeta();
		// Load view tempate.
		$this->load->view('table/table_foreignKeys_view', $this->data);
	}
	
	/**
	 * View and modify table columns.
     * 
     * @param $database database name
     * @param $table    table name
     * 
     * @return response
	*/
	public function columns($table)
	{
		$this->table->set($table);

		// User posted a generate request.
		if ($this->input->post('update_columns')) {
			$this->load->library('form_validation');
			foreach($this->input->post('update') as $field => $row) {
				// Setting validation rules.
				$this->form_validation->set_rules('update['.$field.'][name]', $field, 'required');
				$this->form_validation->set_rules('update['.$field.'][type]', $field, 'required');
			}
			
			// Validate submitted form.
			if ($this->form_validation->run()) {
				// Atempt to edit table fields.
				if ($this->table->editColumns($this->input->post('update'))) {
					// success
					$this->dashman->set_message('Columns have been updated');
				} else {
					// fail
					$this->dashman->set_message('Columns could not be updated', 'error');
				}
				redirect(current_url());
			}
		}
		
		// User posted a delete column request.
		if ($column = $this->input->post('delete_column')) {

			// Atempt to add a column.
			if ($this->table->deleteColumn($column)) {
				// success
				$this->dashman->set_message('Column was removed successfully');
			} else {
				// fail
				$this->dashman->set_message('Column colud not be removed', 'error');
			}
			redirect(current_url());
		}

		// User posted an add key request.
		if ($this->input->post('add_column')) {
			// Atempt to add a column.
			if ($this->table->addColumn($this->input->post())) {
				// success
				$this->dashman->set_message('Column was added successfully');
			} else {
				// fail
				$this->dashman->set_message('Column colud not be added', 'error');
			}
			redirect(current_url());
		}

		// User posted an add key request.
		if ($this->input->post('add_key')) {

			// Atempt to add a column.
			if ($this->table->addkey($this->input->post())) {
				// success
				$this->dashman->set_message('Key was added successfully');
			} else {
				// fail
				$this->dashman->set_message('Key colud not be added', 'error');
			}
			redirect(current_url());
		}

		

		// meta data for fields in this table
		$fieldsMeta = $this->table->columnsData();
		// Get columns that can be referenced.
		// They be used as options in a select input.
		$refs = $this->database->referenceableColumns();
		$refereceables = array();
		$refereceables[''] = 'SELECT'; // empty option
		foreach ($refs as $meta) {
			$refereceables[$meta['table'].'.'.$meta['column']] = $meta['table'].'.'.$meta['column'];
		}

		// Define template data.
		$this->data['table'] = $table;
        $this->data['columns'] = $fieldsMeta;
		$this->data['refereceables'] = $refereceables;
		$this->data['indexKeys'] = array(
			'primary', 'unique', 'foreign',
		);
		$this->data['refOptions'] = array(
			'NO ACTION', 'CASCADE', 'RESTRICT', 'SET DEFAULT', 'SET NULL'
		);
		// Load view tempate.
		$this->load->view('table/table_columns_view', $this->data);
	}

    /**
	 * Crete database table.
     * 
     * @return response
	*/
	public function create()
	{
		if ($this->input->post('new_table')) {
			// Set validation rules.
			$this->load->library('form_validation');
			$row_id = 0;
			$row_ids = array();
			foreach($this->input->post('insert') as $id => $row) {
				// Identify rows using standard counting starting from 1 not 0.
				$row_id++;
				// Save row indexes incase validation fails and the form needs to be repopulated.
				$row_ids[] = $id;
				$this->form_validation->set_rules('table_name', 'Table Name', 'required');
				$this->form_validation->set_rules('insert['.$id.'][name]', ' ', 'required');
				$this->form_validation->set_rules('insert['.$id.'][type]', ' ', 'required');
				// The following fields are not validated,
				// however must be included or their data will not be repopulated by CI.
				$this->form_validation->set_rules('insert['.$id.'][length]');
				$this->form_validation->set_rules('insert['.$id.'][data]');
				$this->form_validation->set_rules('insert['.$id.'][default]');
				$this->form_validation->set_rules('insert['.$id.'][null]');
				$this->form_validation->set_rules('insert['.$id.'][auto]');
				$this->form_validation->set_rules('insert['.$id.'][unsigned]');
				$this->form_validation->set_rules('insert['.$id.'][index]');
				$this->form_validation->set_rules('insert['.$id.'][foreign_key]');
				$this->form_validation->set_rules('insert['.$id.'][on_update]');
				$this->form_validation->set_rules('insert['.$id.'][on_delete]');
			}

			if ($this->form_validation->run()) {
				$newTbl = $this->input->post('table_name');
				$fields = $this->input->post('insert');
				$this->table->set($newTbl);
				
				// Attempt to create table.
				if($this->table->create($fields)) {
					// success
					// Define data to insert into table.
					$insertData = array();
					foreach ($this->input->post('insert') as $field) {
						// Define data type for row that is no a foreign key.
						$insertData[$field['name']] = $field['data'];
					}
					// Attempt to insert data.
					if ($this->table->generate($insertData, $this->input->post('rows'))) {
						// rows were successfully added.
						$this->dashman->set_message($this->lang->line('success_row_generate', $this->table->rows));
					} else {
						// Table created but rows not added.
						$this->dashman->set_message($this->lang->line('success_table_create'), 'success');
					}
					// Go to table page.
					redirect($database.'/'.$newTbl);
				} else {
					// fail.
					$this->dashman->set_message($this->lang->line('error_table_create'), 'error');
					redirect(current_url());
				}
			} else {
				// Define positions where validation errors occured.
				$this->data['validation_row_ids'] = $row_ids;
			}
		}
		// Get columns that can be referenced.
		// They be used as options in a select input.
		$this->database->set($this->session->userdata('database'));
		$refs = $this->database->referenceableColumns();
		$refereceables = array();
		$refereceables[''] = 'NONE'; // empty option
		foreach ($refs as $meta) {
			$this->data['table'] = null;
			$refereceables[$meta['table'].'.'.$meta['column']] = $meta['table'].'.'.$meta['column'];
		}
		
		$this->data['fields'] = 5;
		$this->data['tables'] = [];
		$this->data['refereceables'] = $refereceables;

		// Display the create page to show errors.
		$this->load->view('table/table_create_view', $this->data);
	}

	/**
	 * Delete database table.
	 *
     * @param $table table name
     * 
     * @return response
	*/
	public function delete($table)
	{
		$this->table->set($table);
		// Atempt to delete the table.
		if ($this->table->delete()) {
			// success
			$this->dashman->set_message("Table delete successfully");
			// back to database page.
			redirect();
		} else {
			// fail
			$this->dashman->set_message("Table could not be deleted", 'error');
			// back to database page.
			redirect($table);
		}
	}

    /**
	 * Handle insert data request
	 *
	 * @param	$table 	Table name
	 * @return	response
	 */
    private function _handleInsert($table, $fields)
    {
        if ($this->table->addEntity($table, $fields)) {
			$table_count = $this->db->count_all($table);
            $latest = $this->table->lastRow($table);
            $caption = $this->guess->screenName($table);
            $this->dashman->set_message(
                anchor($table.'/'.$table_count, $latest[$caption]).' was added successfully');
        } else {
            if(!validation_errors()) $this->dashman->set_message('Data could not be added', 'error');
        }
        
        // Reload page if there are no validation errors.
        if (!validation_errors()) redirect(current_url());
	}

    /**
	 * Handle multiple delete data request
	 *
	 * @param	$table 	Table name
	 * @param	$keys 	Positions of rows
	 * @return	response
	 */
    private function _handleDeleteMultiple($table, $keys = [])
	{
		$this->table->set($table);

		if (empty($keys)) {
			$keys = $this->input->post('selected');
			// Load the form validation library.
			$this->load->library('form_validation');
			// Set validation rules.
			$this->form_validation->set_rules('selected[]', 'above', 'required');
			$this->form_validation->set_message('required', 'Select some items first.');
	
			if ($this->form_validation->run() !== true) {
				$this->dashman->set_message('Check the rows you want deleted first', 'error');
				return false;
			}
		}
		
		$num_deleted = $this->table->deleteRows($keys);
		
		if ($num_deleted > 0) {
			$this->dashman->set_message("$num_deleted Row".(($num_deleted !== 1) ? "s were": "was")." deleted");
			// Reload table page.
			redirect($table);
		} else {
			$this->dashman->set_message('Rows could not be deleted', 'error');
			// Reload same page.
			redirect(current_url());
		}
	}

	private function _generateOptions($table, $field) {
		$select_options = [];
		$data = $this->dash->table_data($table, [
			'reference' => $field,
		]);

		foreach ($data as $key => $row) {
			$select_options[$row['_ref_']] = $row['_ref_'].(isset($row['name']) ? ' - '.$row['name'] : null);
		}

		return $select_options;
	}
}

?>