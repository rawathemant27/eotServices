<?php
class UserModel extends CI_Model
{	
	function __construct()
	{
		parent::__construct();
	}

	public function addUser($usrData,$rgtIds)
	{
		$this->db->trans_begin();//--db transaction start
		$responseArray = array();

		//--check required params
		if (!$usrData['usr_compid']) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else if (!$usrData['usr_fname'])
			$responseArray = array('success'=> false, 'message' => 'User first name required.', 'data'=>[]);
		else if (!$usrData['usr_email'])
			$responseArray = array('success'=> false, 'message' => 'User email required.', 'data'=>[]);
		else if (!$usrData['usr_password'])
			$responseArray = array('success'=> false, 'message' => 'Password required.', 'data'=>[]);
		else
		{
			//--get company user limit
			$fields = 'comp_user_limit';
			$tblName = 'eot_company';
			$condition = array('comp_id' => $usrData['usr_compid']);
			$compResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$compResult = $compResult->row();

			//-- get user total counts
            $this->db->from('eot_user');
            $this->db->where('usr_compid',$usrData['usr_compid']);
            $userCounts = $this->db->count_all_results();
            
            //-- check for company user creation limit
            if ($userCounts >= $compResult->comp_user_limit)
            {
                $responseArray = array("success" => false, "message" => "Your Company user limit exceeded, please contact to EyeOnTask services.");
            }
            else
            {
            	//--check email exist or not in company
				$fields = 'usr_id';
				$tblName = 'eot_user';
				$condition = array('usr_compid' => $usrData['usr_compid'] , 'usr_email' => $usrData['usr_email']);
				$usrResult = $this->CommonModel->getData($fields,$tblName,$condition);
				if ($usrResult->num_rows()) 
				{
					$responseArray = array("success"=>false,'message'=>'This email already exist, please try with other email.', 'data'=>[]);
				}
				else
				{ 
					$password = $usrData['usr_password'];
					$usrData['usr_password'] = MD5($password);
					$usrId = $this->CommonModel->insertData($tblName,$usrData);
					if($usrId)
					{
						//-- code for insert user rights
						if(array_count_values($rgtIds)) 
						{
							$rgtIds = array_unique($rgtIds);
							foreach ($rgtIds as $key) 
							{
								$tblName = 'eot_rights_user_mm';
								$rummData['rumm_usrid'] = $usrId;
								$rummData['rumm_rgtid'] = $key;
								$this->CommonModel->insertData($tblName,$rummData);
							}
						}

						//-- send mail to user
			            $url = "www.google.com";
			            $subject = 'Eye On Task - Registration confirmation mail.';
			            $this->load->helper('url');		            
			            $message =  'You are successfully registered in Eye On Task!<br><br>
			                        Your account has been created, Please login via this link: '.anchor($url, 'Click Here') .
			                        '<br><br>Here are your login details.<br>
			                        -------------------------------------------------<br>
			                        Username: '.$usrData['usr_email'].'<br>
			                        Password: '.$password.'<br>
			                        -------------------------------------------------<br><br>
			                        Support team,<br />Eye On Task';
			            
			            //--get user details
			            $usrResult = $this->CommonModel->getUserBasicDetails($usrId);

			            //--call function to send mail 
			            $emailResult = 0;//$this->CommonModel->sendEmail($usrData['usr_email'],$subject,$message);
			            if ($emailResult)							
							$responseArray = array('success'=> true,'message'=>"User registered and mail send successfully.", 'data' => $usrResult);
						else
							$responseArray = array('success'=> true,'message'=>"User registered, but mail not send.", 'data'=> $usrResult);					
					}	
					else
						$responseArray = array("success"=>false,'message'=>'User not registered, please try again.', 'data'=>[]);
				}
            }	
		}	

		//--use db tansaction
		if($this->db->trans_status() === FALSE){
		    $this->db->trans_rollback();
		    $responseArray = array("success" => false, "message" => 'Something went wrong, please try again.', "data" => []);
		}
		else{
		    $this->db->trans_commit();
		}
		return $responseArray;
	}

	public function getUserContacts($compId,$usrId,$limit,$index,$search)
	{
		$responseArray = array();

		//--check required params
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		if (!$usrId) 
			$responseArray = array('success'=> false, 'message' => 'User id required.', 'data'=>[]);
		else
		{
			$this->db->select("SQL_CALC_FOUND_ROWS usr_id as usrId,usr_fname as fnm, usr_lname as lnm,usr_email as email,usr_mobile1 as mob1, CASE WHEN usr_image!='' THEN CONCAT('uploads/profile/',usr_image) ELSE usr_image END as img,usr_isactive as isactive,usr_type as type",FALSE);
			$this->db->from('eot_user');
			$this->db->where('usr_compid',$compId);
			$this->db->where('usr_id!=',$usrId);
			if ($search){
				$this->db->like('usr_fname',$search);
			}
			$this->db->order_by('usr_fname','asc');
			if ($limit || $index)
				$this->db->limit($limit,$index);
			$userResult = $this->db->get();

			echo $this->db->last_query(); exit;

			//for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
			$total_count = $total_record->row();
			$total_count = $total_count->total_count;

			//--final result
			$responseArray = array("success"=>true,'data'=>$userResult->result_array(),'count'=>$total_count);
		}
		return $responseArray;
	}

	public function getUserDetail($usrId)
    {
        $responseArray = array();

        //--check required params
		if (!$usrId) 
			$responseArray = array('success'=> false, 'message' => 'User id required.', 'data'=>[]);
		else
		{
	        $usrResult = $this->CommonModel->getUserBasicDetails($usrId);
	        $responseArray = array("success"=>true,"data"=>$usrResult);
		}
        return $responseArray;
    }

	public function updateUser($usrId,$usrData)
	{
		$responseArray = array();	

		//--check required params
		if (!$usrId) 
			$responseArray = array('success'=> false, 'message' => 'User id required.', 'data'=>[]);
		else if (!$usrData['usr_fname'])
			$responseArray = array('success'=> false, 'message' => 'User first name required.', 'data'=>[]);
		else
		{
			$img = '';
			if (isset($usrData['usr_image'])) 
			{
				$img = 'uploads/profile/'.$usrData['usr_image'];
				$fields = 'usr_image';
				$tblName = 'eot_user';
				$condition = array('usr_id' => $usrId);
				$usrResult = $this->CommonModel->getData($fields,$tblName,$condition);
				$usrResult = $usrResult->row();
				if ($usrResult->usr_image != '') 
					unlink('./uploads/profile/'.$usrResult->usr_image);//delete image
			}
			
				
			$tblName = 'eot_user';
			$condition = array('usr_id' => $usrId);
			$usrResult = $this->CommonModel->updateData($tblName,$usrData,$condition);
			if($usrResult)
				$responseArray = array('success'=> true, 'message'=>'User updated successfully.', 'img' => $img);
			else
				$responseArray = array("success"=>false,'message'=>'User not update, please try again.', 'data'=>[]);
		}
		return $responseArray;
	}

	public function updateUserByAdmin($usrId,$usrData,$rgtIds)
	{
		$responseArray = array();	

		//--check required params
		if (!$usrId) 
			$responseArray = array('success'=> false, 'message' => 'User id required.', 'data'=>[]);
		else if (!$usrData['usr_fname'])
			$responseArray = array('success'=> false, 'message' => 'User first name required.', 'data'=>[]);
		else
		{
			//--get user profile image
			if (isset($usrData['usr_image'])) 
			{
				$fields = 'usr_image';
				$tblName = 'eot_user';
				$condition = array('usr_id' => $usrId);
				$usrResult = $this->CommonModel->getData($fields,$tblName,$condition);
				$usrResult = $usrResult->row();
				if($usrResult->usr_image != '') 
					unlink('./uploads/profile/'.$usrResult->usr_image);
			}

			$tblName = 'eot_user';
			$condition = array('usr_id'=> $usrId);
			$usrResult = $this->CommonModel->updateData($tblName,$usrData,$condition);
			if($usrResult)
			{
				//--delete data from eot_rights_user_mm table 
				$tblName = 'eot_rights_user_mm';
				$condition = array('rumm_usrid' => $usrId);
				$this->CommonModel->deleteData($tblName,$condition);

				//--code for insert user rights
				if(array_count_values($rgtIds)) 
				{
					$rgtIds = array_unique($rgtIds);
					foreach ($rgtIds as $key) 
					{
						$tblName = 'eot_rights_user_mm';
						$rummData['rumm_usrid'] = $usrId;
						$rummData['rumm_rgtid'] = $key;
						$this->CommonModel->insertData($tblName,$rummData);
					}
				}
				$usrResult = $this->CommonModel->getUserBasicDetails($usrId);
				$responseArray = array('success'=> true, 'message' => 'User updated successfully.', 'data'=>$usrResult);
			}
			else
			{
				$responseArray = array("success"=>false,'message' => 'User not updated, please try again.', 'data'=>[]);
			}
		}
		return $responseArray;
	}

	public function updateUserLocation($usrId,$usrData)
	{
		$responseArray = array();

		//--check required params
		if (!$usrId) 
			$responseArray = array('success'=> false, 'message' => 'User id required.', 'data'=>[]);
		else
		{
			$tblName = 'eot_user';
			$condition = array('usr_id' => $usrId);
			$result = $this->CommonModel->updateData($tblName,$usrData,$condition);
			if($result)
				$responseArray = array('success'=> true, 'message'=>'Location updated successfully.', 'data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Location not update, please try again.', 'data'=>[]);
		}
		return $responseArray;	
	}

	public function getFieldWorkerList($compId,$limit,$index,$search)
	{
		$responseArray = array();

		$this->db->select("SQL_CALC_FOUND_ROWS usr_id as usrId,usr_fname as fnm,usr_lname as lnm,usr_email as email,usr_mobile1 as mob1,CASE WHEN usr_image!='' THEN CONCAT('uploads/profile/',usr_image) ELSE usr_image END as img,usr_lat as lat,usr_long as lng",FALSE);
		$this->db->from('eot_user');
		$this->db->where('usr_compid',$compId);
		$this->db->where('usr_type',1);
		if ($search)
		{
			$this->db->like('usr_fname',$search);
			$this->db->or_like('usr_email',$search);
			$this->db->or_like('usr_mobile1',$search);
		}
		$this->db->order_by('usr_fname','asc');
		if ($limit || $index)
			$this->db->limit($limit,$index);
		$userResult = $this->db->get();

		//for pagignation
		$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
		$total_count = $total_record->row();
		$total_count = $total_count->total_count;

		//--final result
		$responseArray = array("success"=>true,'data'=>$userResult->result_array(),'count'=>$total_count);
		
		return $responseArray;
	}

	public function deleteUser($usrId)
	{
		$responseArray = array();

		//--check required params
		if (!$usrId) 
			$responseArray = array('success'=> false, 'message' => 'User id required.', 'data'=>[]);
		else
		{
			//--check user record in job_member table
			$fields = 'jm_usrid';
			$tblName = 'eot_job_member';	
			$condition = array('jm_usrid' => $usrId);
			$jmResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$jmResult = $jmResult->num_rows();

			//--check user record in status_log table
			$fields = 'slog_usrid';
			$tblName = 'eot_status_log';	
			$condition = array('slog_usrid' => $usrId);
			$slogResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$slogResult = $slogResult->num_rows();

			//--check user record in time_log table
			$fields = 'log_usrid';
			$tblName = 'eot_time_log';	
			$condition = array('log_usrid' => $usrId);
			$tlogResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$tlogResult = $tlogResult->num_rows();

			//--check user record in travel_log table
			$fields = 'tlog_usrid';
			$tblName = 'eot_travel_log';	
			$condition = array('tlog_usrid' => $usrId);
			$trlogResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$trlogResult = $trlogResult->num_rows();

			if(empty($jmResult) && empty($slogResult) && empty($tmResult) && empty($tlogResult) && empty($trlogResult))
			{
				//--delete from user device table
				$tblName = 'eot_user_devices';
				$condition = array('ud_usrid' => $usrId);
				$result = $this->CommonModel->deleteData($tblName,$condition);

				$tblName = 'eot_user';
				$condition = array('usr_id' => $usrId);
				$result = $this->CommonModel->deleteData($tblName,$condition);
				if($result)
				$responseArray = array("success"=>true,'message'=>'User deleted successfully.', 'data'=>[]);
				else
				$responseArray = array("success"=>false,'message'=>'User not deleted, please try again.', 'data'=>[]);
			}
			else
			{
				$responseArray = array("success"=>false,'message'=>"You can't delete this user, because it is already used.", 'data'=>[]);
			}
		}
		return $responseArray;
	}

	public function getRightsList()
	{
		$this->db->select("rgt_id as rgtIds,rgt_code as code,rgt_subject as sub,rgt_description as des");
		$rgtResult = $this->db->get('eot_rights');
		return array("success"=>true, 'message'=>'Rights found.', 'data'=>$rgtResult->result_array());
	}

	public function changePassword($usrId,$oldPassword,$newPassword)
    {
        $responseArray = array();

        //--check required params
		if (!$usrId) 
			$responseArray = array('success'=> false, 'message' => 'User id required.', 'data'=>[]);
		else if (!$oldPassword) 
			$responseArray = array('success'=> false, 'message' => 'Old password required.', 'data'=>[]);
		else if (!$newPassword) 
			$responseArray = array('success'=> false, 'message' => 'New password required.', 'data'=>[]);
		else
		{
			$fields = 'usr_email';
	        $tblName = 'eot_user';
	        $condition = array('usr_id' => $usrId, 'usr_password' => MD5($oldPassword));
	        $result = $this->CommonModel->getData($fields,$tblName,$condition);
	        if($result->num_rows())
	        {
		        $tblName = 'eot_user';
		        $data = array('usr_password' => MD5($newPassword));
		        $condition = array('usr_id' => $usrId);
		        $response = $this->CommonModel->updateData($tblName,$data,$condition);
	            if($response)
	                $responseArray = array("success"=>true,"message"=>"Password changed successfully.", 'data'=>[]);
	            else
	                $responseArray = array("success"=>false,"message"=>"Error while changing password, please try again.", 'data'=>[]);
	        }
	        else
	        {
	            $responseArray = array("success"=>false,"message"=>"Old password does not match, please try again.", 'data'=>[]);
	        }
	    }
        return $responseArray;
    }

    public function logout($udId)
    {
    	$responseArray = array();
        if($this->session->has_userdata('loginData'))
        {
	        //--check required params
			if (!$udId) 
				$responseArray = array('success'=> false, 'message' => 'User detail id required.');
			else
			{
		        $tblName = 'eot_user_devices';
				$condition = array('ud_id' => $udId);
				$updateResult = $this->CommonModel->deleteData($tblName,$condition);
		        if ($updateResult) 
		        {
		            //--unset session
		        	$this->session->unset_userdata('loginData');
		        	$responseArray = array("success"=>true,"message"=>"Logout successfully.", 'data'=>[]);
		        }
		        else
	           		$responseArray = array("success"=>false,"message"=>"Error in logout, please try again.", 'data'=>[]);
			}
        }
        else
        {
        	$responseArray = array("success"=>false,"message"=>"Session expire, please try again.");
        }
        return $responseArray;
    }
}
?>