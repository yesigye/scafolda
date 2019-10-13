<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Json
*
* Author: Ignatius Yesigye
*		  ignatiusyesigye@gmail.com
*/

class Json
{
    
	protected $codes = [];

	/**
	 * Define headers
	 */
	public function __construct()
	{
		// clear the old headers
        header_remove();
        // set the header to make sure cache is forced
        header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
        // treat this as json
        header('Content-Type: application/json');

        $this->load->config('httpcodes');
        $this->codes = $this->config->item('codes');
	}

	/**
	 * @param	$var
     * 
	 * @return	mixed
	 */
	public function __get($var)
	{
		return get_instance()->$var;
	}

	/**
	 * Return Json formatted rsponse
	 *
	 * @param string $type    human friendly header type
	 * @param string $message response message
	 **/
	function response($type = 'success', $message = null)
    {
        switch (strtolower($type)) {
            case 'deleted':
                $code = 204;
                break;
            case 'unauthorized':
                $code = 401;
                break;
            case 'forbidden':
                $code = 403;
                break;
            case 'not found':
                $code = 404;
                break;
            default:
                $code = 200;
                break;
        }

        header('HTTP/1.1 '.$this->codes[$code]);
        header("Status: $code");
        http_response_code($code);
        
        if($message) return json_encode($message);

        exit();
    }
}

/* End of file Json.php */
/* Location: ./application/libraries/Json.php */