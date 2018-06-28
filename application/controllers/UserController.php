<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class UserController extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		header("Access-Control-Allow-Headers: Content-Type");
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
		header("Access-Control-Allow-Origin: *");  
		$this->load->model('UserModel');
		$this->load->model('CommonModel');

		//--for get params from front end
     	$fileContent = file_get_contents("php://input");
		if(!empty($fileContent))
			$this->requestData = json_decode(file_get_contents("php://input"));
		else 
			$this->requestData = (object)$_REQUEST;

		/*if(!$this->session->has_userdata('loginData')){
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => 'Session expire, please try again.', 'data' => []));
			die();
		}*/
	}

	//--add users by Admin
	public function addUser()
	{
		//--logic for image upload if get else blank
		if (isset($_FILES['img']) && !empty($_FILES['img']['tmp_name']))
			$imgName = $this->CommonModel->imageUpload('img','./uploads/profile');
		else
			$imgName = '';

		//-- get params
		$usrData['usr_compid'] = isset($this->requestData->compId)?$this->requestData->compId:"";
		$usrData['usr_fname'] = isset($this->requestData->fnm)?$this->requestData->fnm:"";
		$usrData['usr_lname'] = isset($this->requestData->lnm)?$this->requestData->lnm:"";
		$usrData['usr_email'] = isset($this->requestData->email)?$this->requestData->email:"";
		$usrData['usr_password'] = isset($this->requestData->pass)?$this->requestData->pass:"";
		$usrData['usr_image'] = $imgName;
		$usrData['usr_mobile1'] = isset($this->requestData->mob1)?$this->requestData->mob1:"";
		$usrData['usr_mobile2'] = isset($this->requestData->mob2)?$this->requestData->mob2:"";
		$usrData['usr_address'] = isset($this->requestData->adr)?$this->requestData->adr:"";
		$usrData['usr_timezone'] = isset($this->requestData->tz)?$this->requestData->tz:"";
		$usrData['usr_type'] = isset($this->requestData->type)?$this->requestData->type:"";
		$usrData['usr_isactive'] = 1;
		$usrData['usr_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		$usrData['usr_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		
		//--get rights ids
		$rgtIds = isset($this->requestData->rgtIds)?$this->requestData->rgtIds:array();
		try
		{
			//-- call model function addUser
			$arrayResponse = $this->UserModel->addUser($usrData,$rgtIds);
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

	//-- Function to get user contact list
	public function getUserContacts()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$usrId = isset($this->requestData->usrId)?$this->requestData->usrId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		$search = isset($this->requestData->search)?$this->requestData->search:"";
		try
		{
			//-- call model function getUserContacts
			$arrayResponse = $this->UserModel->getUserContacts($compId,$usrId,$limit,$index,$search);
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
	
    //--for get user details
	public function getUserDetail()
	{
	    //-- get params
		$usrId = isset($this->requestData->usrId)?$this->requestData->usrId:"";
		try
		{
			//-- call model function getUserDetail
			$arrayResponse = $this->UserModel->getUserDetail($usrId);
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
    
    //--update user by Admin
	public function updateUser()
	{
		//-- get params
		$usrId = isset($this->requestData->usrId)?$this->requestData->usrId:"";
		$usrData['usr_fname'] = isset($this->requestData->fnm)?$this->requestData->fnm:"";
		$usrData['usr_lname'] = isset($this->requestData->lnm)?$this->requestData->lnm:"";
		$usrData['usr_mobile1'] = isset($this->requestData->mob1)?$this->requestData->mob1:"";
		$usrData['usr_mobile2'] = isset($this->requestData->mob2)?$this->requestData->mob2:"";
		$usrData['usr_address'] = isset($this->requestData->adr)?$this->requestData->adr:"";
		$usrData['usr_timezone'] = isset($this->requestData->tz)?$this->requestData->tz:"";
		$usrData['usr_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		
		//--logic for image upload if get else blank
		if (isset($_FILES['img']) && !empty($_FILES['img']['tmp_name']))
			$usrData['usr_image'] = $this->CommonModel->imageUpload('img','./uploads/profile');

		try
		{
			//-- call model function updateUser
			$arrayResponse = $this->UserModel->updateUser($usrId,$usrData);
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
	
	//--update user by Admin
	public function updateUserByAdmin()
	{
		//-- get params
		$usrId = isset($this->requestData->usrId)?$this->requestData->usrId:"";
		$usrData['usr_fname'] = isset($this->requestData->fnm)?$this->requestData->fnm:"";
		$usrData['usr_lname'] = isset($this->requestData->lnm)?$this->requestData->lnm:"";
		$usrData['usr_mobile1'] = isset($this->requestData->mob1)?$this->requestData->mob1:"";
		$usrData['usr_mobile2'] = isset($this->requestData->mob2)?$this->requestData->mob2:"";
		$usrData['usr_address'] = isset($this->requestData->adr)?$this->requestData->adr:"";
		$usrData['usr_timezone'] = isset($this->requestData->tz)?$this->requestData->tz:"";
		$usrData['usr_type'] = isset($this->requestData->type)?$this->requestData->type:"";
		$usrData['usr_isactive'] = isset($this->requestData->isactive)?$this->requestData->isactive:"";
		$usrData['usr_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		$rgtIds = isset($this->requestData->rgtIds)?$this->requestData->rgtIds:array();
		
		//--logic for image upload if get else blank
		if (isset($_FILES['img']) && !empty($_FILES['img']['tmp_name']))
			$usrData['usr_image'] = $this->CommonModel->imageUpload('img','./uploads/profile');

		try
		{
			//-- call model function updateUserByAdmin
			$arrayResponse = $this->UserModel->updateUserByAdmin($usrId,$usrData,$rgtIds);
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

	//--update user location
	public function updateUserLocation()
	{
		//-- get params
		$usrId = isset($this->requestData->usrId)?$this->requestData->usrId:"";
		$usrData['usr_lat'] = isset($this->requestData->lat)?$this->requestData->lat:"";
		$usrData['usr_long'] = isset($this->requestData->lng)?$this->requestData->lng:"";
		$usrData['usr_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		try
		{
			//-- call model function updateUserLocation
			$arrayResponse = $this->UserModel->updateUserLocation($usrId,$usrData);
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

	//--get field worker(user) list
	public function getFieldWorkerList()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		$search = isset($this->requestData->search)?$this->requestData->search:""; 
		try
		{
			//-- call model function getFieldWorkerList
			$arrayResponse = $this->UserModel->getFieldWorkerList($compId,$limit,$index,$search);
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

	//--Function for delete user
	public function deleteUser()
	{
		//-- get params
		$usrId = isset($this->requestData->usrId)?$this->requestData->usrId:"";

		try
		{
			//-- call model function deleteUser
			$arrayResponse = $this->UserModel->deleteUser($usrId);
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

	//--get rights list
	public function getRightsList()
	{
		try
		{
			//-- call model function getRightsList
			$arrayResponse = $this->UserModel->getRightsList();
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

	//-- function for changing user password
	public function changePassword()
	{
		//-- get params
		$usrId = isset($this->requestData->usrId)?$this->requestData->usrId:"";
		$oldPassword = isset($this->requestData->op)?$this->requestData->op:"";
    	$newPassword = isset($this->requestData->np)?$this->requestData->np:"";

		try
		{
			//-- call model function changePassword
			$arrayResponse = $this->UserModel->changePassword($usrId,$oldPassword,$newPassword);
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
	
	//--for users and admin logout
	public function logout()
	{
		//-- get params
		$udId = isset($this->requestData->udId)?$this->requestData->udId:"";

		try
		{
			//-- call model function logout
			$arrayResponse = $this->UserModel->logout($udId);
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
}
?>