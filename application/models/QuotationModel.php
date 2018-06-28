<?php
class QuotationModel extends CI_Model
{	
	function __construct()
	{
		parent::__construct();
	}
	
	public function addQuotation($quoteData,$quoteMembers,$clientForFuture,$siteForFuture,$contactForFuture,$cltData,$siteData,$conData)
	{
		$this->db->trans_begin();//--db transaction start
		$responseArray = array();

		//--check required params
		if (!$quoteData['quot_compid']) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else if (!$quoteData['quot_author']) 
			$responseArray = array('success'=> false, 'message' => 'Author id required.', 'data'=>[]);
		else if ($quoteData['quot_cltid'] && $quoteData['quot_client_name']) 
			$responseArray = array('success'=> false, 'message' => 'Only one required, client id or name.', 'data'=>[]);
		else if ($quoteData['quot_conid'] && $quoteData['quot_contact_name']) 
			$responseArray = array('success'=> false, 'message' => 'Only one required, contact id or name.', 'data'=>[]);
		else if ($quoteData['quot_siteid'] && $quoteData['quot_site_name']) 
			$responseArray = array('success'=> false, 'message' => 'Only one required, site id or name.', 'data'=>[]);
		else
		{
			//--if client save for future
			if ($clientForFuture) 
			{
				if (!$cltData['clt_name']) 
					return array('success'=> false, 'message' => 'Client name required.', 'data'=>[]);
				else
				{
					//--add client
					$tblName = 'eot_client';
					$quoteData['quot_cltid'] = $this->CommonModel->insertData($tblName,$cltData);
					$quoteData['quot_client_name'] = '';
					$siteData['site_default'] = 1;
					$conData['con_default'] = 1;
				}
			}

			//--if client sie save for future
			if ($siteForFuture) 
			{
				if (!$siteData['site_name']) 
					return array('success'=> false, 'message' => 'Client site name required.', 'data'=>[]);
				else if (!$siteData['site_address']) 
					return array('success'=> false, 'message' => 'Address required.', 'data'=>[]);
				else
				{
					//--Get latitude and longitude from address for client site
					$LatLongResult = $this->CommonModel->getLatLong($siteData['site_address']);

					//--add client site
					$tblName = 'eot_site';
					$siteData['site_cltid'] = $quoteData['quot_cltid'];				
					$siteData['site_lat'] = $LatLongResult['latitude'];
					$siteData['site_long'] = $LatLongResult['longitude'];
				   	$quoteData['quot_siteid'] = $this->CommonModel->insertData($tblName,$siteData);
				   	$quoteData['quot_contact_name'] = '';
				}
			}

			//--if client contact save for future
			if ($contactForFuture) 
			{
				if (!$conData['con_name']) 
					return array('success'=> false, 'message' => 'Client contact name required.', 'data'=>[]);
				else
				{
					//--add client contact
					$tblName = 'eot_contact';
					$conData['con_cltid'] = $quoteData['quot_cltid'];
					$quoteData['quot_conid'] = $this->CommonModel->insertData($tblName,$conData);
					$quoteData['quot_site_name'] = '';
				} 
			}

			//--insert quotation data
			$tblName = 'eot_quotation';
			$quotId = $this->CommonModel->insertData($tblName,$quoteData);
			if ($quotId)
			{
				//--update quotation count
				$tblName = 'eot_company';	
				$compData = array('comp_job_count' => $autoId);
				$condition = array('comp_id' => $quoteData['quot_compid']);
				$this->CommonModel->updateData($tblName,$compData,$condition);

				//--make return data array
				$data['quotId'] = $quotId;
				$responseArray = array('success'=> true, 'message'=>'Quotation added successfully.', 'data'=>$data);
			}
			else
			{
				$responseArray = array("success"=>false,'message'=>'Quotation not added, please try again.', 'data'=>[]);
			}
		}

		//--use db tansaction
		if($this->db->trans_status() === FALSE){
		    $this->db->trans_rollback();
		    $responseArray = array("success" => false, "message" => 'Something went wrong, please try again.', "data" => '');
		}
		else{
		    $this->db->trans_commit();
		}
		return $responseArray;
	}


	public function getAdminQuoteList($compId,$limit,$index,$search)
	{
		$responseArray = array();

		//--check required params
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$data = array();
			$quotSql = "SELECT SQL_CALC_FOUND_ROWS quot_id as quotId, quot_label as label,quot_description as des,quot_status as status, quot_address as adr,CASE WHEN quot_client_name !='' THEN quot_client_name ELSE clt_name END as nm,CASE WHEN quot_site_name !='' THEN quot_site_name ELSE site_name END as snm FROM 
				(
					SELECT 
						quo.quot_id,quo.quot_label,quo.quot_description,quo.quot_status,quo.quot_address,quo.quot_client_name,quo.quot_site_name,clt.clt_name,site.site_name
					FROM eot_quotation as quo
					LEFT JOIN eot_client as clt ON clt.clt_id = quo.quot_cltid
					LEFT JOIN eot_site as site ON site.site_id = quo.quot_siteid
					AND quot_compid = '".$compId."'";
					if ($search){
						$quotSql = $quotSql . " AND (quot_label LIKE '%".$search."%' OR quot_description LIKE '%".$search."%')";
					}
					$quotSql = $quotSql . " GROUP BY quot_id ORDER BY quot_id DESC";
					$quotSql = $quotSql . "
				) as quotTbl";
			if ($limit || $index){
				$quotSql = $quotSql . " LIMIT ".$index." , ".$limit;
			}
			$quotResult = $this->db->query($quotSql);
			//--for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
			$total_count = $total_record->row();
			$total_count = $total_count->total_count;

			if ($quotResult->num_rows()) 
			{
				$data = $quotResult->result_array();
				$responseArray = array("success"=>true,'message'=>'','data'=>$data,'count'=>$total_count);
			}
			else
			{
				$responseArray = array("success"=>true,'message'=>'','data'=>$data,'count'=>$total_count);
			}			
		}
		return $responseArray;
	}



	public function getQuoteDetail($quotId = NULL)
	{
		$responseArray = array();
		//--check required params
		if ($quotId == NULL) 
			$responseArray = array('success'=> false, 'message' => 'Quotation id required.', 'data'=>[]);
		else
		{
			$this->db->select("quot.quot_id as quotId,quot.quot_cltid as cltId,quot.quot_siteid as siteId,quot.quot_conid as conId,quot.quot_label as label,quot.quot_description as des,quot.quot_status as status,quot.quot_author as athr, quot.quot_instruction as inst,CASE WHEN quot.quot_client_name !='' THEN quot.quot_client_name ELSE (SELECT clt_name FROM eot_client JOIN eot_quotation ON eot_quotation.quot_cltid = eot_client.clt_id WHERE quot_id = '".$quotId."') END as nm,quot.quot_contact_name as cnm,quot.quot_site_name as snm,quot.quot_email as email,quot.quot_mobile1 as mob1,quot.quot_mobile2 as mob2,quot.quot_address as adr,quot.quot_city as city,quot.quot_state as state,quot.quot_country as ctry,quot.quot_zipcode as zip,quot.quot_createdate as createDate,quot.quot_updatedate as updateDate");
			$this->db->from('eot_quotation as quot');
			$this->db->where('quot_id',$quotId);
			$quotResult = $this->db->get();	
			if ($quotResult->num_rows())
			{ 
				$quotResult = $quotResult->row();
				//--get and merge site data
				if ($quotResult->siteId) 
				{
					$fields = 'site_name as snm,site_address as adr,site_city as city,site_state as state,site_country as ctry,site_zipcode as zip';
					$tblName = 'eot_site';	
					$condition = array('site_id' => $quotResult->siteId);
					$siteResult = $this->CommonModel->getData($fields,$tblName,$condition);
					$sitaData = $siteResult->row();
					//--merge data
					$quotResult->snm = $sitaData->snm;
				}

				//--get and merge contact data
				if ($quotResult->conId) 
				{
					$fields = 'con_name as cnm,con_email as email,con_mobile1 as mob1,con_mobile2 as mob2';
					$tblName = 'eot_contact';	
					$condition = array('con_id' => $quotResult->conId);
					$contactResult = $this->CommonModel->getData($fields,$tblName,$condition);
					$contactData = $contactResult->row();
					//--merge data
					$quotResult->cnm = $contactData->cnm;
					$quotResult->email = $contactData->email;
					$quotResult->mob1 = $contactData->mob1;
					$quotResult->mob2 = $contactData->mob2;
				}
				$responseArray = array("success"=>true,'message'=>'Quotation data found.','data'=>$quotResult);
			}
			else
				$responseArray = array("success"=>false,'message'=>'Quotation data not found, please try again.','data'=>[]);
		}
		return $responseArray;
	}


	public function changeQuotStatus($quotId,$status,$dateTime)
	{
		$responseArray = array();
		//--check required params
		if (!$quotId) 
			$responseArray = array('success'=> false, 'message' => 'Quotation id required.', 'data'=>[]);
		else if (!$status) 
			$responseArray = array('success'=> false, 'message' => 'Status required.', 'data'=>[]);
		else if (!$dateTime) 
			$responseArray = array('success'=> false, 'message' => 'Date time required.', 'data'=>[]);
		else
		{	
			
			//--update quotation
			$tblName = 'eot_quotation';
			$quotData['quot_status'] = $status;
			$quotData['quot_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
			$condition = array('quot_id' => $quotId);
			$updateResult = $this->CommonModel->updateData($tblName,$quotData,$condition);

			if ($updateResult) 
				$responseArray = array("success"=>true,'message'=>'Quotation status changed successfully.', 'data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Quotation status not changed, please try again.', 'data'=>[]); 
		}
		return $responseArray;
	}


	public function updateQuotation($quotId,$compId,$quoteData,$quoteMembers,$siteForFuture,$contactForFuture,$siteData,$conData)
	{
		$this->db->trans_begin();//--db transaction start
		$responseArray = array();

		//--check required params
		if (!$quotId) 
			$responseArray = array('success'=> false, 'message' => 'Quotation id required.', 'data'=>[]);
		else if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{	
			//--insert or update client site
			if ($siteForFuture)
			{
				if ($quoteData['quot_siteid']) 
				{
					if (!$siteData['site_name']) 
						return array('success'=> false, 'message' => 'Client site name required.', 'data'=>[]);
					else if (!$siteData['site_address']) 
						return array('success'=> false, 'message' => 'Address required.', 'data'=>[]);
					else
					{
						//--Get latitude and longitude from address for client site
						$LatLongResult = $this->CommonModel->getLatLong($siteData['site_address']);

						//--update client site
						$tblName = 'eot_site';	
						$condition = array('site_id' => $quoteData['quot_siteid']);
						$siteData['site_cltid'] = $quoteData['quot_cltid'];				
						$siteData['site_lat'] = $LatLongResult['latitude'];
						$siteData['site_long'] = $LatLongResult['longitude'];
						$this->CommonModel->updateData($tblName,$siteData,$condition);
					}
				}
				else
				{
					if (!$siteData['site_name']) 
						return array('success'=> false, 'message' => 'Client site name required.', 'data'=>[]);
					else if (!$siteData['site_address']) 
						return array('success'=> false, 'message' => 'Address required.', 'data'=>[]);
					else
					{
						//--Get latitude and longitude from address for client site
						$LatLongResult = $this->CommonModel->getLatLong($siteData['site_address']);

						//--add client site
						$tblName = 'eot_site';
						$siteData['site_cltid'] = $quoteData['quot_cltid'];				
						$siteData['site_lat'] = $LatLongResult['latitude'];
						$siteData['site_long'] = $LatLongResult['longitude'];
						$siteData['site_isactive'] = 1;
						$siteData['site_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
					   	$quoteData['quot_siteid'] = $this->CommonModel->insertData($tblName,$siteData);
					}
				}
				$quoteData['quot_site_name'] = '';
			}

			//--insertor update client contact
			if ($contactForFuture)
			{
				if ($quoteData['quot_conid']) 
				{
					if (!$conData['con_name']) 
						return array('success'=> false, 'message' => 'Client contact name required.', 'data'=>[]);
					else if (!$conData['con_email']) 
						return array('success'=> false, 'message' => 'Client contact email required.', 'data'=>[]);
					else
					{
						//--add client contact
						$tblName = 'eot_contact';	
						$condition = array('con_id' => $quoteData['quot_conid']);
						$conData = array('con_cltid' =>$quoteData['quot_cltid']);
						$this->CommonModel->updateData($tblName,$conData,$condition);
					}
				}
				else
				{
					if (!$conData['con_name']) 
						return array('success'=> false, 'message' => 'Client contact name required.', 'data'=>[]);
					else if (!$conData['con_email']) 
						return array('success'=> false, 'message' => 'Client contact email required.', 'data'=>[]);
					else
					{
						//--add client contact
						$tblName = 'eot_contact';
						$conData['con_cltid'] = $quoteData['quot_cltid'];
						$conData['con_isactive'] = 1;
						$conData['con_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
						$quoteData['quot_conid'] = $this->CommonModel->insertData($tblName,$conData);
					} 
				}
				$quoteData['quot_contact_name'] = '';
			}

			//--logic for change status
			$fields = 'quot_status';
			$tblName = 'eot_quotation';	
			$condition = array('quot_id' => $quotId);
			$quotResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$quotResult = $quotResult->row();
			if ($quotResult->quot_status != $quoteData['quot_status']) 
			{
				$this->changeQuotStatus($quotId,$quoteData['quot_status'],strtotime(gmdate('Y-m-d h:i:s a').' UTC'));
			}

			//--update quot data
			$tblName = 'eot_quotation';
			$condition = array('quot_id' => $quotId);
			$result = $this->CommonModel->updateData($tblName,$quoteData,$condition);
			if ($result)
			{
			    $data = array();
			    $responseArray = array('success'=> true, 'message'=>'Quotation update successfully.', 'data'=>$data);
			}
			else
				$responseArray = array("success"=>false,'message'=>'Quotation not update, please try again.', 'data'=>[]);
		}
		//--use db tansaction
		if($this->db->trans_status() === FALSE){
		    $this->db->trans_rollback();
		    $responseArray = array("success" => false, "message" => 'Something went wrong, please try again.', "data" => '');
		}
		else{
		    $this->db->trans_commit();
		}
		return $responseArray;
	}



	public function deleteQuote($quotId)
	{
		$responseArray = array();
		//--check required params
		if (!$quotId) 
			$responseArray = array('success'=> false, 'message' => 'Quotation id required.', 'data'=>[]);
		else
		{	
			//--soft delete
			$tblName = 'eot_quotation';	
			$jobData = array('quot_isactive' => 0);
			$condition = array('quot_id' => $quotId);
			$result = $this->CommonModel->updateData($tblName,$jobData,$condition);
			if ($result) 
				$responseArray = array("success"=>true,'message'=>'Job deleted successfully.', 'data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Job not deleted, please try again.', 'data'=>[]); 
		}
		return $responseArray;
	}




	public function sendEmail($email,$subject,$message,$fileName)
	{

	       $config = Array(
		      'protocol' => 'smtp',
		      'smtp_host' => 'ssl://smtp.googlemail.com',
		      'smtp_port' => 465,
		      'smtp_user' => 'rawat.hemant27@gmail.com', 
		      'smtp_pass' => PASSWORD, 
		      'mailtype' => 'html',
		      'charset' => 'iso-8859-1',
		      'wordwrap' => TRUE
	      );

	      $this->load->library('email', $config);
	      $this->email->set_newline("\r\n");
	      $this->email->from('rawat.hemant27@gmail.com', 'Hemant Rawat');
	      $this->email->to($email);
	      $this->email->subject($subject);
	      $this->email->message($message);
	      $this->email->attach('./uploads/'.$fileName);

	      if($this->email->send())
		  {

		  	 $file = FCPATH . 'uploads/' . $fileName;
             if (file_exists($file)) {
                unlink($file);
                $responseArray = array("success"=>true,'message'=>'Mail send successfully.',); 
             }

		  }
		  else
		  {
		       $responseArray = array("success"=>false,'message'=>$this->email->print_debugger(), 'data'=>[]);
		  }

	    return $responseArray;

	}


	
}
?>