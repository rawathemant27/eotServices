<?php
class JobModel extends CI_Model
{	
	function __construct()
	{
		parent::__construct();
	}

	public function addJobTitle($jobTitleData,$taxData)
	{
		$responseArray = array();

		//--check required params
		if (!$jobTitleData['jt_compid']) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{ 
			$tblName = 'eot_job_title';	
			$jtId = $this->CommonModel->insertData($tblName,$jobTitleData);
			if ($jtId)
			{
				//--insert data in job title tax mappin
                foreach ($taxData as $key) 
            	{
                	$data['ttmm_taxid'] = $key->taxId;
                    $data['ttmm_jtid'] = $jtId;
                    $data['ttmm_tax_rate'] = $key->rate;
					$tblName = 'eot_title_tax_mm';	
					$this->CommonModel->insertData($tblName,$data);
                }

				$jobTitleId=$this->getJobTitleDetail($jtId);
				$responseArray = array("success"=>true,'message'=>'Job title added successfully.','data'=>$jobTitleId['data']);
			}
			else
				$responseArray = array("success"=>false,'message'=>'Job title not added, please try again.', 'data'=>[]); 
		}
		return $responseArray;
	}

	public function getJobTitleList($compId,$limit,$index,$search)
	{
		$responseArray = array();

		//--check required params
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$this->db->select('SQL_CALC_FOUND_ROWS jt_id as jtId,jt_title as title,jt_description as des,jt_labour_rate as labour',FALSE);
			$this->db->from('eot_job_title');
			$this->db->where('jt_compid',$compId);
			if ($search)
				$this->db->like('jt_title',$search);

			if ($limit || $index)
				$this->db->limit($limit,$index);

			$this->db->order_by('jtId','desc');
			$jobTitleResult = $this->db->get();
			if ($jobTitleResult->num_rows()) 
			{
				$data = $jobTitleResult->result_array();

				//for pagignation
				$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
				$total_count = $total_record->row();
				$total_count = $total_count->total_count;

				//--for get title tax
				$i = 0;
				foreach ($jobTitleResult->result_array() as $key) 
				{
					$fields = 'ttmm_taxid as taxId,ttmm_tax_rate as rate';
					$tblName = 'eot_title_tax_mm';	
					$condition = array('ttmm_jtid' => $key['jtId']);
					$result = $this->CommonModel->getData($fields,$tblName,$condition);
					$data[$i]['taxData'] = $result->result_array();
					$i++;
				}
				$responseArray = array("success"=>true,'message'=>'','data'=>$data,'count'=>$total_count);
			}
			else
			{
				$responseArray = array("success"=>true,'message'=>'','data'=>[],'count'=>0);
			}			
		}
		return $responseArray;
	}

	public function getJobTitleDetail($jtId)
	{
		$responseArray = array();

		//--check required params
		if (!$jtId) 
			$responseArray = array('success'=> false, 'message' => 'Job title id required.', 'data'=>[]);
		else
		{
			$fields = 'jt_id as jtId,jt_title as title,jt_description as des,jt_labour_rate as labour';
			$tblName = 'eot_job_title';	
			$condition = array('jt_id' => $jtId);
			$result = $this->CommonModel->getData($fields,$tblName,$condition);
			if ($result->num_rows())
			{ 
				$data = $result->row();

				//--get title tax
				$fields = 'ttmm_taxid as taxId,ttmm_tax_rate as rate';
				$tblName = 'eot_title_tax_mm';	
				$condition = array('ttmm_jtid' => $jtId);
				$result = $this->CommonModel->getData($fields,$tblName,$condition);
				$data->taxData = $result->result_array();

				$responseArray = array("success"=>true, 'message'=>'Job title found.', 'data'=>$data);
			}
			else
				$responseArray = array("success"=>false,'message'=>'Job title data not get, please try again.', 'data'=>[]); 
		}
		return $responseArray;
	}

	public function updateJobTitle($jtId,$jobTitleData,$taxData)
	{
		$responseArray = array();

		//--check required params
		if (!$jtId) 
			$responseArray = array('success'=> false, 'message' => 'Job title id required.', 'data'=>[]);
		else
		{	
			$tblName = 'eot_job_title';	
			$condition = array('jt_id' => $jtId);
			$updateResult = $this->CommonModel->updateData($tblName,$jobTitleData,$condition);
			if ($updateResult) 
			{
				//--first delete previous title tax
				$tblName = 'eot_title_tax_mm';
				$condition = array('ttmm_jtid' => $jtId);
				$this->CommonModel->deleteData($tblName,$condition);

				//--insert data in job title tax mappin
                foreach ($taxData as $key) 
            	{
                	$data['ttmm_taxid'] = $key->taxId;
                    $data['ttmm_jtid'] = $jtId;
                    $data['ttmm_tax_rate'] = $key->rate;
					$tblName = 'eot_title_tax_mm';	
					$this->CommonModel->insertData($tblName,$data);
                }

                $responseArray = array("success"=>true,'message'=>'Job title updated successfully.', 'data'=>[]);
			}
			else
				$responseArray = array("success"=>false,'message'=>'Job title not updated, please try again.', 'data'=>[]); 
		}
		return $responseArray;
	}

	public function deleteJobTitle($jtId)
	{
		$responseArray = array();

		//--check required params
		if (!$jtId) 
			$responseArray = array('success'=> false, 'message' => 'Job title id required.', 'data'=>[]);
		else
		{
			//--check job title record in mapping table
			$fields = 'job_id';
			$tblName = 'eot_job';	
			$condition = array('job_jtid' => $jtId);
			$jobResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$jobResult = $jobResult->num_rows();
			if(empty($jobResult))
			{
				$tblName = 'eot_title_tax_mm';
				$condition = array('ttmm_jtid' => $jtId);
				$this->CommonModel->deleteData($tblName,$condition);

				$tblName = 'eot_job_title';
				$condition = array('jt_id' => $jtId);
				$result = $this->CommonModel->deleteData($tblName,$condition);
				if($result)
					$responseArray = array("success"=>true,'message'=>'Job title deleted successfully.', 'data'=>[]);
				else
					$responseArray = array("success"=>false,'message'=>'Job title not deleted, please try again.', 'data'=>[]);
			}
			else
			{
				$responseArray=	array("success"=>false,'message'=>
					"You can't delete this job title, because it is already used.", 'data'=>[]);
			}
		}
		return $responseArray;
	}
	
	public function addJob($jobData,$jobMembers,$clientForFuture,$siteForFuture,$contactForFuture,$cltData,$siteData,$conData,$tagData)
	{
		$this->db->trans_begin();//--db transaction start
		$responseArray = array();

		//--check required params
		if (!$jobData['job_compid']) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else if (!$jobData['job_jtid']) 
			$responseArray = array('success'=> false, 'message' => 'Job title id required.', 'data'=>[]);
		else if (!$jobData['job_type']) 
			$responseArray = array('success'=> false, 'message' => 'Job type required.', 'data'=>[]);
		else if (!$jobData['job_author']) 
			$responseArray = array('success'=> false, 'message' => 'Author id required.', 'data'=>[]);
		else if ($jobData['job_cltid'] && $jobData['job_client_name']) 
			$responseArray = array('success'=> false, 'message' => 'Only one required, client id or name.', 'data'=>[]);
		else if ($jobData['job_conid'] && $jobData['job_contact_name']) 
			$responseArray = array('success'=> false, 'message' => 'Only one required, contact id or name.', 'data'=>[]);
		else if ($jobData['job_siteid'] && $jobData['job_site_name']) 
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
					$jobData['job_cltid'] = $this->CommonModel->insertData($tblName,$cltData);
					$jobData['job_client_name'] = '';
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
					$siteData['site_cltid'] = $jobData['job_cltid'];				
					$siteData['site_lat'] = $LatLongResult['latitude'];
					$siteData['site_long'] = $LatLongResult['longitude'];
				   	$jobData['job_siteid'] = $this->CommonModel->insertData($tblName,$siteData);
				   	$jobData['job_contact_name'] = '';
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
					$conData['con_cltid'] = $jobData['job_cltid'];
					$jobData['job_conid'] = $this->CommonModel->insertData($tblName,$conData);
					$jobData['job_site_name'] = '';
				} 
			}

			//get company name
			$fields = 'comp_name,comp_job_count';
			$tblName = 'eot_company';	
			$condition = array('comp_id' => $jobData['job_compid']);
			$compResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$compResult = $compResult->row();

			//--make lablel of job
			$compName = substr($compResult->comp_name,0,3);
            $autoId = $compResult->comp_job_count+1;
            $jobData['job_label'] = $compName.$autoId;

            //--make label for sub job
            if ($jobData['job_parent_jobid'])
            {
            	//get parent job label
				$fields = 'job_label';
				$tblName = 'eot_job';	
				$condition = array('job_id' => $jobData['job_parent_jobid']);
				$jobResult = $this->CommonModel->getData($fields,$tblName,$condition);
				$jobResult = $jobResult->row();
				$jobData['job_label'] = 'T-'.$jobResult->job_label;
            }

            //--Get latitude and longitude from address
			$LatLongResult = $this->CommonModel->getLatLong($jobData['job_address']);
			$jobData['job_lat'] = $LatLongResult['latitude'];
			$jobData['job_long'] = $LatLongResult['longitude'];

			//--insert job data
			$tblName = 'eot_job';
			$jobId = $this->CommonModel->insertData($tblName,$jobData);
			if ($jobId)
			{
				//--if job type is 2 (Multiple)
				if ($jobData['job_type'] == 2) 
				{
					//--merge author and keeper in member array
					//array_push($jobMembers, $jobData['job_author']);
					//array_push($jobMembers, $jobData['job_keeper']);
					
					//-- code for insert job members
		            $jobMembers = array_unique($jobMembers);
		            foreach ($jobMembers as $key) 
		            {
		                $tblName = 'eot_job_member';
		                $jobMemData['jm_usrid'] = $key;
		                $jobMemData['jm_jobid'] = $jobId;
		                $jobMemData['jm_status'] = 1;
		                $jobMemData['jm_mem_isactive'] = 1;
						$this->CommonModel->insertData($tblName,$jobMemData);
		            }
				}

				//--update job count
				$tblName = 'eot_company';	
				$compData = array('comp_job_count' => $autoId);
				$condition = array('comp_id' => $jobData['job_compid']);
				$this->CommonModel->updateData($tblName,$compData,$condition);

				//--add tag if get
				if($tagData) 
				{				
					foreach ($tagData as $tag) 
			        {
			            //--if get tag id
			            if($tag->tagId)  
			            {
			                $tblName = 'eot_tag_mm';
							$mmData['tmm_tagid'] = $tag->tagId;
							$mmData['tmm_jobid'] = $jobId;
							$this->CommonModel->insertData($tblName,$mmData);
			            }
			            else
			            {
							$tblName = 'eot_tag';
							$data['tag_name'] = $tag->tnm;
							$data['tag_compid'] = $jobData['job_compid'];
							$tagId = $this->CommonModel->insertData($tblName,$data);
							if ($tagId) 
							{
								$tblName = 'eot_tag_mm';
								$mmData['tmm_tagid'] = $tagId;
								$mmData['tmm_jobid'] = $jobId;
								$this->CommonModel->insertData($tblName,$mmData);
							}
			            }
			        }
			    }

				//--make return data array
				$data['jobId'] = $jobId;
				$data['schdlStart'] = $jobData['job_shedule_start'];
				$data['schdlFinish'] = $jobData['job_shedule_finish'];
				$data['label'] = $jobData['job_label'];
				$data['tagData'] = $tagResult = $this->getJobTags($jobId);
				$responseArray = array('success'=> true, 'message'=>'Job added successfully.', 'data'=>$data);
			}
			else
			{
				$responseArray = array("success"=>false,'message'=>'Job not added, please try again.', 'data'=>[]);
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

	public function getUserJobList($usrId,$limit,$index,$search,$dateTime)
	{
		$responseArray = array();

		//--check required params
		if (!$usrId) 
			$responseArray = array('success'=> false, 'message' => 'User id required.', 'data'=>[]);
		else
		{
			$fields = "job.job_id as jobId,job.job_parent_jobid as parentId,job.job_cltid as cltId,job.job_siteid as siteId,job.job_conid as conId,job.job_quotid as quotId,job.job_label as label,job.job_description as des,job.job_type as type,job.job_priority as prty,job.job_author as athr,job.job_keeper as kpr,job.job_shedule_start as schdlStart,job.job_shedule_finish as schdlFinish,job.job_instruction as inst,job.job_client_name as nm,job.job_contact_name as cnm,job.job_site_name as snm,job.job_email as email,job.job_mobile1 as mob1,job.job_mobile2 as mob2,job.job_address as adr,job.job_city as city,job.job_state as state,job.job_country as ctry,job.job_zipcode as zip,job.job_lat as lat,job.job_long as lng,jt.jt_id as jtId,jt.jt_title as title,jt.jt_labour_rate as labour,job.job_createdate as createDate,job.job_updatedate as updateDate";
			$jobSql = "SELECT SQL_CALC_FOUND_ROWS tbl.* FROM
			(
				(
					SELECT 
						$fields, job.job_status as status
					FROM 
						eot_job as job JOIN eot_job_title as jt ON jt.jt_id = job.job_jtid 
					WHERE 
						job.job_type = '1' AND job_isactive = '1' AND job_isdelete = '1' AND (job.job_author = '".$usrId."' OR job.job_keeper = '".$usrId."')";

						if ($dateTime) {
							$jobSql = $jobSql . " AND job_updatedate >= '".$dateTime."'";
						}
				$jobSql = $jobSql . "
				) 
				UNION
				(
					SELECT 
						$fields,jm.jm_status as status
					FROM 
						eot_job as job JOIN eot_job_title as jt ON jt.jt_id = job.job_jtid JOIN eot_job_member as jm ON jm.jm_jobid = job.job_id  
					WHERE 
						job.job_type = '2' AND job_isactive = '1' AND job_isdelete = '1' AND jm.jm_usrid = '".$usrId."'";
						if ($dateTime) {
							$jobSql = $jobSql . " AND job_updatedate >= '".$dateTime."'";
						}
				$jobSql = $jobSql . "
				)
			)
			AS tbl ORDER BY tbl.updateDate DESC";
			if ($limit || $index)
				$jobSql = $jobSql . " LIMIT ".$index." , ".$limit;
			$jobResult = $this->db->query($jobSql);
			
			//--for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
			$total_count = $total_record->row();
			$total_count = $total_count->total_count;

			//--final result
			$responseArray = array("success"=>true,'message'=>'','data'=>$jobResult->result_array(),'count'=>$total_count);
		}
		return $responseArray;
	}

	public function getAdminJobList($compId,$limit,$index,$search)
	{
		$responseArray = array();

		//--check required params
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$data = array();
			$jobSql = "SELECT SQL_CALC_FOUND_ROWS job_id as jobId,job_parent_jobid as parentId,job_label as label,job_description as des,job_type as type,job_status as status,job_priority as prty,job_shedule_start as schdlStart,job_shedule_finish as schdlFinish,job_address as adr,jt_id as jtId,jt_title as title,inv_id as invId,CASE WHEN job_client_name !='' THEN job_client_name ELSE clt_name END as nm,CASE WHEN job_site_name !='' THEN job_site_name ELSE site_name END as snm FROM 
				(
					SELECT 
						job.job_id,job.job_parent_jobid,job.job_label,job.job_description,job.job_type,job.job_status,job.job_priority,job.job_shedule_start, job.job_shedule_finish,job.job_address,jt.jt_id,jt.jt_title,job.job_client_name,job.job_site_name,clt.clt_name,site.site_name,inv.inv_id
					FROM eot_job as job
					JOIN eot_job_title as jt ON jt.jt_id = job.job_jtid
					LEFT JOIN eot_client as clt ON clt.clt_id = job.job_cltid
					LEFT JOIN eot_site as site ON site.site_id = job.job_siteid
					LEFT JOIN eot_invoice as inv ON inv.inv_jobid = job.job_id
					LEFT JOIN eot_tag_mm as mm ON mm.tmm_jobid = job.job_id
					LEFT JOIN eot_tag as tag ON tag.tag_id = mm.tmm_tagid 
					WHERE job_isdelete = '1' AND job_compid = '".$compId."'";
					if ($search){
						$jobSql = $jobSql . " AND (job_label LIKE '%".$search."%' OR job_description LIKE '%".$search."%' OR tag_name LIKE '%".$search."%')";
					}
					$jobSql = $jobSql . " GROUP BY job_id ORDER BY job_id DESC";
					$jobSql = $jobSql . "
				) as jobTbl";
			if ($limit || $index){
				$jobSql = $jobSql . " LIMIT ".$index." , ".$limit;
			}
			$jobResult = $this->db->query($jobSql);

			//--for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
			$total_count = $total_record->row();
			$total_count = $total_count->total_count;

			if ($jobResult->num_rows()) 
			{
				$data = $jobResult->result_array();
				
				//--get tag data
				$i = 0;
				foreach ($data as $key) 
				{
					$data[$i]['tagData'] = $this->getJobTags($key['jobId']);
					$i++;
				}
				$responseArray = array("success"=>true,'message'=>'','data'=>$data,'count'=>$total_count);
			}
			else
			{
				$responseArray = array("success"=>true,'message'=>'','data'=>$data,'count'=>$total_count);
			}			
		}
		return $responseArray;
	}

	public function getJobDetail($jobId)
	{
		$responseArray = array();

		//--check required params
		if (!$jobId) 
			$responseArray = array('success'=> false, 'message' => 'Job id required.', 'data'=>[]);
		else
		{
			$this->db->select("job.job_id as jobId,job.job_parent_jobid as parentId,job.job_cltid as cltId,job.job_siteid as siteId,job.job_conid as conId,job.job_quotid as quotId,job.job_label as label,job.job_description as des,job.job_type as type,job.job_priority as prty,job.job_status as status,job.job_keeper as kpr,job.job_author as athr,job.job_shedule_start as schdlStart,job.job_shedule_finish as schdlFinish,job.job_instruction as inst,CASE WHEN job.job_client_name !='' THEN job.job_client_name ELSE (SELECT clt_name FROM eot_client JOIN eot_job ON eot_job.job_cltid = eot_client.clt_id WHERE job_id = '".$jobId."') END as nm,job.job_contact_name as cnm,job.job_site_name as snm,job.job_email as email,job.job_mobile1 as mob1,job.job_mobile2 as mob2,job.job_address as adr,job.job_city as city,job.job_state as state,job.job_country as ctry,job.job_zipcode as zip,job.job_createdate as createDate,job.job_updatedate as updateDate,jt.jt_id as jtId,jt.jt_title as title");
			$this->db->from('eot_job as job');
			$this->db->join('eot_job_title as jt','jt.jt_id = job.job_jtid');
			$this->db->where('job_id',$jobId);
			$jobResult = $this->db->get();	
			if ($jobResult->num_rows())
			{ 
				$jobResult = $jobResult->row();

				//--get and merge site data
				if ($jobResult->siteId) 
				{
					$fields = 'site_name as snm,site_address as adr,site_city as city,site_state as state,site_country as ctry,site_zipcode as zip';
					$tblName = 'eot_site';	
					$condition = array('site_id' => $jobResult->siteId);
					$siteResult = $this->CommonModel->getData($fields,$tblName,$condition);
					$sitaData = $siteResult->row();

					//--merge data
					$jobResult->snm = $sitaData->snm;
					// $jobResult->adr = $sitaData->adr;
					// $jobResult->city = $sitaData->city;
					// $jobResult->state = $sitaData->state;
					// $jobResult->ctry = $sitaData->ctry;
					// $jobResult->zip = $sitaData->zip;
				}

				//--get and merge contact data
				if ($jobResult->conId) 
				{
					$fields = 'con_name as cnm,con_email as email,con_mobile1 as mob1,con_mobile2 as mob2';
					$tblName = 'eot_contact';	
					$condition = array('con_id' => $jobResult->conId);
					$contactResult = $this->CommonModel->getData($fields,$tblName,$condition);
					$contactData = $contactResult->row();
					
					//--merge data
					$jobResult->cnm = $contactData->cnm;
					$jobResult->email = $contactData->email;
					$jobResult->mob1 = $contactData->mob1;
					$jobResult->mob2 = $contactData->mob2;
				}

				//--logic for make keeper array
				if ($jobResult->type == 1) 
				{
					$keeperArray[0]['usrId'] = $jobResult->kpr;
					$jobResult->keeper = $keeperArray;
				}
				else
				{
					$fields = 'jm_usrid as usrId';
					$tblName = 'eot_job_member';	
					$condition = array('jm_jobid' => $jobId);
					$memResult = $this->CommonModel->getData($fields,$tblName,$condition);
					$jobResult->keeper = $memResult->result_array();
				}

				//--get tags of a job
				$jobResult->tagData = $this->getJobTags($jobId);
				$responseArray = array("success"=>true,'message'=>'Job data found.','data'=>$jobResult);
			}
			else
				$responseArray = array("success"=>false,'message'=>'Job data not found, please try again.','data'=>[]);
		}
		return $responseArray;
	}

	//--get tags of a job
	public function getJobTags($jobId)
	{
		$this->db->select('tag.tag_id as tagId,tag.tag_name as tnm');
		$this->db->from('eot_tag_mm as mm');
		$this->db->join('eot_tag as tag','tag.tag_id = mm.tmm_tagid');
		$this->db->where('tmm_jobid',$jobId);
		$tagResult = $this->db->get();
		return $tagResult->result_array();
	}

	public function changeJobPriority($jobId,$jobData)
	{
		$responseArray = array();

		//--check required params
		if (!$jobId) 
			$responseArray = array('success'=> false, 'message' => 'Job id required.', 'data'=>[]);
		else
		{	
			$tblName = 'eot_job';	
			$condition = array('job_id' => $jobId);
			$updateResult = $this->CommonModel->updateData($tblName,$jobData,$condition);
			if ($updateResult) 
				$responseArray = array("success"=>true,'message'=>'Job priority changed successfully.', 'data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Job priority not changed, please try again.', 'data'=>[]); 
		}
		return $responseArray;
	}

	public function changeJobStatus($jobId,$usrId,$status,$jobType,$dateTime)
	{
		$this->db->trans_begin();//--db transaction start
		$responseArray = array();

		//--check required params
		if (!$jobId) 
			$responseArray = array('success'=> false, 'message' => 'Job id required.', 'data'=>[]);
		else if (!$usrId) 
			$responseArray = array('success'=> false, 'message' => 'User id required.', 'data'=>[]);
		else if (!$status) 
			$responseArray = array('success'=> false, 'message' => 'Status required.', 'data'=>[]);
		else if (!$jobType) 
			$responseArray = array('success'=> false, 'message' => 'Job type required.', 'data'=>[]);
		else if (!$dateTime) 
			$responseArray = array('success'=> false, 'message' => 'Date time required.', 'data'=>[]);
		else
		{	
			if ($status == 5) 
			{
				//--insert in travel log table
				$this->insertTravelLog($jobId,$usrId,$status,$dateTime);
			}
			else if($status == 6)
			{
				//--get current travell of user
				$tlogResult = $this->getTravellLog($jobId,$usrId);
				if ($tlogResult->num_rows()) 
				{
					$tlogResult = $tlogResult->row();
					
					//--end travel time for take break
					$this->updateTravelLog($tlogResult->tlog_id,$dateTime);
				}
			}
			else if($status == 7)
			{
				//--get current travell of user
				$tlogResult = $this->getTravellLog($jobId,$usrId);
				if ($tlogResult->num_rows()) 
				{
					$tlogResult = $tlogResult->row();
					
					//--end travel time for take break
					$this->updateTravelLog($tlogResult->tlog_id,$dateTime);
				}

				//--get current progress log of user
				$logResult = $this->getProgressLog($jobId,$usrId);
				if ($logResult->num_rows()) 
				{
					$logResult = $logResult->row();
					
					//--end log time of progress
					$this->updateProgressLog($logResult->log_id,$dateTime);
				}					

				//--insert in time log table
				$this->insertProgressLog($jobId,$usrId,$status,$dateTime);
			}
			else if ($status == 8 || $status == 9 || $status == 10)  
			{
				//--get current progress log of user
				$logResult = $this->getProgressLog($jobId,$usrId);
				if ($logResult->num_rows()) 
				{
					$logResult = $logResult->row();
					
					//--end log time of progress
					$this->updateProgressLog($logResult->log_id,$dateTime);
				}
			}

			//--insert in status log table for history
			$this->insertStatusLog($jobId,$usrId,$status,$dateTime);

			//--update job
			$updateResult = '';
			if ($jobType == 1) 
			{
				$tblName = 'eot_job';
				$jobData['job_status'] = $status;
				$jobData['job_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
				$condition = array('job_id' => $jobId);
				$updateResult = $this->CommonModel->updateData($tblName,$jobData,$condition);
			}
			else
			{
				$tblName = 'eot_job_member';
				$jobMemData = array('jm_status' => $status);
				$condition = array('jm_usrid' => $usrId, 'jm_jobid' => $jobId);
				$updateResult = $this->CommonModel->updateData($tblName,$jobMemData,$condition);
			}
			if ($updateResult) 
				$responseArray = array("success"=>true,'message'=>'Job status changed successfully.', 'data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Job status not changed, please try again.', 'data'=>[]); 
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

	//--for insert status log of a job
	public function insertStatusLog($jobId,$usrId,$status,$dateTime)
	{
		$tblName = 'eot_status_log';
		$logData['slog_usrid'] = $usrId;
		$logData['slog_jobid'] = $jobId;
		$logData['stlog_status'] = $status;
		$logData['slog_time'] = $dateTime;
		$this->CommonModel->insertData($tblName,$logData);
	}

	//--for insert travell log
	public function insertTravelLog($jobId,$usrId,$status,$dateTime)
	{
		$tblName = 'eot_travel_log';
		$logData['tlog_usrid'] = $usrId;
		$logData['tlog_jobid'] = $jobId;
		$logData['tlog_login_time'] = $dateTime;
		$logData['tlog_progress_status'] = 1;
		$this->CommonModel->insertData($tblName,$logData);
	}

	//--for end travell log
	public function updateTravelLog($tlogId,$dateTime)
	{
		$tblName = 'eot_travel_log';
		$logData['tlog_logout_time'] = $dateTime;
		$logData['tlog_progress_status'] = 2;
		$condition = array('tlog_id' => $tlogId);
		$this->CommonModel->updateData($tblName,$logData,$condition);
	}

	//--for get travell log
	public function getTravellLog($jobId,$usrId)
	{
		$fields = 'tlog_id';
		$tblName = 'eot_travel_log';	
		$condition = array('tlog_usrid' => $usrId, 'tlog_jobid' => $jobId, 'tlog_progress_status' => 1);
		return $this->CommonModel->getData($fields,$tblName,$condition);
	}

	//--insert progress log
	public function insertProgressLog($jobId,$usrId,$status,$dateTime)
	{
		$tblName = 'eot_time_log';
		$logData['log_usrid'] = $usrId;
		$logData['log_jobid'] = $jobId;
		$logData['log_login_time'] = $dateTime;
		$logData['log_progress_status'] = 1;
		$this->CommonModel->insertData($tblName,$logData);
	}

	//--end progress log
	public function updateProgressLog($logId,$dateTime)
	{
		$tblName = 'eot_time_log';
		$logData['log_logout_time'] = $dateTime;
		$logData['log_progress_status'] = 2;
		$condition = array('log_id' => $logId);
		$this->CommonModel->updateData($tblName,$logData,$condition);
	}	

	//--get progress log
	public function getProgressLog($jobId,$usrId)
	{
		$fields = 'log_id';
		$tblName = 'eot_time_log';	
		$condition = array('log_usrid' => $usrId, 'log_jobid' => $jobId, 'log_progress_status' => 1);
		return $this->CommonModel->getData($fields,$tblName,$condition);
	}

	public function updateJob($jobId,$usrId,$compId,$jobData,$jobMembers,$siteForFuture,$contactForFuture,$siteData,$conData,$tagData)
	{
		$this->db->trans_begin();//--db transaction start
		$responseArray = array();

		//--check required params
		if (!$jobId) 
			$responseArray = array('success'=> false, 'message' => 'Job id required.', 'data'=>[]);
		else if (!$jobData['job_jtid']) 
			$responseArray = array('success'=> false, 'message' => 'Job title id required.', 'data'=>[]);
		else if (!$usrId) 
			$responseArray = array('success'=> false, 'message' => 'User id required.', 'data'=>[]);
		else if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{	
			//--insert or update client site
			if ($siteForFuture)
			{
				if ($jobData['job_siteid']) 
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
						$condition = array('site_id' => $jobData['job_siteid']);
						$siteData['site_cltid'] = $jobData['job_cltid'];				
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
						$siteData['site_cltid'] = $jobData['job_cltid'];				
						$siteData['site_lat'] = $LatLongResult['latitude'];
						$siteData['site_long'] = $LatLongResult['longitude'];
						$siteData['site_isactive'] = 1;
						$siteData['site_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
					   	$jobData['job_siteid'] = $this->CommonModel->insertData($tblName,$siteData);
					}
				}
				$jobData['job_site_name'] = '';
			}

			//--insertor update client contact
			if ($contactForFuture)
			{
				if ($jobData['job_conid']) 
				{
					if (!$conData['con_name']) 
						return array('success'=> false, 'message' => 'Client contact name required.', 'data'=>[]);
					else if (!$conData['con_email']) 
						return array('success'=> false, 'message' => 'Client contact email required.', 'data'=>[]);
					else
					{
						//--add client contact
						$tblName = 'eot_contact';	
						$condition = array('con_id' => $jobData['job_conid']);
						$conData = array('con_cltid' =>$jobData['job_cltid']);
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
						$conData['con_cltid'] = $jobData['job_cltid'];
						$conData['con_isactive'] = 1;
						$conData['con_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
						$jobData['job_conid'] = $this->CommonModel->insertData($tblName,$conData);
					} 
				}
				$jobData['job_contact_name'] = '';
			}

			//--Get latitude and longitude from address
			$LatLongResult = $this->CommonModel->getLatLong($jobData['job_address']);
			$jobData['job_lat'] = $LatLongResult['latitude'];
			$jobData['job_long'] = $LatLongResult['longitude'];

			//--if job type is 2 (Multiple)
			if ($jobData['job_type'] == 2) 
			{
				//--first delete previous all job members
				$tblName = 'eot_job_member';
				$condition = array('jm_jobid' => $jobId);
				$this->CommonModel->deleteData($tblName,$condition);

				//--merge author and keeper in member array
				//array_push($jobMembers, $jobData['job_author']);
				//array_push($jobMembers, $jobData['job_keeper']);
				
				//-- code for insert job members
	            $jobMembers = array_unique($jobMembers);
	            foreach ($jobMembers as $key) 
	            {
	                $tblName = 'eot_job_member';
	                $jobMemData['jm_usrid'] = $key;
	                $jobMemData['jm_jobid'] = $jobId;
	                $jobMemData['jm_status'] = 1;
	                $jobMemData['jm_mem_isactive'] = 1;
					$this->CommonModel->insertData($tblName,$jobMemData);
	            }
			}

			//--logic for change status
			$fields = 'job_status';
			$tblName = 'eot_job';	
			$condition = array('job_id' => $jobId);
			$jobResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$jobResult = $jobResult->row();
			if ($jobResult->job_status != $jobData['job_status']) 
			{
				$this->changeJobStatus($jobId,$usrId,$jobData['job_status'],$jobData['job_type'],strtotime(gmdate('Y-m-d h:i:s a').' UTC'));
			}

			//--update job data
			$tblName = 'eot_job';
			$condition = array('job_id' => $jobId);
			$result = $this->CommonModel->updateData($tblName,$jobData,$condition);
			if ($result)
			{
				//--delete all previous tag
				$tblName = 'eot_tag_mm';
				$condition = array('tmm_jobid' => $jobId);	
				$this->CommonModel->deleteData($tblName,$condition);

				//--add tag if get
				if($tagData) 
				{				
					foreach ($tagData as $tag) 
			        {
			            //--if get tag id
			            if($tag->tagId)  
			            {
			                $tblName = 'eot_tag_mm';
							$mmData['tmm_tagid'] = $tag->tagId;
							$mmData['tmm_jobid'] = $jobId;
							$this->CommonModel->insertData($tblName,$mmData);
			            }
			            else
			            {
							$tblName = 'eot_tag';
							$data['tag_name'] = $tag->tnm;
							$data['tag_compid'] = $compId;
							$tagId = $this->CommonModel->insertData($tblName,$data);
							if ($tagId) 
							{
								$tblName = 'eot_tag_mm';
								$mmData['tmm_tagid'] = $tagId;
								$mmData['tmm_jobid'] = $jobId;
								$this->CommonModel->insertData($tblName,$mmData);
							}
			            }
			        }
			    }
			    
			    //--make return data array
				$data['schdlStart'] = $jobData['job_shedule_start'];
				$data['schdlFinish'] = $jobData['job_shedule_finish'];
				$data['tagData'] = $tagResult = $this->getJobTags($jobId);
			    $responseArray = array('success'=> true, 'message'=>'Job update successfully.', 'data'=>$data);
			}
			else
				$responseArray = array("success"=>false,'message'=>'Job not update, please try again.', 'data'=>[]);
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

	public function deleteJob($jobId)
	{
		$responseArray = array();

		//--check required params
		if (!$jobId) 
			$responseArray = array('success'=> false, 'message' => 'Job id required.', 'data'=>[]);
		else
		{	
			//--stop travell log if running
			$fields = 'tlog_id';
			$tblName = 'eot_travel_log';	
			$condition = array('tlog_jobid' => $jobId, 'tlog_progress_status' => 1);
			$result = $this->CommonModel->getData($fields,$tblName,$condition);
			if ($result->num_rows()) 
			{
				//--end travel time
				$tblName = 'eot_travel_log';	
				$jobData['tlog_progress_status'] = 0;
				$jobData['tlog_logout_time'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
				$condition = array('tlog_jobid' => $jobId);
				$this->CommonModel->updateData($tblName,$jobData,$condition);
			}

			//--stop time log if running
			$fields = 'log_id';
			$tblName = 'eot_time_log';
			$condition = array('log_jobid' => $jobId, 'log_progress_status' => 1);
			$result = $this->CommonModel->getData($fields,$tblName,$condition);
			if ($result->num_rows()) 
			{
				//--end log time
				$tblName = 'eot_time_log';	
				$jobData['log_progress_status'] = 0;
				$jobData['log_logout_time'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
				$condition = array('log_jobid' => $jobId);
				$this->CommonModel->updateData($tblName,$jobData,$condition);
			}

			//--soft delete
			$tblName = 'eot_job';	
			$jobData = array('job_isdelete' => 0);
			$condition = array('job_id' => $jobId);
			$result = $this->CommonModel->updateData($tblName,$jobData,$condition);
			if ($result) 
				$responseArray = array("success"=>true,'message'=>'Job deleted successfully.', 'data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Job not deleted, please try again.', 'data'=>[]); 
		}
		return $responseArray;
	}

	public function getJobStatusHistory($jobId,$limit,$index)
	{
		$responseArray = array();

		//--check required params
		if (!$jobId)
			$responseArray = array('success'=> false,'message'=> 'Job id required.','data'=> []);
		else
		{
			$this->db->select('SQL_CALC_FOUND_ROWS CONCAT(usr.usr_fname," ",usr.usr_lname) as name,slog.stlog_status as status,slog.slog_time as time',FALSE);
			$this->db->from('eot_status_log as slog');
			$this->db->join('eot_user as usr','usr.usr_id = slog.slog_usrid');
			$this->db->where('slog_jobid',$jobId);
			$this->db->order_by('slog_time','desc');
			if ($limit || $index){
				$this->db->limit($limit,$index);
			}
			$statusResult = $this->db->get();

			//for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
			$total_count = $total_record->row();
			$total_count = $total_count->total_count;

			$responseArray = array('success'=> true,'data'=> $statusResult->result_array(), 'count'=>$total_count);
		}	
		return $responseArray;
	}

	public function getTagList($compId,$limit,$index,$search)
	{
		$responseArray = array();

		//--check required params
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$this->db->select('SQL_CALC_FOUND_ROWS tag_id as tagId,tag_name as tnm',FALSE);
			$this->db->from('eot_tag');
			$this->db->where('tag_compid',$compId);
			if ($search){
				$this->db->like('tag_name',$search);
			}
			$this->db->order_by('tag_name','asc');
			if ($limit || $index)
				$this->db->limit($limit,$index);
			$tagResult = $this->db->get();

			//for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
			$total_count = $total_record->row();
			$total_count = $total_count->total_count;

			//--final result
			$responseArray = array("success"=>true,'data'=>$tagResult->result_array(),'count'=>$total_count);
		}
		return $responseArray;
	}

	public function changeJobTitle($jobId,$jtId)
	{
		$responseArray = array();

		//--check required params
		if (!$jobId) 
			$responseArray = array('success'=> false, 'message' => 'Job id required.', 'data'=>[]);
		else
		{	
			$tblName = 'eot_job';
			$data = array('job_jtid' => $jtId);	
			$condition = array('job_id' => $jobId);
			$updateResult = $this->CommonModel->updateData($tblName,$data,$condition);
			if ($updateResult) 
				$responseArray = array("success"=>true,'message'=>'Job title changed successfully.', 'data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Job title not changed, please try again.', 'data'=>[]); 
		}
		return $responseArray;
	}

	public function changeJobKeeper($jobId,$kpr)
	{
		$responseArray = array();

		//--check required params
		if (!$jobId) 
			$responseArray = array('success'=> false, 'message' => 'Job id required.', 'data'=>[]);
		else
		{	
			$tblName = 'eot_job';
			$data = array('job_keeper' => $kpr);	
			$condition = array('job_id' => $jobId);
			$updateResult = $this->CommonModel->updateData($tblName,$data,$condition);
			if ($updateResult) 
				$responseArray = array("success"=>true,'message'=>'Job keeper changed successfully.', 'data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Job keeper not changed, please try again.', 'data'=>[]); 
		}
		return $responseArray;
	}

	public function addFeedback($feedData)
	{
		$responseArray = array();

		//--check required params
		if (!$feedData['feed_jobid']) 
			$responseArray = array('success'=> false, 'message' => 'Job id required.', 'data'=>[]);
		else if (!$feedData['feed_usrid']) 
			$responseArray = array('success'=> false, 'message' => 'User id required.', 'data'=>[]);
		else
		{ 
			$tblName = 'eot_feedback';
			$insertResult = $this->CommonModel->insertData($tblName,$feedData);
		 
			if($insertResult)
				$responseArray = array("success"=>true,'message'=>'Feedback added successfully.','data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Feedback not added, please try again.', 'data'=>[]); 
		}
		return $responseArray;
	}

	public function getFeedbackList($jobId,$limit,$index,$search)
	{
		$responseArray = array();

		//--check required params
		if (!$jobId)
			$responseArray = array('success'=> false, 'message' => 'Job id required.', 'data'=>[]);
		else
		{
			$this->db->select('SQL_CALC_FOUND_ROWS feed.feed_id as feedId,feed.feed_usrid as usrId,usr.usr_fname as fnm,usr.usr_lname as lnm,job.job_client_name as nm,feed.feed_jobid as jobId,feed.feed_description as des,feed.feed_rating as rating,CASE WHEN feed.feed_signature!="" THEN CONCAT("uploads/signature/",feed_signature) ELSE feed.feed_signature END as sign',FALSE);
			$this->db->from('eot_feedback as feed');
			$this->db->join('eot_job as job','job.job_id = feed.feed_jobid');
			$this->db->join('eot_user as usr','usr.usr_id = feed.feed_usrid');
			$this->db->where('feed_jobid',$jobId);
			if ($search){
				$this->db->like('feed_description',$search);
				$this->db->or_like('usr_fname',$search);
				$this->db->or_like('usr_lname',$search);
				$this->db->or_like('job_client_name',$search);
			}
			
			$this->db->order_by('feed_id','asc');
			if ($limit || $index)
				$this->db->limit($limit,$index);
			$feedResult = $this->db->get();

			//for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
			$total_count = $total_record->row();
			$total_count = $total_count->total_count;

			//--final result
			$responseArray = array("success"=>true,'data'=>$feedResult->result_array(),'count'=>$total_count);
		}
		return $responseArray;
	}
}
?>