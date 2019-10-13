<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

ini_set('upload_limit', '2M');
ini_set('memory_limit', '1024M');

class Image
{
	/**
	 * Path to upload directory
	 *
	 * @var	string
	 */
	public $upload_path = '';

	/**
	 * Name to uploaded file
	 *
	 * @var	string
	 */
	public $filename = '';

	/**
	 * Path to uploaded file
	 *
	 * @var	string
	 */
	public $filepath = '';

	/**
	 * Width of file after resize
	 *
	 * @var	string
	 */
	public $resize_width = '';

	/**
	 * Height of file after resize
	 *
	 * @var	string
	 */
	public $resize_height = '';

	/**
	 * Errors that occured
	 *
	 * @var	string
	 */
	public $error_message = '';

	/**
	 * __get
	 *
	 * Enables the use of CI super-global without having to define an extra variable.
	 *
	 * I can't remember where I first saw this, so thank you if you are the original author. -Militis
	 *
	 * @access	public
	 * @param	$var
	 * @return	mixed
	 */
	public function __get($var)
	{
		return get_instance()->$var;
	}

	/**
	 * __construct
	 */
	public function __construct()
	{
		$this->load->config('dashman');
		$this->upload_path = $this->config->item('upload_file_path');
	}

	/**
	 * Set any error that occurs
	 *
	 * @var	string $message error meassage to be set
	 **/
	public function set_error_message($message = '')
	{
		$this->error_message = $message;
	}

	/**
	 * Return error message
	 *
	 * @return string
	 **/
	public function error_message()
	{
		return $this->error_message;
	}

	/**
	 * upload image
	 *
	 * @access	public
	 * @return boolean
	 **/
	public function upload($options = array())
	{
		if (!isset($options['field'])) $options['field'] = 'userfile';

		// Load the CI upload library
		$config['upload_path'] 		= $this->upload_path;
		$config['allowed_types']	= 'gif|jpg|png|jpeg';
		$config['max_width'] 		= 0;
		$config['max_height'] 		= 0;
		$config['max_size'] 		= 0;
		$config['remove_spaces'] 	= FALSE;
		$config['encrypt_name'] 	= TRUE;
		$config['overwrite'] 		= FALSE;
		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload($options['field'])) {
			// Set an error message.
			$this->set_error_message = $this->upload->display_errors();
			return false;
		} else {
				$this->filename = $this->upload->data()['file_name'];
				$this->filepath = base_url($this->upload_path.$this->filename);
			return true;
		}
	}

	/**
	 * upload multiple images
	 *
	 * @access	public
	 * @return boolean
	 **/
	public function upload_multi($options = array())
	{
		// load and initialize the CI upload library
		$this->load->library('upload');
		$this->upload->initialize(array(
			"upload_path" 	=> $this->upload_path,
			"encrypt_name" 	=> TRUE,
			"allowed_types" =>"gif|jpg|png|jpeg|JPEG|PNG",
			"max_size" 		=> 2000,
			"xss_clean" 	=> FALSE, // Turn false for PDF
		));

		$this->upload->do_multi_upload("files");

		$images = $this->upload->get_multi_upload_data();
		
		if ( !empty($images)) {

			foreach ($images as $key => $image) {
				if ($this->input->post('crop_width') AND $this->input->post('crop_height')) {
					// Crop Banner image.
					$this->load->library('image_lib');
					$config['source_image']	  = $this->upload_path.$image['file_name'];
					$config['maintain_ratio'] = FALSE;
					$config['width'] 	= $this->input->post('crop_width')[$key];
					$config['height'] 	= $this->input->post('crop_height')[$key];
					$config['x_axis'] 	= $this->input->post('crop_x')[$key];
					$config['y_axis'] 	= $this->input->post('crop_y')[$key];

					$this->image_lib->initialize($config);
				}
			}
		}
		return $images;
	}


	/**
	 * crop file
	 *
	 * @access	public
	 * @return boolean
	 **/
	public function crop($options = array())
	{
		if (!isset($options['filepath'])) {
			$options['filepath'] = $this->upload_path.$this->filename;
		}
		
		$this->load->library('image_lib');
		$this->image_lib->clear();
		$this->image_lib->initialize([
			'source_image' => $options['filepath'],
			'maintain_ratio' => $options['ratio'],
			'width' => round($options['width']),
			'height' => round($options['height']),
			'x_axis' => round($options['x_axis']),
			'y_axis' => round($options['y_axis'])
		]);

		if ( ! $this->image_lib->crop()) {
			// Set an error message.
			$this->set_error_message($this->image_lib->display_errors());
			return false;
		}

		return true;
	}

	/**
	 * resize file
	 *
	 * @access	public
	 * @return object
	 **/
	public function resize($options = array())
	{
		if (!isset($options['filepath'])) $options['filepath'] = $this->upload_path.$this->filename;
		
		$this->load->library('image_lib');

		$config['image_library'] 	= 'gd2';
		$config['source_image']		= $options['filepath'];
		$config['filename']		= $options['filepath'];
		$config['create_thumb'] 	= FALSE;
		$config['maintain_ratio'] 	= TRUE;
		$config['width']	= $options['width'] ? $options['width'] : $this->resize_width;
		$config['height']	= $options['height'] ? $options['height'] : $this->resize_height;

		$this->image_lib->initialize($config);

		if ( ! $this->image_lib->resize($config)) {
			// Set an error message.
			$this->set_error_message($this->image_lib->display_errors());
			return false;
		} else {
			return true;
		}
	}

	/**
	 * delete file
	 *
	 * @return boolean
	 **/
	public function delete($filename)
	{
		$filename = str_replace(base_url($this->upload_path), '', $filename);

		if (is_file($this->upload_path.$filename)) {
			unlink($this->upload_path.$filename);
			return true;
		} else {
			return false;
		}
	}
}
