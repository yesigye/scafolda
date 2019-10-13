<?php

/**
 * Run some tests and display their results.
 *
 * @category Test
 * @package  Dashman
 * @author   Ignatius Yesigye <ignatiusyesigye@gmail.com>
 * @license  MIT <mit.com>
 * @link     null
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Run some tests and display their results.
 *
 * @category Test
 * @package  Dashman
 * @author   Ignatius Yesigye <ignatiusyesigye@gmail.com>
 * @license  MIT <http://opensource.org/licenses/MIT>
 * @link     null
 */
class Dashboard extends CI_Controller
{
    /**
     * Constructor
     **/
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display dashboard page of the database
     *
     * @return response
     */
    public function index()
    {
        // User posted a auto generate request.
        if ($this->input->post('auto_generate')) {                                                  
            // Validate submitted form.
            $this->form_validation->set_rules('rows', ' ', 'trim|required');
            $this->form_validation->set_error_delimiters("", "");

            if ($this->form_validation->run()) {
                $tbl = $this->input->post('table');
                $this->table->set($tbl);
                // Attempt to generate data.
                if ($this->table->auto_generate($this->input->post('rows'))) {
                    // success
                    $this->dashman->set_message($this->table->rows.' rows were generated for '.$tbl);
                } else {
                    // fail
                    $this->dashman->set_message($this->table->error(), 'error');
                }
                redirect(current_url());
            } else {
                // fail
                $this->dashman->set_message(validation_errors(), 'error');
            }
        }
		
		// User posted an import request.
		if ($this->input->post('import')) {
			// Atempt to add a column.
			if ($this->database->import()) {
				// success
				$this->dashman->set_message('Data was imported successfully');
			} else {
				// fail
				$this->dashman->set_message(
					$this->database->error ? $this->database->error : 'Something went wrong on our part', 'error');
			}
			redirect(current_url());
        }

        $this->load->helper('date');
        
        $tables = [];
        foreach($this->db->list_tables() as $tbl) {
            array_push($tables, $this->table->meta($tbl));
        }

        $this->database = $this->db->database;
        $this->data['table_list'] = $tables;
        $this->data['last_update'] = $this->table->lastUpdated();
        $this->load->view('dashboard_view', $this->data);
    }

    /**
     * Display settings page of the app
     *
     * @return response
     */
    public function settings()
    {
        $this->load->helper('file');

        $config_file = APPPATH.'config'.DIRECTORY_SEPARATOR.'dashman.php';
        $config_perm = octal_permissions(fileperms($config_file));
        
        
        // 644 applies to files that are writable
        if ($config_perm === '666' || $config_perm === '777' || $config_perm === '744' || $config_perm === '644') {
            $config_data = file_get_contents($config_file);
        } else {
            // User does not have file read permission.
            $this->dashman->set_message('An error occured while accessing settings', 'error');
        }

        include $config_file;
        
        if ($this->input->post('save')) {
            $formatted = [];
            $formatted['authentication'] = (bool) $this->input->post('authentication');
            $formatted['page_limit'] = intval($this->input->post('page_limit'));
            $formatted['upload_file_path'] = $this->input->post('upload_file_path');
            $formatted['icons'] = [];
            $formatted['default_icon'] = $this->input->post('default_icon');
            $formatted['column_name_types'] = [];
            
            foreach ($this->input->post('column_name_types') as $key => $values) {
                $formatted['column_name_types'][$key] = explode(',', str_replace(' ', '', $values));
            }
            
            $icons = $this->input->post('icons');
            foreach ($this->input->post('icon_keys') as $key => $value) {
                $formatted['icons'][$value] = explode(',', str_replace(' ', '', $icons[$key]));
            }

            if ($config === $formatted) {
                // Submitted, without any changes
                $this->dashman->set_message('You did not make any changes.', 'warn');
                redirect(current_url(), 'refresh');
            }

            // Write data to config file with CI security standard at top.
            $field_data = "<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); \n";
            $field_data .= ('$config = '.var_export($formatted, true)).';';

            if (write_file($config_file, $field_data)) {
                $this->dashman->set_message('Your settings were saved successfully.');
            } else {
                // Unable to write the file
                $this->dashman->set_message('Your settings could not be saved.', 'error');
            }

            redirect(current_url(), 'refresh');
        }

        $this->data['settings'] = $config;
        $this->load->view('settings_view', $this->data);
    }

    /**
     * Download a database
     *
     * @return resource
     */
    public function downloadDB($database)
    {
        $fileName = $this->db->database.".sql";
        $this->load->dbutil();
        
        $backup = $this->dbutil->backup([
            'format' => 'txt', // gzip, zip, txt
            'filename' => $fileName  // File name - NEEDED ONLY WITH ZIP FILES
            
        ]);
        
        $this->load->helper('download');
        force_download($fileName, $backup);
        redirect();
    }
}

/* End of file Dashboard.php */
/* Location: ./application/controllers/Dashboard.php */
