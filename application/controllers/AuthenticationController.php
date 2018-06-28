<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class AuthenticationController extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		header("Access-Control-Allow-Headers: Content-Type");
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
		header("Access-Control-Allow-Origin: *");  
		$this->load->model('AuthenticationModel');
		$this->load->model('CommonModel');

		//--for get params from front end
     	$fileContent = file_get_contents("php://input");
		if(!empty($fileContent))
			$this->requestData = json_decode(file_get_contents("php://input"));
		else 
			$this->requestData = (object)$_REQUEST;
	}

	//-- function for registration of company
    public function addCompany()
    {
		//--logic for image upload if get else blank
		if (isset($_FILES['logo']) && !empty($_FILES['logo']['tmp_name']))
			$logoName = $this->CommonModel->imageUpload('logo','./uploads/companyLogo');
		else
			$logoName = 'default_logo.png';

		//-- get params
		$compData['comp_name'] = $this->input->post('name');
        $compData['comp_email'] = $this->input->post('email');
        $compData['comp_verification_code'] = rand(100000,999999);
        $compData['comp_logo'] = $logoName;
        $compData['comp_status'] = 0;
        $compData['comp_user_limit'] = 10;
        $compData['comp_job_limit'] = 100;
        $compData['comp_isactive'] = 1;
        $compData['comp_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
        $compData['comp_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		try
		{
			//-- call model function resendVerificationCode
			$arrayResponse = $this->AuthenticationModel->addCompany($compData);
			if(empty($arrayResponse))
				throw new Exception('Response not get, please try again.');
		} 
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage(), 'data' => []));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($arrayResponse);
    }

    //-- function for resend the company verification code
	public function resendVerificationCode()
	{
        $email = isset($_SESSION['email'])?$_SESSION['email']:"";
        try
		{
			//-- call model function resendVerificationCode
			$arrayResponse = $this->AuthenticationModel->resendVerificationCode($email);
			if(empty($arrayResponse))
				throw new Exception('Response not get, please try again.');
		} 
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage(), 'data' => []));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($arrayResponse);
	}

	//-- function for verify company code
	public function verifyCompanyCode()
	{
		//-- get params
		$email = isset($_SESSION['email'])?$_SESSION['email']:"";
    	$code = $this->input->post('code');
		try
		{
			//-- call model function verifyCompanyCode
			$arrayResponse = $this->AuthenticationModel->verifyCompanyCode($email,$code);
			if(empty($arrayResponse))
				throw new Exception('Response not get, please try again.');
		} 
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage(), 'data' => []));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($arrayResponse);
	}
	
	//--for all users login
	public function login()
	{
		//-- get params
	    $email = isset($this->requestData->email)?$this->requestData->email:"";
	    $pass = isset($this->requestData->pass)?$this->requestData->pass:"";
	    $devType = isset($this->requestData->devType)?$this->requestData->devType:"";
	    $devId = isset($this->requestData->devId)?$this->requestData->devId:"";
		
		try
		{
			//-- call model function login	
			$arrayResponse = $this->AuthenticationModel->login($email,$pass,$devType,$devId);
			if(empty($arrayResponse))
				throw new Exception('Response not get, please try again.');
		} 
		catch (Exception $e){
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage(), 'data' => []));
			exit();
		}

		//-- convert arrayResponse to json
    	echo $this->CommonModel->getJsonData($arrayResponse);	
    }

	//-- Function for forgot password
	function forgotPassword()
	{
		//-- get params
		$email = isset($this->requestData->email)?$this->requestData->email:"";
		$cCode = isset($this->requestData->cc)?$this->requestData->cc:"";

		try
		{
			//-- call model function forgot password
			$arrayResponse = $this->AuthenticationModel->forgotPassword($email,$cCode);
			if(empty($arrayResponse))
				throw new Exception('Response not get, please try again.');
		}
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage(), 'data' => []));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($arrayResponse);
	}

	//--function for confirmation of forgot password key and returning userId
	function forgotPasswordKey()
	{
		$email = isset($this->requestData->email)?$this->requestData->email:"";
		$key = isset($this->requestData->key)?$this->requestData->key:"";
		$cCode = isset($this->requestData->cc)?$this->requestData->cc:"";

		try
		{
			//-- call model function forgotPasswordKey
			$arrayResponse = $this->AuthenticationModel->forgotPasswordKey($email,$key,$cCode);
			if(empty($arrayResponse))
				throw new Exception('Response not get, please try again.');
		}
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' =>$e->getMessage(), 'data' => []));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($arrayResponse);
	}

	//-- function for reset password
	function forgotPasswordReset()
	{
		$usrId = isset($this->requestData->usrId)?$this->requestData->usrId:"";
		$password = isset($this->requestData->pass)?$this->requestData->pass:"";

		try
		{
			//-- call model function forgotPasswordReset
			$arrayResponse = $this->AuthenticationModel->forgotPasswordReset($usrId,$password);
			if(empty($arrayResponse))
				throw new Exception('Response not get, please try again.');
		}
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage(), 'data' => []));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($arrayResponse);
	}
}
?>