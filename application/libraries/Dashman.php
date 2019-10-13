<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:  Dashman
 *
 * Author: Ignatius Yesigye
 *		  ignatiusyesigye@gmail.com
 */

class Dashman
{
	private $error_message = '';
	private $status_message = '';
	
	/**
	 * Enables the use of CI super-global without having to define an extra variable.
	 *
	 * @access	public
	 * @param	$var
	 * 
	 * @return	mixed
	 */
	public function __get($var)
	{
		return get_instance()->$var;
	}

	public function __construct()
	{
		// Load the session library.
		$this->load->library('session');
		$this->connectDB();
	}
	
	/**
	 * Check if user is logged in
	 *
	 * @return boolean
	 **/
	public function isLoggedIn()
	{
		if (! $this->session->userdata('logged_in')) {
			if (current_url() !== site_url()) {
				// User is not requesting home page.
				// Set the url to redirect to after login.
				$this->session->set_userdata('login_redirect', current_url());
			}
			return false;
		}
		
		return true;
	}
	
	/**
	 * Return server info
	 *
	 * @return array
	 **/
	public function info()
	{
		$this->load->model('mapping_model');
		return $this->mapping_model->info();
	}
	
	/**
	 * Return recently updated tables
	 *
	 * @return object
	 **/
	public function recents(Int $num = 5)
	{
		$tables = $this->db->list_tables();

		$this->db->limit($num);
		$this->db->where('UPDATE_TIME !=', null);
		$this->db->select('TABLE_NAME AS table')->group_by('TABLE_NAME, UPDATE_TIME');
		$this->db->select('MAX(UPDATE_TIME) AS time');
		$this->db->order_by('UPDATE_TIME', 'DESC');
		$this->db->where('TABLE_SCHEMA', $this->database);
		
		return $this->db->get('information_schema.tables')->result_array();
	}
	
	/**
	 * Return main database entites
	 *
	 * @return object
	 **/
	public function entities()
	{
		$this->load->model('guesser_model');
		return $this->guesser_model->entities();
	}
	
	/**
	 * Return database tables
	 *
	 * @return object
	 **/
	public function tables()
	{
		$this->load->model('mapping_model');
		$tables = $this->db->list_tables();
		$data = [];
		foreach ($tables as $table) {
			array_push($data, ['name' => $table, 'rows' => $this->db->count_all($table)]);
		}
		return $data;
	}

	public function databases()
	{
		$this->load->dbutil();

		$result = [];
		$hidden = [$this->db->database, 'information_schema', 'performance_schema', 'mysql', 'sys'];
		$dbs = $this->dbutil->list_databases();
		foreach ($dbs as $db) {
			if(!in_array($db, $hidden)) {
				array_push($result, $db);
			}
		}
		return $result;
	}
	
	public function tableMeta(String $table)
	{
        return $this->table->meta();
	}

	public function guessField($fieldData)
	{
		$this->load->model('guesser_model');
		return $this->guesser_model->field($fieldData);
	}

	/**
	 * Return an icon for a table
	 *
	 * @param string $table table name
	 *
	 * @return string
	 **/
	public function icon(String $table)
	{
		$icon = '';
		
		// Load dasman configuration file.
        // Found in the application/config/dashman.php file
        $this->load->config('dashman');
		$iconOptions = $this->config->item('icons');
		$iconDefault = $this->config->item('default_icon');
		
		foreach ($iconOptions as $key => $option) {
			foreach ($option as $keyword) {
				if ( strpos(strtolower($table), strtolower($keyword)) !== false) {
					$icon = $key;
					break;	
				}
			}
		}
		
		return $icon ? $icon : $iconDefault;
	}

	/**
	 * Return array of database field types
	 *
	 * @return array
	 **/
	public function field_types()
	{
		$this->load->config('datamorph');
		return $this->config->item('field_types');
	}

	/**
	 * List of data types that can be generated.
	 *
	 * @return array
	 **/
	public function data_types()
	{
		$this->load->config('datamorph');
		return $this->config->item('data_types');
	}
	
	/**
	 * Return type of table field
	 *
	 * @param string $fieldData field constraint data
	 *
	 * @return string
	 **/
	public function fieldType($fieldData)
	{
		$this->load->model('Guesser_model');
		return $this->guesser_model->field($fieldData);
	}
	
	public function connectDB(String $database = '')
	{
		$this->load->config('dashman');
		$authentication = $this->config->item('authentication');
		
		if ($authentication) {
			// User is not logged in and is not requesting a login page.
			if(!$this->session->userdata('logged_in') && $this->uri->segment(1) !== 'login') {

				if(current_url() !== site_url()) {
					$this->session->set_userdata('login_redirect', current_url());
				}
				redirect('login');
			}
			
			// Load Database
			$config['hostname'] = $this->session->userdata('host');
			$config['username'] = $this->session->userdata('user');
			$config['password'] = $this->session->userdata('pass');
			$config['database'] = $this->session->userdata('database');
			$config['dbdriver'] = 'mysqli';
			$config['dbprefix'] = '';
			$config['pconnect'] = FALSE;
			$config['db_debug'] = TRUE;
			$config['cache_on'] = FALSE;
			$config['cachedir'] = '';
			$config['char_set'] = 'utf8';
			$config['dbcollat'] = 'utf8_general_ci';
			$config['autoinit'] = FALSE;

			if ($config['hostname'] && $config['username'] && $config['database']) {
				$this->load->database($config);
				$this->database = $this->session->userdata('database');
			}
		} else {
			// We are not set to use inbuilt authentication.
			
			//  Load the database config file.
			if(file_exists($file_path = APPPATH.'config/database.php')) include($file_path);
			
			// We assume use hasr defined DB values in "application/config/database.php".
			$config = $db[$active_group];
			$config['db_debug'] = false;
			$this->load->database($config);
			// Capture any DB loading errors.
			$db_error = $this->db->error();

			if ($config['hostname'] && $config['username'] && $config['database']) {
				if ($db_error['code'] !== 0) {
					// Database credentials are not working
					log_message('error', 'Invalid DB credentials. App authentication turned off');
					$this->load->view('error_view', array_merge($db_error, $config));
					return false;
				}else {
					// Save Database credentials
					$this->load->database();
					$this->hostname = $this->db->hostname;
					$this->database = $this->db->database;
				}
			} else {
				log_message('error', 'Undefined DB credentials. App authentication turned off');
				redirect('login');
			}
		}
	}

	/**
	 * Set error message
	 *
	 * @param string $message error message
	 * @param string $target_user user group
	 * @param string $message_type type of alert message
	 **/
	function set_message($message, $message_type = 'status')
	{
		switch ($message_type) {
			case 'error':
				$type = 'danger';
				$this->error_message = $message;
				break;
			
			case 'warn':
				$type = 'warning';
				$this->error_message = $message;
				break;
			
			case 'status':
				$type = 'success';
				$this->status_message = $message;
				break;
			
			default:
				$type = 'success';
				$this->status_message = $message;
				break;
		}

		// Save message in session
		$this->session->set_flashdata('alert', array(
			'type' => $type,
			'message' => $message
		));
	}

	/**
	 * Get message
	 *
	 * @param string $target_user user group
	 * @param string $message_type type of alert message
	 *
	 * @return string str error message
	 **/
	function get_message()
	{
		return $this->session->flashdata('alert');
	}
}

/* End of file Dashman.php */
/* Location: ./application/libraries/Dashman.php */
