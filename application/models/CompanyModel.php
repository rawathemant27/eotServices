<?php
class CompanyModel extends CI_Model
{	
	function __construct()
	{
		parent::__construct();
	}

	public function addClient($cltData,$siteData,$conData)
	{
		$this->db->trans_begin();//--db transaction start
		$responseArray = array();

		//--check required params
		if (!$cltData['clt_compid']) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else if (!$siteData['site_name']) 
			$responseArray = array('success'=> false, 'message' => 'Client site name required.', 'data'=>[]);
		else if (!$siteData['site_address']) 
			$responseArray = array('success'=> false, 'message' => 'Address required.', 'data'=>[]);
		else if (!$conData['con_name']) 
			$responseArray = array('success'=> false, 'message' => 'Client contact name required.', 'data'=>[]);
		else
		{
			$tblName = 'eot_client';
			$cltId = $this->CommonModel->insertData($tblName,$cltData);
			if($cltId)
			{			
				//--Get latitude and longitude from address for client site
				$LatLongResult = $this->CommonModel->getLatLong($siteData['site_address']);

				//--add client site
				$tblName = 'eot_site';
				$siteData['site_cltid'] = $cltId;				
				$siteData['site_lat'] = $LatLongResult['latitude'];
				$siteData['site_long'] = $LatLongResult['longitude'];
			   	$this->CommonModel->insertData($tblName,$siteData);

			   	//--add client contact
				$tblName = 'eot_contact';
				$conData['con_cltid'] = $cltId;
				$this->CommonModel->insertData($tblName,$conData);

				$clientResult = $this->getClientDetail($cltId);
				$responseArray = array('success'=> true, 'message'=>'Client added successfully.','data' => $clientResult['data']);
			}
			else
				$responseArray = array("success"=>false,'message'=>'Client not added, please try again.');
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

	public function getClientList($compId,$limit,$index,$search)
	{
		$responseArray = array();

		//--check required params
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$this->db->select('SQL_CALC_FOUND_ROWS clt.clt_id as cltId,clt.clt_name as nm,clt.clt_payment_type as pymtType,clt.clt_gst_no as gstNo,clt.clt_tin_no as tinNo,clt.clt_industry as industry,clt.clt_note as note,clt.clt_isactive as isactive,acct.acct_id as accid,acct.acct_type as acctype,site.site_id as siteId,site.site_name as snm,site.site_address as adr,site.site_city as city,site.site_state as state,site.site_country as ctry,con.con_id as conId,con.con_name as cnm,con.con_email as email,con.con_mobile1 as mob1,con.con_mobile2 as mob2',FALSE);
			$this->db->from('eot_client as clt');
			$this->db->join('eot_comp_acct_info as acct','acct.acct_id = clt.clt_payment_type','left');
			$this->db->join('eot_site as site','site.site_cltid = clt.clt_id AND site_default = 1','left');
			$this->db->join('eot_contact as con','con.con_cltid = clt.clt_id AND con_default = 1','left');
			$this->db->where('clt_compid',$compId);
			if ($search){
				$this->db->like('clt_name',$search);
			}
			$this->db->order_by('clt_name','asc');
			if ($limit || $index)
				$this->db->limit($limit,$index);
			$clientResult = $this->db->get();

			//for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
			$total_count = $total_record->row();
			$total_count = $total_count->total_count;

			//--final result
			$responseArray = array("success"=>true,'data'=>$clientResult->result_array(),'count'=>$total_count);
		}
		return $responseArray;
	}

	public function getClientDetail($cltId)
	{
		$responseArray = array();

		//--check required params
		if (!$cltId) 
			$responseArray = array('success'=> false, 'message' => 'Client id required.', 'data'=>[]);
		else
		{
			$this->db->select('SQL_CALC_FOUND_ROWS clt.clt_id as cltId,clt.clt_name as nm,clt.clt_payment_type as pymtType,clt.clt_gst_no as gstNo,clt.clt_tin_no as tinNo,clt.clt_industry as industry,clt.clt_note as note,clt.clt_isactive as isactive,acct.acct_id as accid,acct.acct_type as acctype,site.site_id as siteId,site.site_name as snm,site.site_address as adr,site.site_city as city,site.site_state as state,site.site_country as ctry,con.con_id as conId,con.con_name as cnm,con.con_email as email,con.con_mobile1 as mob1,con.con_mobile2 as mob2',FALSE);
			$this->db->from('eot_client as clt');
			$this->db->join('eot_comp_acct_info as acct','acct.acct_id = clt.clt_payment_type','left');
			$this->db->join('eot_site as site','site.site_cltid = clt.clt_id AND site_default = 1','left');
			$this->db->join('eot_contact as con','con.con_cltid = clt.clt_id AND con_default = 1','left');
			$this->db->where('clt_id',$cltId);
			$clientResult = $this->db->get();
			if($clientResult->num_rows())
				$responseArray = array('success'=> true, 'message'=>'Client found.', 'data'=>$clientResult->row());
			else
				$responseArray = array("success"=>false,'message'=>'Client not get, please try again.');
		}
		return $responseArray; 
	}

	public function updateClient($cltId,$cltData)
	{
		$responseArray = array();

		//--check required params
		if (!$cltId) 
			$responseArray = array('success'=> false, 'message' => 'Client id required.', 'data'=>[]);
		else
		{
			$tblName = 'eot_client';
			$condition = array('clt_id' => $cltId);
			$result = $this->CommonModel->updateData($tblName,$cltData,$condition);
			if($result)
				$responseArray = array('success'=> true, 'message'=>'Client updated successfully.', 'data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Client not update, please try again.', 'data'=>[]);
		}
		return $responseArray;	
	}

	public function deleteClient($cltId)
	{
		$this->db->trans_begin();//--db transaction start
		$responseArray = array();

		//--check required params
		if (!$cltId) 
			$responseArray = array('success'=> false, 'message' => 'Client id required.', 'data'=>[]);
		else
		{
			//--check client record in contact table
			$fields = 'con_cltid';
			$tblName = 'eot_contact';	
			$condition = array('con_cltid' => $cltId);
			$conResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$conResult = $conResult->num_rows();

			//--check client record in site table
			$fields = 'site_cltid';
			$tblName = 'eot_site';	
			$condition = array('site_cltid' => $cltId);
			$siteResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$siteResult = $siteResult->num_rows();

			//--check client record in job table
			$fields = 'job_cltid';
			$tblName = 'eot_job';	
			$condition = array('job_cltid' => $cltId);
			$jobResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$jobResult = $jobResult->num_rows();

			if(empty($conResult) && empty($siteResult) && empty($jobResult))
			{
				$tblName = 'eot_client';
				$condition = array('clt_id' => $cltId);
				$result = $this->CommonModel->deleteData($tblName,$condition);
				if($result)
					$responseArray = array("success"=>true,'message'=>'Client deleted successfully.', 'data'=>[]);
				else
					$responseArray = array("success"=>false,'message'=>'Client not deleted, please try again.', 'data'=>[]);
			}
			else
			{
				$responseArray=	array("success"=>false,'message'=>
					"You can't delete this client, because it is already used.", 'data'=>[]);
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

	public function addClientSite($siteData)
    {
    	$responseArray = array();

    	//--check required params
    	if (!$siteData['site_cltid']) 
			$responseArray = array('success'=> false, 'message' => 'Client id required.');
		else if (!$siteData['site_name']) 
			$responseArray = array('success'=> false, 'message' => 'Site name required.');
		else
		{    
		    //--Get latitude and longitude from address
			$LatLongResult = $this->CommonModel->getLatLong($siteData['site_address']);

			$tblName = 'eot_site';		    
			$siteData['site_lat'] = $LatLongResult['latitude'];
			$siteData['site_long'] = $LatLongResult['longitude'];

			//--logic if default site get true
			if ($siteData['site_default']){
				$data = array('site_default' => 0);
				$condition = array('site_default' => 1);
				$this->CommonModel->updateData($tblName,$data,$condition);
			}
	
		   	$siteId = $this->CommonModel->insertData($tblName,$siteData);
		    if ($siteId)
		    {
		    	$siteResult = $this->getClientSiteDetail($siteId);
		    	$responseArray = array("success"=>true,'message'=>'Site added successfully.','data' => $siteResult['data']);
		    }
		    else
		    	$responseArray = array("success"=>false,'message'=>'Site not added, please try again.'); 
		}
		return $responseArray;
	}

	public function getClientSiteList($cltId,$limit,$index,$search)
	{
		$responseArray = array();

		//--check required params
		if (!$cltId) 
			$responseArray = array('success'=> false, 'message' => 'Client id required.', 'data'=>[]);
		else
		{
			$this->db->select('SQL_CALC_FOUND_ROWS site_id as siteId,site_cltid as cltId,site_name as snm,site_address as adr,site_city as city,site_state as state,site_country as ctry,site_zipcode as zip,site_lat as lat,site_long as lng',FALSE);
			$this->db->from('eot_site');
			$this->db->where('site_cltid',$cltId);
			if ($search){
				$this->db->like('site_name',$search);
			}
			$this->db->order_by('site_name','asc');
			if ($limit || $index)
				$this->db->limit($limit,$index);
			$siteResult = $this->db->get();

			//for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
	        $total_count = $total_record->row();
	        $total_count = $total_count->total_count;

	        //--final result
	        $responseArray = array("success"=>true,'data'=>$siteResult->result_array(),'count'=>$total_count);
		}
		return $responseArray;
	}

	public function getClientSiteDetail($siteId)
	{
		$responseArray = array();

		//--check required params
		if (!$siteId) 
			$responseArray = array('success'=> false, 'message' => 'Site id required.', 'data'=>[]);
		else
		{
			$fields = 'site_id as siteId,site_name as snm,site_address as adr,site_city as city,site_state as state,site_country as ctry,site_zipcode as zip,site_lat as lat,site_long as lng';
			$tblName = 'eot_site';	
			$condition = array('site_id' => $siteId);
			$siteResult = $this->CommonModel->getData($fields,$tblName,$condition);
			if ($siteResult->num_rows()) 
				$responseArray = array("success"=>true, 'message'=>'Site data found.', 'data'=>$siteResult->row());
			else
				$responseArray = array("success"=>false,'message'=>'Site data not get, please try again.', 'data'=>[]); 
		}
		return $responseArray;
	}

	public function updateClientSite($siteId,$siteData)
	{
		$responseArray = array();

		//--check required params
		if (!$siteId) 
			$responseArray = array('success'=> false, 'message' => 'Site id required.', 'data'=>[]);
		else
		{	
			//--Get latitude and longitude from address
			$LatLongResult = $this->CommonModel->getLatLong($siteData['site_address']);
			$siteData['site_lat'] = $LatLongResult['latitude'];
			$siteData['site_long'] = $LatLongResult['longitude'];

			$tblName = 'eot_site';	
			$condition = array('site_id' => $siteId);
			$updateResult = $this->CommonModel->updateData($tblName,$siteData,$condition);
			if ($updateResult) 
			{
				$siteResult = $this->getClientSiteDetail($siteId);
		    	$responseArray = array("success"=>true,'message'=>'Site updated successfully.','data' => $siteResult['data']);
			}
			else
				$responseArray = array("success"=>false,'message'=>'Site not updated, please try again.', 'data'=>[]); 
		}
		return $responseArray;
	}

	public function deleteClientSite($siteId)
	{
		$responseArray = array();

		//--check required params
		if (!$siteId) 
			$responseArray = array('success'=> false, 'message' => 'Site id required.', 'data'=>[]);
		else
		{
			//--check site record in job table
			$fields = 'job_siteid';
			$tblName = 'eot_job';	
			$condition = array('job_siteid' => $siteId);
			$jobResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$jobResult = $jobResult->num_rows();
			if(empty($jobResult))
			{
				//--check site is default or not
				$fields = 'site_id';
				$tblName = 'eot_site';	
				$condition = array('site_id' => $siteId,'site_default' => 1);
				$siteResult = $this->CommonModel->getData($fields,$tblName,$condition);
				if($siteResult->num_rows())
				{
					$responseArray = array("success"=>false,'message'=> "This is default client site.So, you can't delete this site.", 'data'=>[]);
				}
				else
				{
					$condition = array('site_id' => $siteId);
					$result = $this->CommonModel->deleteData($tblName,$condition);
					if($result)
						$responseArray = array("success"=>true,'message'=>'Site deleted successfully.', 'data'=>[]);
					else
						$responseArray = array("success"=>false,'message'=>'Site not deleted, please try again.', 'data'=>[]);
				}				
			}
			else
			{
				$responseArray = array("success"=>false,'message'=> "You can't delete this site, because it is already used.", 'data'=>[]);
			}
		}
		return $responseArray;
	}

	public function addClientContact($conData)
	{
		$responseArray = array();

		//--check required params
		if (!$conData['con_cltid']) 
			$responseArray = array('success'=> false, 'message' => 'Client id required.');
		else
		{
			$tblName = 'eot_contact';

			//--logic if default contact get true
			if ($conData['con_default']) 
			{
				$data = array('con_default' => 0);
				$condition = array('con_default' => 1);
				$this->CommonModel->updateData($tblName,$data,$condition);
			}

			$conId = $this->CommonModel->insertData($tblName,$conData);
			if($conId)
			{
				$contactResult = $this->getClientContactDetail($conId);
				$responseArray = array('success'=> true, 'message'=>'Contact added successfully.','data' => $contactResult['data']);
			}
			else
				$responseArray = array("success"=>false,'message'=>'Contact not added, please try again.');
		}
		return $responseArray;
	}

	public function getClientContactList($cltId,$limit,$index,$search)
	{
		$responseArray = array();

		//--check required params
		if (!$cltId) 
			$responseArray = array('success'=> false, 'message' => 'Client id required.', 'data'=>[]);
		else
		{
			$this->db->select('SQL_CALC_FOUND_ROWS con_id as conId,con_cltid as cltId,con_name as cnm,con_email as email,con_mobile1 as mob1,con_mobile2 as mob2,con_fax as fax,con_twitter as twitter,con_skype as skype,con_default as def',FALSE);
			$this->db->from('eot_contact');
			$this->db->where('con_cltid',$cltId);
			if ($search){
				$this->db->like('con_name',$search);
				$this->db->or_like('con_mobile1',$search);
			}
			$this->db->order_by('con_name','asc');
			if ($limit || $index)
				$this->db->limit($limit,$index);
			$contactResult = $this->db->get();

			//for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
			$total_count = $total_record->row();
			$total_count = $total_count->total_count;

			//--final result
			$responseArray = array("success"=>true,'data'=>$contactResult->result_array(),'count'=>$total_count);
		}
		return $responseArray;
	}

	public function getClientContactDetail($conId)
	{
		$responseArray = array();

		//--check required params
		if (!$conId) 
			$responseArray = array('success'=> false, 'message' => 'Contact id required.', 'data'=>[]);
		else
		{
			$fields = "con_id as conId,con_name as cnm,con_email as email,con_mobile1 as mob1,con_mobile2 as mob2,con_fax as fax,con_twitter as twitter,con_skype as skype,con_default as def,con_isactive as isactive";
			$tblName = 'eot_contact';
			$condition = array('con_id' => $conId);
			$contactResult = $this->CommonModel->getData($fields,$tblName,$condition);
			if($contactResult->num_rows())
				$responseArray = array('success'=> true, 'message'=>'Contact detail found.', 'data' => $contactResult->row());
			else
				$responseArray = array("success"=>false,'message'=>'Contact not get, please try again.');
		}
		return $responseArray; 
	}

	public function updateClientContact($conId,$conData)
	{
		$responseArray = array();

		//--check required params
		if (!$conId) 
			$responseArray = array('success'=> false, 'message' => 'Contact id required.', 'data'=>[]);
		else
		{
			$tblName = 'eot_contact';

			//--logic if default contact get true
			if ($conData['con_default']) 
			{
				$data = array('con_default' => 0);
				$condition = array('con_default' => 1);
				$this->CommonModel->updateData($tblName,$data,$condition);
			}

			$condition = array('con_id' => $conId);
			$result = $this->CommonModel->updateData($tblName,$conData,$condition);
			if($result)
			{
				$contactResult = $this->getClientContactDetail($conId);
				$responseArray = array('success'=> true, 'message'=>'Contact updated successfully.','data' => $contactResult['data']);
			}
			else
				$responseArray = array("success"=>false,'message'=>'Contact not update, please try again.', 'data'=>[]);
		}
		return $responseArray;
	}

	public function deleteClientContact($conId)
	{
		$responseArray = array();

		//--check required params
		if (!$conId) 
			$responseArray = array('success'=> false, 'message' => 'Contact id required.', 'data'=>[]);
		else
		{
			//--check contact record in job table
			$fields = 'job_conId';
			$tblName = 'eot_job';	
			$condition = array('job_conId' => $conId);
			$jobResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$jobResult = $jobResult->num_rows();
			if(empty($jobResult))
			{
				//--check contact is default or not
				$fields = 'con_id';
				$tblName = 'eot_contact';	
				$condition = array('con_id' => $conId,'con_default' => 1);
				$contactResult = $this->CommonModel->getData($fields,$tblName,$condition);
				if ($contactResult->num_rows()) 
				{
					$responseArray = array("success"=>false,'message'=> "This is default client contact.So, you can't delete this contact.", 'data'=>[]);
				}
				else
				{
					$tblName = 'eot_contact';
					$condition = array('con_id' => $conId);
					$result = $this->CommonModel->deleteData($tblName,$condition); 
					if($result)
						$responseArray = array('success'=> true, 'message'=>'Contact deleted successfully.', 'data'=>[]);
					else
						$responseArray = array("success"=>false,'message'=>'Contact not deleted, please try again.', 'data'=>[]);
				}
			}
			else
			{
				$responseArray = array("success"=>false,'message'=> "You can't delete this contact, because it is already used.", 'data'=>[]);
			}
		}
		return $responseArray;
	}

	public function addCompanyAccountType($accData)
	{
		$responseArray = array();

		//--check required params
		if (!$accData['acct_compid']) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$tblName = 'eot_comp_acct_info';
			$accId = $this->CommonModel->insertData($tblName,$accData);
			if($accId)
			{
				$result = $this->getCompanyAccountDetails($accId);
				$responseArray = array('success'=> true, 'message'=>'Company account type added successfully.', 'data' => $result['data']);
			}
			else
				$responseArray = array("success"=>false,'message'=>'Company account type not added, please try again.', 'data'=>[]);
		}
		return $responseArray;
	}

	public function getCompanyAccountTypeList($compId,$limit,$index,$search)
	{
		$responseArray = array();

		//--check required params
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$this->db->select('SQL_CALC_FOUND_ROWS acct_id as accId,acct_compid as compId,acct_type as type',FALSE);
			$this->db->from('eot_comp_acct_info');
			$this->db->where('acct_compid',$compId);
			if ($search)
				$this->db->like('acct_type',$search);
			if ($limit || $index)
				$this->db->limit($limit,$index);
			$accountTypeResult = $this->db->get();

			//for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
			$total_count = $total_record->row();
			$total_count = $total_count->total_count;

			//--final result
			$responseArray = array("success"=>true,'data'=>$accountTypeResult->result_array(),'count'=>$total_count);
		}
		return $responseArray;
	}

	public function getCompanyAccountDetails($accId)
	{
		$responseArray = array();

		//--check required params
		if (!$accId) 
			$responseArray = array('success'=> false, 'message' => 'Account id required.', 'data'=>[]);
		else
		{
			$fields = "acct_id as accId, acct_compid as compId,acct_type as type";
			$tblName = 'eot_comp_acct_info';
			$condition = array('acct_id' => $accId);
			$accountResult = $this->CommonModel->getData($fields,$tblName,$condition);
			if($accountResult->num_rows())
				$responseArray = array('success'=> true, 'message'=>[], 'data' => $accountResult->row());
			else
				$responseArray = array("success"=>false,'message'=>'Company account type not get, please try again.', 'data'=>[]);
		}
		return $responseArray; 
	}

	public function updateCompanyAccountType($accId,$accData)
	{
		$responseArray = array();

		//--check required params
		if (!$accId) 
			$responseArray = array('success'=> false, 'message' => 'Account id required.', 'data'=>[]);
		else
		{
			$tblName = 'eot_comp_acct_info';
			$condition = array('acct_id' => $accId);
			$result = $this->CommonModel->updateData($tblName,$accData,$condition);
			if($result)
				$responseArray = array('success'=> true, 'message'=>'Company account type updated successfully.', 'data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Company account type not update, please try again.', 'data'=>[]);
		}
		return $responseArray;
	}

	public function deleteCompanyAccountType($accId)
	{
		$responseArray = array();

		//--check required params
		if (!$accId) 
			$responseArray = array('success'=> false, 'message' => 'Account id required.', 'data'=>[]);
		else
		{
			//--check Company account type record in client table 
			$fields = 'clt_payment_type';
			$tblName = 'eot_client';	
			$condition = array('clt_payment_type' => $accId);
			$cltResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$cltResult = $cltResult->num_rows();
			if(empty($cltResult))
			{
				$tblName = 'eot_comp_acct_info';
				$condition = array('acct_id' => $accId);
				$result = $this->CommonModel->deleteData($tblName,$condition);
				if($result)
					$responseArray = array('success'=> true, 'message'=>'Company account type deleted successfully.', 'data'=>[]);
				else
				$responseArray = array("success"=>false,'message'=>'Company account type not deleted, please try again.', 'data'=>[]);
			}
			else
			{
				$responseArray = array("success"=>false,'message'=>"You can't delete this Company account type, because it is already used.", 'data'=>[]);
			}
		}
		return $responseArray;
	}

	public function getCompanySettingDetails($compId)
	{
		$responseArray = array();

		//--check required params
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$fields = "set_city as city,set_state as state,set_country as ctry,set_email as email,set_bcc as bcc,set_duration as duration";
			$tblName = 'eot_comp_setting';
			$condition = array('set_compid' => $compId);
			$settingResult = $this->CommonModel->getData($fields,$tblName,$condition);
			if($settingResult->num_rows())
				$responseArray = array('success'=> true, 'message'=>[], 'data' => $settingResult->row());
			else
				$responseArray = array("success"=>false,'message'=>'Company settings not get, please try again.', 'data'=>[]);
		}
		return $responseArray; 
	}

	public function updateCompanySetting($compId,$setData,$trueMsg,$falseMsg)
	{
		$responseArray = array();

		//--check required params
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$tblName = 'eot_comp_setting';
			$condition = array('set_compid' => $compId);
			$result = $this->CommonModel->updateData($tblName,$setData,$condition);
			if($result)
				$responseArray = array('success'=> true, 'message'=>$trueMsg, 'data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>$falseMsg, 'data'=>[]);
		}
		return $responseArray;
	}

	public function addCompanyTax($taxData)
	{
		$responseArray = array();

		//--check required params
		if (!$taxData['tax_compid']) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{ 
			$tblName = ' eot_tax';	
			$taxId = $this->CommonModel->insertData($tblName,$taxData);
			if ($taxId)
			{
				$taxResult = $this->getTaxDetail($taxId);
				$responseArray = array("success"=>true,'message'=>'Tax type added successfully.','data'=>$taxResult['data']);
			}
			else
				$responseArray = array("success"=>false,'message'=>'Tax type not added, please try again.', 'data'=>[]); 
		}
		return $responseArray;
	}

	public function getTaxList($compId,$limit,$index,$search)
	{
		$responseArray = array();

		//--check required params
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$this->db->select('SQL_CALC_FOUND_ROWS tax_id as taxId,tax_label as label,tax_isactive as isactive',FALSE);
			$this->db->from('eot_tax');
			$this->db->where('tax_compid',$compId);
			if ($search){
				$this->db->like('tax_label',$search);
			}
			if ($limit || $index)
				$this->db->limit($limit,$index);
			$taxResult = $this->db->get();

			//for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
			$total_count = $total_record->row();
			$total_count = $total_count->total_count;

			//--final result
			$responseArray = array("success"=>true,'data'=>$taxResult->result_array(),'count'=>$total_count);
		}
		return $responseArray;
	}

	public function getTaxDetail($taxId)
	{
		$responseArray = array();

		//--check required params
		if (!$taxId) 
			$responseArray = array('success'=> false, 'message' => 'Tax id required.', 'data'=>[]);
		else
		{
			$fields = 'tax_id as taxId,tax_label as label,tax_isactive as isactive';
			$tblName = 'eot_tax';	
			$condition = array('tax_id' => $taxId);
			$result = $this->CommonModel->getData($fields,$tblName,$condition);
			if ($result->num_rows()) 
				$responseArray = array("success"=>true, 'message'=>'Tax found.', 'data'=>$result->row());
			else
				$responseArray = array("success"=>false,'message'=>'Tax data not get, please try again.'); 
		}
		return $responseArray;
	}

	public function updateCompanyTax($taxId,$taxData)
	{
		$responseArray = array();

		//--check required params
		if (!$taxId) 
			$responseArray = array('success'=> false, 'message' => 'Tax id required.', 'data'=>[]);
		else
		{	
			$tblName = 'eot_tax';	
			$condition = array('tax_id' => $taxId);
			$updateResult = $this->CommonModel->updateData($tblName,$taxData,$condition);
			if ($updateResult) 
				$responseArray = array("success"=>true,'message'=>'Tax type updated successfully.', 'data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Tax type not updated, please try again.', 'data'=>[]); 
		}
		return $responseArray;
	}

	public function deleteTaxType($taxId)
	{
		$responseArray = array();

		//--check required params
		if (!$taxId) 
			$responseArray = array('success'=> false, 'message' => 'Tax id required.', 'data'=>[]);
		else
		{
			//--check tax record in title tax table
			$fields = 'ttmm_taxid';
			$tblName = 'eot_title_tax_mm';	
			$condition = array('ttmm_taxid' => $taxId);
			$titleResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$titleResult = $titleResult->num_rows();

			//--check tax record in item tax table
			$fields = 'itmm_taxid';
			$tblName = 'eot_item_tax_mm';	
			$condition = array('itmm_taxid' => $taxId);
			$taxResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$taxResult = $taxResult->num_rows();

			//--check tax record in job tax table
			$fields = 'ijtmm_taxid';
			$tblName = 'eot_item_job_tax_mm';	
			$condition = array('ijtmm_taxid' => $taxId);
			$jobResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$jobResult = $jobResult->num_rows();

			//--check tax record in quotation tax table
			$fields = 'iqtmm_taxid';
			$tblName = 'eot_item_quot_tax_mm';	
			$condition = array('iqtmm_taxid' => $taxId);
			$quotResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$quotResult = $quotResult->num_rows();

			if(empty($titleResult) && empty($taxResult) && empty($jobResult) && empty($quotResult))
			{
				$tblName = 'eot_tax';
				$condition = array('tax_id' => $taxId);
				$result = $this->CommonModel->deleteData($tblName,$condition);
				if($result)
					$responseArray = array("success"=>true,'message'=>'Tax type deleted successfully.');
				else
					$responseArray = array("success"=>false,'message'=>'Tax type not deleted, please try again.', 'data'=>[]);
			}
			else
			{
				$responseArray=	array("success"=>false,'message'=>
				"You can't delete this tax type, because it is already used.", "data" => []);
			}
		}
		return $responseArray;
	}

	public function updateCurrency($compId,$cur)
	{
		$responseArray = array();

		//--check required params
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.');
		else
		{
			$tblName = 'eot_comp_setting';
			$condition = array('set_compid' => $compId);
			$curData = array('set_currency' => $cur);
			$result = $this->CommonModel->updateData($tblName,$curData,$condition);
			if($result)
				$responseArray = array('success'=> true, 'message'=>'Currency updated successfully.');
			else
				$responseArray = array("success"=>false,'message'=>'Currency not update, please try again.');
		}
		return $responseArray;	
	}

	public function getCompanySettingByUser($compId)
	{
		$responseArray = array();

		//--check required params
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$fields = "set_city as city,set_state as state,set_country as ctry,set_email as email,set_bcc as bcc,set_duration as duration,set_shedule_time as schdlTime,set_capture_time as capTime";
			$tblName = 'eot_comp_setting';
			$condition = array('set_compid' => $compId);
			$settingResult = $this->CommonModel->getData($fields,$tblName,$condition);
			if($settingResult->num_rows())
				$responseArray = array('success'=> true, 'message'=>[], 'data' => $settingResult->row());
			else
				$responseArray = array("success"=>false,'message'=>'Company settings not get, please try again.', 'data'=>[]);
		}
		return $responseArray; 
	}
}
?>