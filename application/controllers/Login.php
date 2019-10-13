<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * View to log into the database server.
	*/
	public function index()
	{
		if ($this->input->post('login')) {
			$this->form_validation->set_error_delimiters('', '');
			// Set form validation rules.
			$this->form_validation->set_rules('hostname', 'Hostname', 'trim|required');
			$this->form_validation->set_rules('database', 'Database', 'trim|required');
			$this->form_validation->set_rules('username', 'Username', 'trim|required');

			if ($this->form_validation->run()) {
				$hostname = $this->input->post('hostname');
				$username = $this->input->post('username');
				$password = $this->input->post('password');
				$database = $this->input->post('database');

				// Initiate database loading with POST data.
				$config['hostname'] = $hostname;
				$config['username'] = $username;
				$config['password'] = $password;
				$config['database'] = $database;
				$config['dbdriver'] = 'mysqli';
				$config['db_debug'] = FALSE;
				$config['autoinit'] = FALSE;
				$this->load->database($config);
				// Capture any DB loading errors.
				$db_error = $this->db->error();

				if ($db_error['code'] !== 0) {
					// An error occured while loading the database.
					$this->session->set_flashdata('message', $db_error['message']);
				} else {
					// Delete cache from previous session.
					$this->db->cache_delete_all();
					// Set session variables.
					$this->session->set_userdata(array(
						'host' => $hostname,
						'user' => $username,
						'pass' => $password,
						'database' => $database,
						'logged_in' => TRUE
					));
					$this->session->set_flashdata('message', array(
						'type' => 'success',
						'content' => 'You logged in successfully'
					));
					if ($this->session->userdata('login_redirect') && $this->session->userdata('login_redirect') !== 'favicon.ico') {
						redirect($this->session->userdata('login_redirect'));
					}else {
						redirect();
					}
				}
			}
		}

		// Url to redirect users to after login
		$this->data['redirect'] = $this->session->userdata('login_redirect');
		// Error messages
		$this->data['message'] = validation_errors() ? 'Invalid details' : $this->session->flashdata('message');
		// Load the view
		$this->load->view('login_view', $this->data);
	}

	/**
	 * log out of the database server.
	 * 
	 * @return response
	*/
	public function logout()
	{
		// Remove session data
		$this->session->sess_destroy();
		// Remove cache data
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->cache->clean();

		redirect('login');
	}

	public function database($db)
	{
		// Delete cache from previous session.
		$this->db->cache_delete_all();
		$this->session->set_userdata('database', $db);
		$this->db->db_select($db);
		$this->dashman->set_message('Database switched to '.$db);
		redirect(site_url(), 'redirect');
	}
}

/* End of file Login.php */
/* Location: ./application/controllers/Login.php */
