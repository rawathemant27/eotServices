<?php
class CommonModel extends CI_Model
{	
	function __construct()
	{
		parent::__construct();
	}

	//-- common method for insert data
	public function insertData($tblName,$data)
	{
		$this->db->insert($tblName,$data);
		return $this->db->insert_id();
	}

	//-- common method for get data
	public function getData($fields,$tblName,$condition)
	{
		$this->db->select($fields);
		return $this->db->get_where($tblName,$condition);
	}

	//-- common method for update data
	public function updateData($tblName,$data,$condition)
	{
		return $this->db->update($tblName,$data,$condition);
	}

	//-- common method for delete record
	public function deleteData($tblName,$condition)
	{
		return $this->db->delete($tblName,$condition);
	}

	//--function for return json
    public function getJsonData($arrayResponse)
	{
		return json_encode($arrayResponse);
	}
	
	//--for get user basic details
	public function getUserBasicDetails($usrId)
    {
        $data = array();
        
        //-- get user result
        $fields = "usr_id as usrId,usr_compid as compId,usr_fname as fnm,usr_lname as lnm,usr_email as email,usr_mobile1 as mob1,usr_mobile2 as mob2,usr_address as adr,usr_timezone as tz,usr_type as type, CASE WHEN usr_image!='' THEN CONCAT('uploads/profile/',usr_image) ELSE usr_image END as img,usr_isactive as isactive";
        $tblName = 'eot_user';	
        $condition = array('usr_id' => $usrId);
        $usrResult = $this->getData($fields,$tblName,$condition);
        $data = $usrResult->row();

        //--get user rights
        $this->db->select('rgt.rgt_id as rgtIds,rgt.rgt_code as code');
        $this->db->from('eot_rights_user_mm as rumm');
        $this->db->join('eot_rights as rgt','rgt.rgt_id = rumm.rumm_rgtid');
        $this->db->where('rumm_usrid',$usrId);
        $rightsResult = $this->db->get();
        $data->rights = $rightsResult->result_array();
        return $data;
    }

	//-- Function to get latitude and longitude of given address
	public function getLatLong($address)
	{
		if(!empty($address))
		{
			$data = array();
			$apiKey = 'AIzaSyDoT-OXOifHprmVoD20gQffuVPxcmWX4uw';//Api key 
			
			//--Formatted address
			$formattedAddr = str_replace(' ','+',$address);
			
			//--Send request and receive json data by address
			$geocodeFromAddr = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddr.'&sensor=true_or_false&key='.$apiKey);
			$output = json_decode($geocodeFromAddr);

			if (isset($output->results[0]->geometry)) 
			{
				$data['latitude'] = $output->results[0]->geometry->location->lat; 
				$data['longitude'] = $output->results[0]->geometry->location->lng;
				return $data;
			}
			else
			{
				echo json_encode(array("success"=>false,'message'=>'Google API failed, please try again..', 'data'=>[]));
				die();
			}
		}
		else
		{
			echo json_encode(array("success"=>false,'message'=>'Please send address.', 'data'=>[]));
			die();
		}
	}

	//-- function for image upload 
	public function imageUpload($name,$path)
	{
		$imgName = '';
		$config['upload_path']   = $path;
		$config['allowed_types'] = 'jpg|jpeg|png|gif|bmp|tif';  
		$config['file_name'] = date('Ymdhmi')."_".rand(100,999);
		
		$this->load->library('upload', $config);
		if ($this->upload->do_upload($name)) 
		{
			$imgData = $this->upload->data(); 
			$imgName = $imgData['file_name'];
		}
		else
		{
			$errorData = $this->upload->display_errors();
			echo json_encode((array('success'=> false, 'message' => $errorData)));
            die();
		}
		return $imgName;
	}

	public function sendEmail($to,$subject,$message)
	{
		$fromEmail = "phpproject99939@gmail.com";
		$fromName = "EOT";
		$fromEmailPassword = "php99939";

		$config['protocol'] = "smtp";
		$config['smtp_host'] = "ssl://smtp.gmail.com";
		$config['smtp_port'] = "465";
		$config['smtp_user'] = $fromEmail;
		$config['smtp_pass'] = $fromEmailPassword;
		$config['charset'] = "utf-8";
		$config['mailtype'] = "html";
		$config['newline'] = "\r\n";

		$CI = get_instance();
		$CI->load->library('email');
		$CI->email->initialize($config);
		$CI->email->from($fromEmail, ucfirst($fromName));
		$CI->email->to($to);
		$CI->email->subject(ucfirst($subject));
		$CI->email->message($message);
		return $CI->email->send();
	}

	public function changeTimeInUTC($dateTime)
	{
		if (!$dateTime) 
		{
			$timeStamp = "";
		}
		else
		{
			//--get time zone
			$headers = $this->input->request_headers();
			$userTimeZone = isset($headers['User-Time-Zone'])?$headers['User-Time-Zone']:"Asia/Kolkata";
			
			$userTimeZone = new DateTimeZone($userTimeZone);
			$UTC = new DateTimeZone('UTC');
			$date = new DateTime($dateTime, $userTimeZone );
			$date->setTimezone($UTC);
			$utcData =  $date->format('Y-m-d h:i:s a');
			$timeStamp = strtotime($utcData . ' UTC');
		}
		return $timeStamp;
	}

	//-- function for create different size thumbnail 
	public function createThumbnail($logoName)
	{
		$this->load->library('image_lib');

		//-- Resize to medium
		$config['source_image'] = './uploads/companyLogo/'.$logoName;
		$config['new_image'] = './uploads/companyLogo/logo1/'.$logoName;
		$config['width'] = 100;
		$config['height'] = 100;

		$this->image_lib->initialize($config); 
		if(!$this->image_lib->resize()){
			echo $this->image_lib->display_errors();
		}
		$this->image_lib->clear(); 

		//-- Resize to small
		$config['new_image'] = './uploads/companyLogo/logo2/'.$logoName;
		$config['width'] = 50;
		$config['height'] = 50;

		$this->image_lib->initialize($config); 
		if(!$this->image_lib->resize()){
			echo $this->image_lib->display_errors();
		}
		$this->image_lib->clear();
	}
}
?>