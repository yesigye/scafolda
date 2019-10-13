<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migrate extends CI_Controller {

	public function index()
	{
		$this->load->library('migration');
		
		// Disable migration for production environments
		if (ENVIRONMENT === 'production') show_404();
		
		if ( ! $this->migration->current()) {
			show_error($this->migration->error_string());
		}

		return true;
	}
}

/* End of file Migrate.php */
/* Location: ./application/controllers/Migrate.php */
