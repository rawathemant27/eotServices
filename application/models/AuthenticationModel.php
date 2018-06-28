<?php
class AuthenticationModel extends CI_Model
{	
	function __construct()
	{
		parent::__construct();
	}

	public function addCompany($compData)
    {
    	$this->db->trans_begin();//--db transaction start
        $responseArray = array();

        //--check required params
		if (!$compData['comp_name'])
			$responseArray = array("success"=>false,'message'=>'Company name reqiured');
		else if (!$compData['comp_email'])
			$responseArray = array("success"=>false,'message'=>'Email required');
		else
		{
			$fields = 'comp_id';
			$tblName = 'eot_company';

			//--check company name and email exist or not
			$condition = array('comp_email' => $compData['comp_email'],'comp_name' => $compData['comp_name'],'comp_status' => 1);
			$companyResult = $this->CommonModel->getData($fields,$tblName,$condition);
			if ($companyResult->num_rows()){
				return array("success"=>false,'message'=>'You are already member of EyeOnTask.');
				die;
			}

			//--check company name exist or not
			$condition = array('comp_name' => $compData['comp_name'],'comp_status' => 1);
			$companyResult = $this->CommonModel->getData($fields,$tblName,$condition);
			if ($companyResult->num_rows()){
				return array("success"=>false,'message'=>'Company name already exist, please try with new company name.');
				die;
			}

			//--check email exist or not
			$condition = array('comp_email' => $compData['comp_email'],'comp_status' => 1);
			$companyResult = $this->CommonModel->getData($fields,$tblName,$condition);
			if ($companyResult->num_rows()){
				return array("success"=>false,'message'=>'An account with this email address already exists.');
				die;
			}
			
            $_SESSION['email'] = $compData['comp_email'];//--Create session of email

            $fields = "comp_id,comp_email,comp_status,comp_name";
			$condition = array('comp_email' => $compData['comp_email']);
			$compResult = $this->CommonModel->getData($fields,$tblName,$condition);
	        if($compResult->num_rows())
	        {
	            $compResult = $compResult->row();
	            if ($compResult->comp_status == 1) 
	            {
	            	session_destroy();
	                $responseArray = array('success'=> false, 'message' => 'You are already member of EyeOnTask ,please try again.');
	            }
	            else if ($compResult->comp_name != $compData['comp_name']) 
	            {
	                //-- query to updata company detail
	                $data['comp_name'] = $compData['comp_name'];
	                $data['comp_verification_code'] = $compData['comp_verification_code'];
			        $condition = array('comp_email' => $compData['comp_email']);
			        $result = $this->CommonModel->updateData($tblName,$data,$condition);
	                if($result)
	                {
	                    //-- send mail for verification code
	                    $to = $compData['comp_email'];
	                    $subject = 'EyeonTask - Registration verification code for company.';
	                    $message = 'Your Eye on Task company <b>'.$compData['comp_name'].'</b> verification code is: <b>'.$compData['comp_verification_code'].'</b>';
	                    $message .="<br /><br />Support team,<br />EyeonTask!";
	                    $this->CommonModel->sendEmail($to,$subject,$message);	
	                    $responseArray = array("success"=>true, 'message'=>'You are already registered on EyeOnTask with this email and verification code is again sent to '.$to.'. If in case email not get then, please contact to EyeOnTask support team.');
                    }
	                else
	                    $responseArray = array("success"=>false, 'message'=>'Company not registered, please try again.');
	               
	            }
	            else
	            	$responseArray = array("success"=>true, 'message'=>'This company already exists, Please verify.');
	        }
	        else
	        {
		   	    $compId = $this->CommonModel->insertData($tblName,$compData);
	            if($compId)
	            {
	                //-- send mail for verification code
	                $to = $compData['comp_email'];
	                $subject = 'EyeonTask - Registration verification code for company.';
	                $message = 'Your EyeonTask company <b>'.$compData['comp_name'].'</b> verification code is: <b>'.$compData['comp_verification_code'].'</b>';
	                $message .="<br /><br />Support team,<br />EyeonTask!";
	                $this->CommonModel->sendEmail($to,$subject,$message);
	                $responseArray = array("success"=>true, 'message'=>'You are successfully registered and verification code is sent to '.$to.'. If in case email not get then, please contact to EyeOnTask support team.');
	            }
	            else
	           		$responseArray = array("success"=>false, 'message'=>'Company not registered, please try again.');
	        }
	    }

	    //--use db tansaction
		if($this->db->trans_status() === FALSE){
		    $this->db->trans_rollback();
		    $responseArray = array("success" => false, "message" => 'Something went wrong, please try again.');
		}
		else{
		    $this->db->trans_commit();
		} 
	    return $responseArray;
    }

    public function resendVerificationCode($email)
    {
        $this->db->trans_begin();//--db transaction start
        $responseArray = array();

    	//--update verification code
    	$data['comp_verification_code'] = rand(100000,999999);
    	$tblName = 'eot_company';
		$condition = array('comp_email' => $email);
		$result = $this->CommonModel->updateData($tblName,$data,$condition);

		$fields = "comp_name,comp_email,comp_verification_code,comp_status";
		$tblName = 'eot_company';
		$condition = array('comp_email' => $email);
		$compResult = $this->CommonModel->getData($fields,$tblName,$condition);
        if ($compResult->num_rows()) 
        {
            $compResult = $compResult->row();
            if ($compResult->comp_status == 1) 
            {
            	session_destroy();
            	$responseArray = array('success'=> true, 'message' => 'You are already member of EyeOnTask, please try again.');
            }
            else
            {
                //-- send mail for verification code
                $to = $email;
                $subject = 'EyeonTask - Registration verification code for company.';
                $message = 'Your EyeonTask company <b>'.$compResult->comp_name.'</b> verification code is: <b>'.$compResult->comp_verification_code.'</b>';
                $message .="<br /><br />Support team,<br />EyeonTask!";
                $this->CommonModel->sendEmail($to,$subject,$message);
                $responseArray = array("success"=>'true', "message"=>'Your EyeOnTask verification code is successfully send to '.$email.'. If in case email not get then, please contact to EyeOnTask support team.');
            }
        }
        else
        {
            $responseArray = array("success"=>false, "message"=>'Your EyeOnTask verification code not send, Please try again.');
        }

        //--use db tansaction
		if($this->db->trans_status() === FALSE){
		    $this->db->trans_rollback();
		    $responseArray = array("success" => false, "message" => 'Something went wrong, please try again.');
		}
		else{
		    $this->db->trans_commit();
		} 
        return $responseArray;
    }

	public function verifyCompanyCode($email,$code)
	{
		$this->db->trans_begin();//--db transaction start
		$responseArray = array();

		//--check email and verification code
		$fields = "comp_id,comp_name,comp_email,comp_status";
		$tblName = 'eot_company';
		$condition = array('comp_email' => $email,'comp_verification_code'=>$code);
		$result = $this->CommonModel->getData($fields,$tblName,$condition);
		if ($result->num_rows()) 
		{
			$result = $result->row();
			if ($result->comp_status == 1) 
			{
				$responseArray = array('success'=> false, 'message' => 'You are already verified, please try again.');
			}
			else
			{
				//--Update company 
				$data['comp_verification_code'] = $code;
				$data['comp_status'] = 1;
				$tblName = 'eot_company';
				$condition = array('comp_id' => $result->comp_id);
				$response = $this->CommonModel->updateData($tblName,$data,$condition);
				if ($response) 
				{
					//--get data from company table
					$fields = "comp_id,comp_name,comp_email,comp_logo,comp_status";
					$tblName = 'eot_company';
					$condition = array('comp_id' => $result->comp_id);
					$comResult = $this->CommonModel->getData($fields,$tblName,$condition);
					$comResult = $comResult->row();
					$compId = $comResult->comp_id;

					$this->load->helper('string');//load string helper
					$pass = random_string('alnum', 6);

					//--for Admin registration
					$usrData['usr_fname'] = $comResult->comp_name;
					$usrData['usr_email'] = $comResult->comp_email;
					$usrData['usr_password'] = MD5($pass);
					$usrData['usr_isactive'] = 1;
					$usrData['usr_timezone'] = 'Asia/Kolkata';
					$usrData['usr_compId'] = $compId;
					$usrData['usr_type'] = 3;
					$usrData['usr_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
					$usrData['usr_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
					$tblName = 'eot_user';	
					$userId = $this->CommonModel->insertData($tblName,$usrData);
					if($userId)
					{
						//-- give all rights for super admin
						$this->db->select("rgt_id");
						$rgtResult = $this->db->get('eot_rights');
						if($rgtResult->num_rows())
						{
							foreach ($rgtResult->result_array() as $key) 
							{
								$tblName = 'eot_rights_user_mm';
								$rummData['rumm_usrid'] = $userId;
								$rummData['rumm_rgtid'] = $key['rgt_id'];
								$usrResult = $this->CommonModel->insertData($tblName,$rummData);
							} 
						}

						//-- insert company setting
						$tblName = 'eot_comp_setting';
						$setData['set_currency'] = '';
						$setData['set_compid'] = $compId;
						$setData['set_city'] = '';
						$setData['set_state'] = '';
						$setData['set_country'] = '';
						$setData['set_email'] = $email;
						$setData['set_bcc'] = '';
						$setData['set_duration'] = 1;
						$setData['set_shedule_time'] = 1;
						$setData['set_capture_time'] = 1;
						$this->CommonModel->insertData($tblName,$setData);

						//--logic for company logo thumblin creation
						if($comResult->comp_logo != 'default_logo.png')
						{
							$logoName = $comResult->comp_logo;
							$this->CommonModel->createThumbnail($logoName);
						}
						else
						{
							$logoName = 'default_logo.png';
						}	

						//-- insert invoice setting
						$data = array
						(
							array
							(
								'invset_compid' => $compId,
								'invset_label' => 'Provider',
								'invset_value' => $comResult->comp_name,
								'invset_status' => 1,
								'invset_logo_type' =>''
							),
							array
							(
								'invset_compid' => $compId,
								'invset_label' => 'Logo',
								'invset_value' => $logoName,
								'invset_status' => 1,
								'invset_logo_type' => '2x'
							),
							array
							(
								'invset_compid' => $compId,
								'invset_label' => 'Travel Time',
								'invset_value' => '',
								'invset_status' => '0',
								'invset_logo_type' => ''
							)
						);
                        $this->db->insert_batch('eot_invoice_setting', $data);

						//-- send mail for username and password 
						$url = "www.google.com";
						$to = $email;
						$subject = 'EyeOnTask - Registration confirmation mail.';
						$this->load->helper('url');	
						$message = 'You are successfully registered in EyeOnTask!<br><br>
						Your account has been created, Please login via this link: '.anchor($url,'Click Here') .
						'<br><br>Here are your login details.<br>
						---------------------------	----------------------<br>
						Username: '.$email.'<br>
						Password: '.$pass.'<br>
						-------------------------------------------------<br><br>
						Support team,<br />EyeOnTask!';

						//--call function to send mail 
						$this->CommonModel->sendEmail($to,$subject,$message);
						$responseArray = array("success" =>true, 'message'=>'You are successfully verify, please login using username and password,which is sent to '.$to.'. If in case email not get then, please contact to EyeOnTask support team.');
					}
					else
					{
					$responseArray = array("success"=>'Error','message'=>'Company verified but super admin not register,please try again.');
					}
				}
				else
				{
					$responseArray = array("success"=>false,'message'=>'Something went wrong, please try again.');
				}
			}
		}
		else
		{
			$responseArray = array("success"=>false,'message'=>'Your verification code is wrong, please re-enter.');
		}

		//--use db tansaction
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$responseArray = array("success" => false, "message" => 'Something went wrong, please try again.');
		}
		else{
			$this->db->trans_commit();
		} 
		return $responseArray;
	}

	public function login($email,$pass,$devType,$devId)
    {
        $this->db->trans_begin();//--db transaction start
		$responseArray = array();
        
        //--check required params
		if (!$email) 
			$responseArray = array('success'=> false, 'message' => 'Email required.', 'data'=>[]);
		else if (!$pass) 
			$responseArray = array('success'=> false, 'message' => 'Password required.', 'data'=>[]);
		else
		{
			//--for match username and password
	        $fields = 'usr_id';
	        $tblName = 'eot_user';	
	        $condition = array('usr_email' => $email,'usr_password' => MD5($pass));
	        $result = $this->CommonModel->getData($fields,$tblName,$condition);
	        if($result->num_rows())
	        {
	       	    $fields = 'usr_id,usr_compid';
		        $tblName = 'eot_user';	
		        $condition = array('usr_email' => $email,'usr_password' => MD5($pass),'usr_isactive' => 1);
		        $loginResult = $this->CommonModel->getData($fields,$tblName,$condition);
		        if($loginResult->num_rows())
		        {
		            $loginResult = $loginResult->row();
		            
		            //-- for insert user device details
			        $tblName = 'eot_user_devices';	
			        $usrData['ud_usrid'] = $loginResult->usr_id;
		            $usrData['ud_device_type'] = $devType;
		            $usrData['ud_device_id'] = $devId;
		            $udId = $this->CommonModel->insertData($tblName,$usrData);
		            if ($udId) 
		            {    
			            $data = array();
			            $usrId = $loginResult->usr_id;
		                $this->db->select("usr.usr_id as usrId,usr.usr_compid as compId,usr.usr_fname as fnm,usr.usr_lname as lnm,usr.usr_email as email,usr.usr_mobile1 as mob1,usr.usr_mobile2 as mob2,usr.usr_address as adr,usr.usr_timezone as tz,usr.usr_type as type, CASE WHEN usr.usr_image!='' THEN CONCAT('uploads/profile/',usr_image) ELSE usr.usr_image END as img,ud.ud_id as udId");
		                $this->db->from('eot_user as usr');
		                $this->db->join('eot_user_devices as ud','ud.ud_usrid = usr.usr_id');
		                $this->db->where('usr_id',$usrId);
		                $this->db->where('ud_id',$udId);
		                $usrResult = $this->db->get();
		                $data = $usrResult->result_array();

		                //--get user rights
		            	$this->db->select("*");
		            	$this->db->from('eot_rights_user_mm as rumm');
		            	$this->db->join('eot_rights as rgt','rgt.rgt_id = rumm.rumm_rgtid');
		            	$this->db->where('rumm_usrid',$loginResult->usr_id);
		            	$rightsResult = $this->db->get();
		            	$data[0]['rights'] = $rightsResult->result_array();

		            	//--initialize session
		            	$loginData['usrId'] = $data[0]['usrId'];
		            	$loginData['compId'] = $data[0]['compId'];
		            	$loginData['udId'] = $data[0]['udId'];
		            	$loginData['loggedIn'] = true;
		            	$this->session->set_userdata('loginData', $loginData);

		            	//--returm result
		            	$responseArray = array("success"=>true, 'message'=>'Login successfully.', "data"=>$data);
		            }
		            else
		            {
		                $responseArray = array("success"=>false,"message"=>"Query failed, please try again.", 'data'=>[]);
		            }
		        }
		        else
		        {
		            $responseArray = array("success"=>false,"message"=>"You are not a active user.", 'data'=>[]);
		        }
	        }
	        else
	        {
	            $responseArray = array("success"=>false,"message"=>"Username or password does not match.", 'data'=>[]);
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
	
	public function forgotPassword($email,$cCode)
	{
		$responseArray = array();

        //--check required params
		if (!$email) 
			$responseArray = array('success'=> false, 'message' => 'Email required.', 'data'=>[]);
		else
		{
			/*//--if compny code is not get
			if (!$cCode) 
			{
				$codeResult = $this->getUserCompanyCode($email);
				if ($codeResult['success']) 
					$cCode = $codeResult['cCode'];
				else
					return $codeResult;
			}*/

			//--check email exist or not
			$fields = "usr_fname,usr_lname,usr_email,usr_id";
			$tblName = 'eot_user';
			//$condition = array('usr_email' => $email, 'usr_compCode' =>$cCode);
			$condition = array('usr_email' => $email);
			$userResult = $this->CommonModel->getData($fields,$tblName,$condition);
			if($userResult->num_rows())
			{
				$userResult = $userResult->row();
				$usrId = $userResult->usr_id;
				$usrFname = $userResult->usr_fname;
				$usrLname = $userResult->usr_lname;

				//--create random string
				$this->load->helper('string');
				$key = random_string('alnum',6);

				//-- store key and userId in DB with expiry date
				$today = gmdate('Y-m-d h:i:s a').' UTC';	

				$fields = "fgp_key as key,fgp_expirydate as expDate";
				$tblName = 'eot_forgot_password';
				$condition = array('fgp_usrid' => $usrId);
				$fgpResult = $this->CommonModel->getData($fields,$tblName,$condition);
				if($fgpResult->num_rows())
				{
					$tblName = 'eot_forgot_password';
					$fgpData['fgp_expiryDate'] = strtotime('+24 hours', strtotime( $today ));
					$fgpData['fgp_key'] = $key;
					$condition = array('fgp_usrid' => $usrId);
					$result = $this->CommonModel->updateData($tblName,$fgpData,$condition);
				}
				else
				{ 
					$tblName = 'eot_forgot_password';
					$fgpData['fgp_usrid'] = $usrId;
					$fgpData['fgp_key'] = $key;
					$fgpData['fgp_expiryDate'] = strtotime('+24 hours', strtotime( $today ));
					$result = $this->CommonModel->insertData($tblName,$fgpData);
				}

				$subject = "Eye on Task - Forgotten password reset";
				$message = "<b>Hi ".$usrFname." ".$usrLname."</b>,<br><br>You recently requested to reset your password for your Eye on Task account. Here is your password reset key.</br></br> <b><h1>".$key."</h1></b><br>This key will expire in 24 hours, please use this to reset your password.<br><br>Support team,<br>Eye on Task";

				//--call function to send mail 
				//$emailResult = $this->CommonModel->sendEmail($email,$subject,$message); 
				$responseArray = array("success"=>true,"message"=>"A mail has been sent with password reset instruction on your register email id.");
			}	
			else
			{
				$responseArray = array("success"=>false,"message"=>"Email does not found, please try with right email.", 'data'=>[]);
			}
		}
		return $responseArray;
	}

	public function forgotPasswordKey($email,$key,$cCode)
	{
		$responseArray = array();

		//--check required params
		if (!$email) 
			$responseArray = array('success'=> false, 'message' => 'Email required.', 'data'=>[]);
		else if (!$key) 
			$responseArray = array('success'=> false, 'message' => 'Key required.', 'data'=>[]);
		else
		{
			/*if (!$cCode) 
			{
				$codeResult = $this->getUserCompanyCode($email);
				if ($codeResult['success']) 
					$cCode = $codeResult['cCode'];
				else
					return $codeResult;
			}*/

			//--check email exist or not
			$fields = "usr_id";
			$tblName = 'eot_user';
			//$condition = array('usr_email' => $email, 'usr_compCode' =>$cCode);
			$condition = array('usr_email' => $email);
			$userResult = $this->CommonModel->getData($fields,$tblName,$condition);
			if($userResult->num_rows())
			{
				$userResult = $userResult->row();
				$usrId = $userResult->usr_id;

				$fields = "fgp_expiryDate";
				$tblName = 'eot_forgot_password';
				$condition = array('fgp_usrid' => $usrId,'fgp_key' => $key);
				$fgpResult = $this->CommonModel->getData($fields,$tblName,$condition);
				if($fgpResult->num_rows())
				{
					$userDataNew = $fgpResult->row();
					$expiryDate = $userDataNew->fgp_expiryDate;

					//-- compare expiry date with now
					$today = strtotime(gmdate('Y-m-d h:i:s a'));
					if($today <= $expiryDate)
						$responseArray = array("success"=>true,"message"=>"Key matched.","usrId"=>$usrId);
					else
						$responseArray = array("success"=>false,"message"=>"Key has been expired, please try again.", 'data'=>[]);
				}
				else
				{
					$responseArray = array("success"=>false,"message"=>"Invalid key, please enter right key.", 'data'=>[]);
				} 
			}
			else
			{
				$responseArray = array("success"=>false,"message"=>"Email id not found.", 'data'=>[]);
			}
		}
		return $responseArray;
	}

	public function forgotPasswordReset($usrId,$password)
	{
		$responseArray = array();

		//--check required params
		if (!$usrId) 
			$responseArray = array('success'=> false, 'message' => 'User id required.', 'data'=>[]);
		else if (!$password) 
			$responseArray = array('success'=> false, 'message' => 'Password required.', 'data'=>[]);
		else
		{
			$tblName = 'eot_user';
			$data = array('usr_password' => MD5($password));
			$condition = array('usr_id' => $usrId);
			$result = $this->CommonModel->updateData($tblName,$data,$condition);
			if($result)
				$responseArray = array("success"=>true,"message"=>"Password reset successfully.", 'data'=>[]);
			else 
				$responseArray = array("success"=>false,"message"=>"Error while changing password, please try again.", 'data'=>[]);
		}
		return $responseArray;
	}
}
?>