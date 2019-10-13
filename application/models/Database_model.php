<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Database manipluation.
 *
 * @category Test
 * @package  Dashman
 * @author   Ignatius Yesigye <ignatiusyesigye@gmail.com>
 * @license  MIT <http://opensource.org/licenses/MIT>
 * @link     null
 */
class Database_model extends CI_Model {

	/*
	 * name of database
	*/
	protected $database;
	
	/*
	 * error messages.
	*/
	public $error;

	/**
     * Constructor
     **/
    public function __construct()
    {
		parent::__construct();

		if ($this->session->userdata('database')) $this->set($this->session->userdata('database'));
	}

	/**
	 * Attempt database connection.
	 *
	 * @return void
	 */
	public function connect()
	{
		// Connect to database.
		$config['hostname'] = $this->session->userdata('host');
		$config['username'] = $this->session->userdata('user');
		$config['password'] = $this->session->userdata('pass');
		$config['database'] = $this->database;
		$config['dbdriver'] = 'mysqli';
		$config['dbprefix'] = '';
		$config['pconnect'] = FALSE;
		$config['db_debug'] = FALSE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = '';
		$config['char_set'] = 'utf8';
		$config['dbcollat'] = 'utf8_general_ci';
		//$config['autoinit'] = TRUE; // default
		$config['autoinit'] = FALSE;
		$this->load->database($config);
	}

    /**
	 * Sets database name.
	 *
     * @param string $database database name
	 *
	 * @return object
	 */
	public function set(String $database)
	{
		$this->database = $database;
		return $this;
	}

    /**
	 * Return any errors that occur.
	 *
	 * @return string
	 */
	public function error()
	{
		$error = $this->db->error();
		return $error['message'];
	}

	/**
	 * Returns general information about the server.
	 */
	public function info()
	{
		$info = new StdClass();
		$info->server 	= $this->session->userdata('host');
		$info->platform = $this->db->platform();
		$info->version 	= $this->db->version();
		return $info;
	}

    /**
	 * Returns list of all databases.
	 *
	 * @return array
	 */
    public function listdbs()
	{
		$this->load->dbutil();
		
		$list = $this->dbutil->list_databases();
		
		foreach ($list as $key => $db) {
			if (in_array($db, $this->exclude)) {
				unset($list[$key]);
			}
		}
		return array_values($list);
	}

    /*
	 * Returns tables in a database.
	 *
	 * @return array
	 */
    public function tables()
	{
		return $this->db->list_tables();
	}

	/**
	 * Create a new database.
	 *
	 * @return boolean
	 */
    public function create()
	{
		$this->connect();
		$this->load->dbforge();
		return $this->dbforge->create_database($this->database);
	}

	/**
	 * Remove all tables in the database.
	 *
	 * @return boolean
	 */
	public function trucante()
	{
		$this->connect();
		$this->load->dbforge();

		foreach($this->db->list_tables() as $table) {
			$this->dbforge->drop_table($table);
		}
		
		return empty($this->db->list_tables()) ? true : false;
	}

	/**
	 * Delete entire database. Please confirm with the user before
	 * taking this action as it can not be reversed.
	 *
	 * @return boolean
	 */
	public function drop()
	{
		$this->connect();
		$this->load->dbforge();
		return $this->dbforge->drop_database($this->database);
	}

	/**
	 * List of table columns that can be used as foreign keys.
	 *
	 * @return array
	 */
	public function referenceableColumns()
	{
		$this->connect();
		$query = $this->db->query("SELECT TABLE_NAME AS `table`, COLUMN_NAME AS `column`
			FROM `information_schema`.`TABLE_CONSTRAINTS` t
			JOIN `information_schema`.`KEY_COLUMN_USAGE` k
			USING (`CONSTRAINT_NAME`, `TABLE_SCHEMA`, `TABLE_NAME`)
			WHERE (t.`CONSTRAINT_TYPE` = 'PRIMARY KEY' OR t.`CONSTRAINT_TYPE` = 'UNIQUE')
			AND t.`TABLE_SCHEMA` = '$this->database'")->result_array();

		return $query;
	}

	/**
	 * Uploads a sql file and imports it into the database.
	 *
	 * @return boolean
	 */
	public function import()
	{
		$filepath = 'uploads'.DIRECTORY_SEPARATOR;
		
		$this->load->library('upload', [
			'upload_path' => $filepath,
			'allowed_types' => 'sql|SQL',
			'max_size' => 100

		]);

		if ( ! $this->upload->do_upload('userfile')) {
			// File no uploaded. record the error message.
			$this->error = $this->upload->display_errors();
			return false;
		}

		$fileData = $this->upload->data();
		
		// Temporary variable, used to store current query
		$templine = '';
		// Read in entire file
		$lines = file($filepath.$fileData['file_name']);
		// Loop through each line
		foreach ($lines as $line) {
			// Skip it if it's a comment
			if (substr($line, 0, 2) == '--' || $line == '') continue;

			// Add this line to the current segment
			$templine .= $line;
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($line), -1, 1) == ';') {
				// Perform the query
				// mysql_query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
				$this->db->query($templine);
				// Reset temp variable to empty
				$templine = '';
			}
		}
		// Remove temporary file.
		unlink($filepath.$fileData['file_name']);
		return true;
	}
}

/* End of file Database_model.php */
/* Location: ./application/models/Database_model.php */