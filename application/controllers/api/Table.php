<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Table API functions.
 *
 * @category API
 * @package  Dashman
 * @author   Ignatius Yesigye <ignatiusyesigye@gmail.com>
 * @license  MIT <http://opensource.org/licenses/MIT>
 * @link     null
 */
class Table extends CI_Controller
{
    /**
     * Constructor
     **/
    public function __construct()
    {
        parent::__construct();
		$this->load->model('mapping_model', 'map');
        $this->load->library('json');

        // Dont allow non ajax requests
        // if ( ! $this->input->is_ajax_request()) $this->json->response('unauthorized');
    }

    /**
	 * Get table data 
	 *
	 * @param	$table 	Table name
     * 
	 * @return	response
	 */
    public function fetch(String $table)
    {
        $this->table->set($table);

        $columns = [];
        $fields = $this->map->tableFields($table, ['references' => true]);
        foreach($fields as $field) array_push($columns, $field->name);
        $options['fields'] = $columns;
        // array_values($columns);
        // var_dump($columns);

        if ($order = $this->input->get('order')) {
            $index = (int)$order[0]['column'];
            $value = ($index === 0) ? $index : $index-1;
            
            if (isset($columns[$value])) {
                $options['order'] = [
                    'column' => $columns[$value],
                    'dir' => $order[0]['dir']
                ];
            }
        }
        
        if ($search = $this->input->get('search')) $options['search'] = $search['value'];

        $options['start'] = $this->input->get('start') ? $this->input->get('start') : 0;
        $options['limit'] = $this->input->get('length');
        
        $entries = $this->table->table_data($options);

        foreach ($entries as $index => $row) {
            $key = $row['_position_'];
            
            foreach ($fields as $field) {
                $field_name = $field->name;
                $field_type = $this->dashman->guessField($field);

                switch ($field_type) {
                    case 'boolean':
                        $entries[$index][$field_name] = '
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input inline-checkbox"
                                data-id="'.$key.'" data-col="'.$field_name.'" id="'.$key.'-'.$field_name.'"
                                '.($row[$field_name] == 1 ? 'checked="checked"' : null).' >
                                <label class="custom-control-label" for="'.$key.'-'.$field_name.'">&nbsp</label>
                            </div>';
                        break;

                    case 'image':
                        break;
                    
                    default:
                        $entries[$index][$field_name] = '
                            <div class="inline-editor">
                                <div class="inline-content text-truncate" title="'.$row[$field_name].'" data-tw-bind="'.$key.'-'.$field_name.'" style="cursor:pointer">
                                    '.(($row[$field_name]) ? $row[$field_name] : '<div class="py-3"></div>').'
                                </div>
                                <input type="text" class="inline-field form-control form-control-sm"
                                data-id="'.$key.'" data-col="'.$field_name.'" data-tw-bind="'.$key.'-'.$field_name.'"
                                style="'.(($field_type == 'int' || $field_type == 'double') ? 'width:100px' : '').'"
                                value="'.$row[$field_name].'">
                            </div>';
                        break;
                }
            }            
        }

        foreach ($entries as $key => $row) {
            $html = '<div class="btn-group">';
            $html .= '<a href="'.site_url('/edit-entry/'.$table.'/'.$row['_position_']).'" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i></a> ';
            $html .='<button type="button" class="btn btn-sm btn-outline-danger row_remove"><i class="fa fa-times"></i></button>';
            $html .='</div>';
            $entries[$key]['action'] = $html;
        }
        
        echo $this->json->response('success', [
            'draw' => $this->input->get('draw') ? $this->input->get('order') : 1,
            "recordsTotal" => $this->table->table_count_rows(),
            "recordsFiltered" => $this->table->count,
            'data' => $entries
        ]);
    }

    /**
	 * Inserting data into a table
	 *
	 * @param	$table 	Table name
     * 
	 * @return	response
	 */
    public function edit($table)
    {
        $this->table->set($table);

        $response = [];
        $position = $this->input->post('position');
        $data = $this->input->post('data');
        
        $rules = [];
        $fields = $this->map->tableFields($table);
        
        foreach (array_keys($data) as $field) {
            if(isset($fields[$field])) {
                if($fields[$field]->extra == 'auto_increment' || $this->table->isUniqueKey($table, $fields[$field]->name)) {
                    array_push($rules, 'is_unique['.$table.'.'.$fields[$field]->name.']');
                }
                   
                if ( ! $fields[$field]->null || (isset($fields[$field]->foreign) && $fields[$field]->foreign == TRUE)) {
                    // Fields set as "NOT NULL" or set as foreign keys are required.
                    array_push($rules, 'required');
                }
                if ($fields[$field]->max_length > 1) {
                    // Rule for maximum length
                    array_push($rules, 'max_length['.$fields[$field]->max_length.']');
                }
                if ($fields[$field]->type == 'email') {
                    // Rule for email
                    array_push($rules, 'valid_email');
                }
                if ($fields[$field]->type == 'int') {
                    // Rule for integers
                    array_push($rules, 'integer');
                }
    
                // Set rules for the field.
                if ($rules) {
                    $this->form_validation->set_rules("data[$field]", $field, $rules);
                    $this->form_validation->set_error_delimiters("", "");
                }
                
                if (empty($rules) || $this->form_validation->run() == TRUE) {
                    
                    if ($this->table->saveEntry($table, $position, $data)) {
                        $response = [
                            'error' => false,
                            'message' => ''
                        ];
                    } else {
                        $response = [
                            'error' => true,
                            'message' => 'Row could not be updated'
                        ];
                    }
                } else {
                    
                    $response = [
                        'error' => true,
                        'message' => validation_errors()
                    ];
                }
            }
        }

        echo $this->json->response('success', $response);
    }

    /**
	 * Getting data related to table
	 *
	 * @param	$table 	   Table name
	 * @param	$position  row position of entry
	 * @param	$ref_table Referencing table name
     * 
	 * @return	response
	 */
    public function delete($table, $id = null) {
        $this->table->set($table);
        
        if ($id && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
            // Request to delete a single entity.
            if($this->table->deleteRows([$id])) {
                $this->json->response('deleted');
            }
        }
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Request to batch delete users.
            if($this->table->deleteRows($this->input->post('ids'))) {
                $this->json->response('deleted');
            }
        }
        exit();
    }

    /**
	 * Delete all records of a table
	 *
	 * @param $table Table name
     * 
	 * @return response
	 */
    public function empty($table) {
        // Connect to database.
        $this->table->set($table);
        
        $response['error'] =  ! $this->table->trucante();
        $response['message'] = $this->table->error() ? $this->table->error() : $this->table->rows.' rows were deleted';

        echo $this->json->response('success', $response);
    }

    /**
	 * Get table data 
	 *
	 * @param	$table 	Table name
     * 
	 * @return	response
	 */
    private function _getData($table)
    {
        $this->table->set($table);

        $records = $this->dash->table_data($table, [
            'start' => $this->input->get('start'),
            'limit' => $this->input->get('length'),
        ]);
        $total_records = $this->dash->table_count_rows($table);

        $json_data = array(
            "draw"            => $this->input->get('draw'),
            "recordsTotal"    => intval($total_records),
            "recordsFiltered" => intval(count($records)),
            "data"            => $records
        );

        echo json_encode($json_data);
        return;
    }
    
    /**
	 * Getting data related to table
	 *
	 * @param	$table 	   Table name
	 * @param	$position  row position of entry
	 * @param	$ref_table Referencing table name
     * 
	 * @return	response
	 */
    private function _getRefData($table, $position, $ref_table)
    {
        header('Content-type: text/html');
        
        $data = $this->dash->table_data($ref_table, [
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
        $field->nolabel = true;
        $field->attr = ['class' => "form-control multiselect", 'multiple' => "multiple"];
        $field->options = $options;
        $field->selected = $selected;

        echo $this->load->view('form_fields', ['fields' => [
            0 => $field
        ]], true);
    }
}

?>