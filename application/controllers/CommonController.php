<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class CommonController extends CI_Controller
{
	function __construct()
	{
		parent::__construct(); 
		$this->load->model('CommonModel');

		//--for get params from front end
     	$fileContent = file_get_contents("php://input");
		if(!empty($fileContent))
			$this->requestData = json_decode(file_get_contents("php://input"));
		else 
			$this->requestData = (object)$_REQUEST;
	}
}
?>