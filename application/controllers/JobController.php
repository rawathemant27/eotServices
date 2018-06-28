<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class JobController extends CI_Controller
{
	function __construct()
	{
		parent::__construct(); 
		$this->load->model('JobModel');
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

	//--for add job title
	public function addJobTitle()
	{
		//-- get params
		$jobTitleData['jt_compid'] = isset($this->requestData->compId)?$this->requestData->compId:"";
		$jobTitleData['jt_title'] = isset($this->requestData->title)?$this->requestData->title:"";
		$jobTitleData['jt_description'] = isset($this->requestData->des)?$this->requestData->des:"";
		$jobTitleData['jt_labour_rate'] = isset($this->requestData->labour)?$this->requestData->labour:"";
		$taxData = isset($this->requestData->taxData)?$this->requestData->taxData:array();
        /*$taxData = json_decode
		(
			'[
				{"taxId":"1","rate":"7.5"},
				{"taxId":"2","rate":"7.5"}
			]'
		);*/
		try
		{
			//-- call model function addJobTitle
			$arrayResponse = $this->JobModel->addJobTitle($jobTitleData,$taxData);
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

	//--get job title list
	public function getJobTitleList()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		$search = isset($this->requestData->search)?$this->requestData->search:"";
		try
		{
			//-- call model function getJobTitleList
			$responseArray = $this->JobModel->getJobTitleList($compId,$limit,$index,$search);
			if(empty($responseArray))
				throw new Exception('Response not get, please try again.');
		} 
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage(), 'data' => []));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($responseArray);
	}

	//--get jobtitle details
	public function getJobTitleDetail()
	{
		//-- get params
		$jtId = isset($this->requestData->jtId)?$this->requestData->jtId:"";
		try
		{
			//-- call model function getJobTitleDetail
			$arrayResponse = $this->JobModel->getJobTitleDetail($jtId);
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

	//--update job title
	public function updateJobTitle()
	{
		//-- get params
		$jtId = isset($this->requestData->jtId)?$this->requestData->jtId:"";
		$jobTitleData['jt_title'] = isset($this->requestData->title)?$this->requestData->title:"";
		$jobTitleData['jt_description'] = isset($this->requestData->des)?$this->requestData->des:"";
		$jobTitleData['jt_labour_rate'] = isset($this->requestData->labour)?$this->requestData->labour:"";
		$taxData = isset($this->requestData->taxData)?$this->requestData->taxData:array();
        /*$taxData = json_decode
		(
			'[
				{"taxId":"1","rate":"7"},
				{"taxId":"2","rate":"7"}
			]'
		);*/
		try
		{
			//-- call model function updateJobTitle
			$arrayResponse = $this->JobModel->updateJobTitle($jtId,$jobTitleData,$taxData);
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

	//--delete job title
	public function deleteJobTitle()
	{
		//-- get params
		$jtId = isset($this->requestData->jtId)?$this->requestData->jtId:"";
		try
		{
			//-- call model function deleteJobTitle
			$arrayResponse = $this->JobModel->deleteJobTitle($jtId);
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

	//--add job
	public function addJob()
	{
		//-- get params
		//--job data
		$jobData['job_parent_jobid'] = isset($this->requestData->parentId)?$this->requestData->parentId:"";
		$jobData['job_compid'] = isset($this->requestData->compId)?$this->requestData->compId:"";
		$jobData['job_cltid'] = isset($this->requestData->cltId)?$this->requestData->cltId:"";
		$jobData['job_siteid'] = isset($this->requestData->siteId)?$this->requestData->siteId:"";
		$jobData['job_conid'] = isset($this->requestData->conId)?$this->requestData->conId:"";
		$jobData['job_quotid'] = isset($this->requestData->quotId)?$this->requestData->quotId:"";
		$jobData['job_jtid'] = isset($this->requestData->jtId)?$this->requestData->jtId:"";
		$jobData['job_description'] = isset($this->requestData->des)?$this->requestData->des:"";
		$jobData['job_type'] = isset($this->requestData->type)?$this->requestData->type:"";
		$jobData['job_priority'] = isset($this->requestData->prty)?$this->requestData->prty:"";
		$jobData['job_status'] = isset($this->requestData->status)?$this->requestData->status:"1";
		$jobData['job_author'] = isset($this->requestData->athr)?$this->requestData->athr:"";
		$jobData['job_keeper'] = isset($this->requestData->kpr)?$this->requestData->kpr:"";
		$jobData['job_shedule_start'] = $this->CommonModel->changeTimeInUTC(isset($this->requestData->schdlStart)?$this->requestData->schdlStart:"");
		$jobData['job_shedule_finish'] = $this->CommonModel->changeTimeInUTC(isset($this->requestData->schdlEnd)?$this->requestData->schdlEnd:"");
		$jobData['job_instruction'] = isset($this->requestData->inst)?$this->requestData->inst:"";
		$jobData['job_client_name'] = isset($this->requestData->nm)?$this->requestData->nm:"";
		$jobData['job_contact_name'] = isset($this->requestData->cnm)?$this->requestData->cnm:"";
		$jobData['job_site_name'] = isset($this->requestData->snm)?$this->requestData->snm:"";
		$jobData['job_email'] = isset($this->requestData->email)?$this->requestData->email:"";
		$jobData['job_mobile1'] = isset($this->requestData->mob1)?$this->requestData->mob1:"";
		$jobData['job_mobile2'] = isset($this->requestData->mob2)?$this->requestData->mob2:"";
		$jobData['job_address'] = isset($this->requestData->adr)?$this->requestData->adr:"";
		$jobData['job_city'] = isset($this->requestData->city)?$this->requestData->city:"";
		$jobData['job_state'] = isset($this->requestData->state)?$this->requestData->state:"";
		$jobData['job_country'] = isset($this->requestData->ctry)?$this->requestData->ctry:"";
		$jobData['job_zipcode'] = isset($this->requestData->zip)?$this->requestData->zip:"";
		$jobData['job_isactive'] = 1;
		$jobData['job_isdelete'] = 1;
		$jobData['job_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		$jobData['job_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		$jobMembers = isset($this->requestData->memIds)?$this->requestData->memIds:array();

		$clientForFuture = isset($this->requestData->clientForFuture)?$this->requestData->clientForFuture:"";
		$siteForFuture = isset($this->requestData->siteForFuture)?$this->requestData->siteForFuture:"";
		$contactForFuture = isset($this->requestData->contactForFuture)?$this->requestData->contactForFuture:"";

		//--client data
		$cltData = array();
		if ($clientForFuture) 
		{
			$cltData['clt_compid'] = isset($this->requestData->compId)?$this->requestData->compId:"";
			$cltData['clt_name'] = isset($this->requestData->nm)?$this->requestData->nm:"";
			$cltData['clt_payment_type'] = isset($this->requestData->pymtType)?$this->requestData->pymtType:"";
			$cltData['clt_gst_no'] = isset($this->requestData->gstNo)?$this->requestData->gstNo:"";
			$cltData['clt_tin_no'] = isset($this->requestData->tinNo)?$this->requestData->tinNo:"";
			$cltData['clt_industry'] = isset($this->requestData->industry)?$this->requestData->industry:"";
			$cltData['clt_note'] = isset($this->requestData->note)?$this->requestData->note:"";
			$cltData['clt_isactive'] = 1;
			$cltData['clt_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
			$cltData['clt_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		}
		
		//--site data
		$siteData = array();
		if ($siteForFuture) 
		{
			$siteData['site_name'] = isset($this->requestData->snm)?$this->requestData->snm:"";
			$siteData['site_address'] = isset($this->requestData->adr)?$this->requestData->adr:"";
			$siteData['site_city'] = isset($this->requestData->city)?$this->requestData->city:"";
			$siteData['site_state'] = isset($this->requestData->state)?$this->requestData->state:"";
			$siteData['site_country'] = isset($this->requestData->ctry)?$this->requestData->ctry:"";
			$siteData['site_zipcode'] = isset($this->requestData->zip)?$this->requestData->zip:"";
			$siteData['site_isactive'] = 1;
			$siteData['site_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
			$siteData['site_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		}

		//--contact data
		$conData = array();
		if ($contactForFuture) 
		{
			$conData['con_name'] = isset($this->requestData->cnm)?$this->requestData->cnm:"";
			$conData['con_email'] = isset($this->requestData->email)?$this->requestData->email:"";
			$conData['con_mobile1'] = isset($this->requestData->mob1)?$this->requestData->mob1:"";
			$conData['con_mobile2'] = isset($this->requestData->mob2)?$this->requestData->mob2:"";
			$conData['con_fax'] = isset($this->requestData->fax)?$this->requestData->fax:"";
			$conData['con_twitter'] = isset($this->requestData->twitter)?$this->requestData->twitter:"";
			$conData['con_skype'] = isset($this->requestData->skype)?$this->requestData->skype:"";
			$conData['con_isactive'] = 1;
			$conData['con_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
			$conData['con_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		}
		$tagData = isset($this->requestData->tagData)?$this->requestData->tagData:array();
		// $tagData = json_decode(
		// 	'[
		// 	 	{"tagId":"1","tnm":"test"},
		// 	 	{"tagId":"","tnm":"test"};
		// 	 ]'
		// );
		try
		{
			//-- call model function addJob
			$arrayResponse = $this->JobModel->addJob($jobData,$jobMembers,$clientForFuture,$siteForFuture,$contactForFuture,$cltData,$siteData,$conData,$tagData);
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

	//--get user job list
	public function getUserJobList()
	{
		//-- get params
		$usrId = isset($this->requestData->usrId)?$this->requestData->usrId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		$search = isset($this->requestData->search)?$this->requestData->search:"";
		$dateTime = $this->CommonModel->changeTimeInUTC(isset($this->requestData->dateTime)?$this->requestData->dateTime:"");
		try
		{
			//-- call model function getUserJobList
			$responseArray = $this->JobModel->getUserJobList($usrId,$limit,$index,$search,$dateTime);
			if(empty($responseArray))
				throw new Exception('Response not get, please try again.');
		} 
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage(), 'data' => []));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($responseArray);
	}

	//--get Admin job list
	public function getAdminJobList()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		$search = isset($this->requestData->search)?$this->requestData->search:"";
		try
		{
			//-- call model function getAdminJobList
			$responseArray = $this->JobModel->getAdminJobList($compId,$limit,$index,$search);
			if(empty($responseArray))
				throw new Exception('Response not get, please try again.');
		} 
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage(), 'data' => []));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($responseArray);
	}

	//--get job detail
	public function getJobDetail()
	{
		//-- get params
		$jobId = isset($this->requestData->jobId)?$this->requestData->jobId:"";
		try
		{
			//-- call model function getJobDetail
			$responseArray = $this->JobModel->getJobDetail($jobId);
			if(empty($responseArray))
				throw new Exception('Response not get, please try again.');
		} 
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage(), 'data' => []));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($responseArray);
	}

	//--Change job priority
	public function changeJobPriority()
	{
		//-- get params
		$jobId = isset($this->requestData->jobId)?$this->requestData->jobId:"";
		$jobData['job_priority'] = isset($this->requestData->prty)?$this->requestData->prty:"";
		$jobData['job_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		try
		{
			//-- call model function changeJobPriority
			$arrayResponse = $this->JobModel->changeJobPriority($jobId,$jobData);
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

	//--Change job status
	public function changeJobStatus()
	{
		//-- get params
		$jobId = isset($this->requestData->jobId)?$this->requestData->jobId:"";
		$usrId = isset($this->requestData->usrId)?$this->requestData->usrId:"";
		$status = isset($this->requestData->status)?$this->requestData->status:"";
		$jobType = isset($this->requestData->type)?$this->requestData->type:"";
		$dateTime = $this->CommonModel->changeTimeInUTC(isset($this->requestData->dateTime)?$this->requestData->dateTime:"");
		try
		{
			//-- call model function changeJobStatus
			$arrayResponse = $this->JobModel->changeJobStatus($jobId,$usrId,$status,$jobType,$dateTime);
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

	//--update job
	public function updateJob()
	{
		//-- get params
		$jobId = isset($this->requestData->jobId)?$this->requestData->jobId:"";
		$usrId = isset($this->requestData->usrId)?$this->requestData->usrId:"";
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";

		//--job data
		$jobData['job_cltid'] = isset($this->requestData->cltId)?$this->requestData->cltId:"";
		$jobData['job_siteid'] = isset($this->requestData->siteId)?$this->requestData->siteId:"";
		$jobData['job_conid'] = isset($this->requestData->conId)?$this->requestData->conId:"";
		$jobData['job_quotid'] = isset($this->requestData->quotId)?$this->requestData->quotId:"";
		$jobData['job_jtid'] = isset($this->requestData->jtId)?$this->requestData->jtId:"";
		$jobData['job_description'] = isset($this->requestData->des)?$this->requestData->des:"";
		$jobData['job_type'] = isset($this->requestData->type)?$this->requestData->type:"";
		$jobData['job_priority'] = isset($this->requestData->prty)?$this->requestData->prty:"";
		$jobData['job_status'] = isset($this->requestData->status)?$this->requestData->status:"";
		$jobData['job_author'] = isset($this->requestData->athr)?$this->requestData->athr:"";
		$jobData['job_keeper'] = isset($this->requestData->kpr)?$this->requestData->kpr:"";
		$jobData['job_shedule_start'] = $this->CommonModel->changeTimeInUTC(isset($this->requestData->schdlStart)?$this->requestData->schdlStart:"");
		$jobData['job_shedule_finish'] = $this->CommonModel->changeTimeInUTC(isset($this->requestData->schdlEnd)?$this->requestData->schdlEnd:"");
		$jobData['job_instruction'] = isset($this->requestData->inst)?$this->requestData->inst:"";
		$jobData['job_contact_name'] = isset($this->requestData->cnm)?$this->requestData->cnm:"";
		$jobData['job_site_name'] = isset($this->requestData->snm)?$this->requestData->snm:"";
		$jobData['job_email'] = isset($this->requestData->email)?$this->requestData->email:"";
		$jobData['job_mobile1'] = isset($this->requestData->mob1)?$this->requestData->mob1:"";
		$jobData['job_mobile2'] = isset($this->requestData->mob2)?$this->requestData->mob2:"";
		$jobData['job_address'] = isset($this->requestData->adr)?$this->requestData->adr:"";
		$jobData['job_city'] = isset($this->requestData->city)?$this->requestData->city:"";
		$jobData['job_state'] = isset($this->requestData->state)?$this->requestData->state:"";
		$jobData['job_country'] = isset($this->requestData->ctry)?$this->requestData->ctry:"";
		$jobData['job_zipcode'] = isset($this->requestData->zip)?$this->requestData->zip:"";
		$jobData['job_isactive'] = isset($this->requestData->isactive)?$this->requestData->isactive:"";
		$jobData['job_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		$jobMembers = isset($this->requestData->memIds)?$this->requestData->memIds:array();
	
		$siteForFuture = isset($this->requestData->siteForFuture)?$this->requestData->siteForFuture:"";
		$contactForFuture = isset($this->requestData->contactForFuture)?$this->requestData->contactForFuture:"";

		//--site data
		$siteData = array();
		if ($siteForFuture) 
		{
			$siteData['site_name'] = isset($this->requestData->snm)?$this->requestData->snm:"";
			$siteData['site_address'] = isset($this->requestData->adr)?$this->requestData->adr:"";
			$siteData['site_city'] = isset($this->requestData->city)?$this->requestData->city:"";
			$siteData['site_state'] = isset($this->requestData->state)?$this->requestData->state:"";
			$siteData['site_country'] = isset($this->requestData->ctry)?$this->requestData->ctry:"";
			$siteData['site_zipcode'] = isset($this->requestData->zip)?$this->requestData->zip:"";
			$siteData['site_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		}

		//--contact data
		$conData = array();
		if ($contactForFuture) 
		{
			$conData['con_name'] = isset($this->requestData->cnm)?$this->requestData->cnm:"";
			$conData['con_email'] = isset($this->requestData->email)?$this->requestData->email:"";
			$conData['con_mobile1'] = isset($this->requestData->mob1)?$this->requestData->mob1:"";
			$conData['con_mobile2'] = isset($this->requestData->mob2)?$this->requestData->mob2:"";
			$conData['con_fax'] = isset($this->requestData->fax)?$this->requestData->fax:"";
			$conData['con_twitter'] = isset($this->requestData->twitter)?$this->requestData->twitter:"";
			$conData['con_skype'] = isset($this->requestData->skype)?$this->requestData->skype:"";
			$conData['con_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		}
		$tagData = isset($this->requestData->tagData)?$this->requestData->tagData:array();
		/*$tagData = json_decode(
			'[
			 	{"tagId":"","tnm":"test1"},
			 	{"tagId":"","tnm":"test2"}
			 ]'
		);*/
		try
		{
			//-- call model function updateJob
			$arrayResponse = $this->JobModel->updateJob($jobId,$usrId,$compId,$jobData,$jobMembers,$siteForFuture,$contactForFuture,$siteData,$conData,$tagData);
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

	//--delete JOb
	public function deleteJob()
	{
		//-- get params
		$jobId = isset($this->requestData->jobId)?$this->requestData->jobId:"";
		try
		{
			//-- call model function deleteJob
			$arrayResponse = $this->JobModel->deleteJob($jobId);
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

	//--get job status history
	public function getJobStatusHistory()
	{
		//-- get params
		$jobId = isset($this->requestData->jobId)?$this->requestData->jobId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		try
		{
			//-- call model function getJobStatusHistory
			$responseArray = $this->JobModel->getJobStatusHistory($jobId,$limit,$index);
			if(empty($responseArray))
				throw new Exception('Response not get, please try again.');
		} 
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage()));
			exit();
		}	
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($responseArray);
	}

	//--get tag list
	public function getTagList()
	{
		//-- get params
			$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
			$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
			$index = isset($this->requestData->index)?$this->requestData->index:"";
			$search = isset($this->requestData->search)?$this->requestData->search:"";
		try
		{
			//-- call model function getTagList
			$responseArray = $this->JobModel->getTagList($compId,$limit,$index,$search);
			if(empty($responseArray))
				throw new Exception('Response not get, please try again.');
		} 
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage()));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($responseArray);
	}

	//--change job title
	public function changeJobTitle()
	{
		//-- get params
		$jobId = isset($this->requestData->jobId)?$this->requestData->jobId:"";
		$jtId = isset($this->requestData->jtId)?$this->requestData->jtId:"";
		try
		{
			//-- call model function changeJobTitle
			$responseArray = $this->JobModel->changeJobTitle($jobId,$jtId);
			if(empty($responseArray))
				throw new Exception('Response not get, please try again.');
		} 
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage()));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($responseArray);
	}

	//--change job keeper
	public function changeJobKeeper()
	{
		//-- get params
		$jobId = isset($this->requestData->jobId)?$this->requestData->jobId:"";
		$kpr = isset($this->requestData->kpr)?$this->requestData->kpr:"";
		try
		{
			//-- call model function changeJobKeeper
			$responseArray = $this->JobModel->changeJobKeeper($jobId,$kpr);
			if(empty($responseArray))
				throw new Exception('Response not get, please try again.');
		} 
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage()));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($responseArray);
	}

	//--for add feedback
	public function addFeedback()
	{
		//--logic for image upload if get else blank
		if (isset($_FILES['sign']) && !empty($_FILES['sign']['tmp_name']))
			$signName = $this->CommonModel->imageUpload('sign','./uploads/signature');
		else
			$signName = '';

		//-- get params
		$feedData['feed_usrid'] = isset($this->requestData->usrId)?$this->requestData->usrId:"";
		$feedData['feed_jobid'] = isset($this->requestData->jobId)?$this->requestData->jobId:"";
		$feedData['feed_description'] = isset($this->requestData->des)?$this->requestData->des:"";
		$feedData['feed_rating'] = isset($this->requestData->rating)?$this->requestData->rating:"";
		$feedData['feed_signature'] = $signName;
		$feedData['feed_isactive'] = isset($this->requestData->isactive)?$this->requestData->isactive:"";
		$feedData['feed_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
	 
		try
		{
			//-- call model function addFeedback
			$arrayResponse = $this->JobModel->addFeedback($feedData);
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

	//--get feedback list
	public function getFeedbackList()
	{
		//-- get params
		$jobId = isset($this->requestData->jobId)?$this->requestData->jobId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		$search = isset($this->requestData->search)?$this->requestData->search:"";
		
		try
		{
			//-- call model function getFeedbackList
			$responseArray = $this->JobModel->getFeedbackList($jobId,$limit,$index,$search);
			if(empty($responseArray))
				throw new Exception('Response not get, please try again.');
		} 
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage()));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($responseArray);
	}
}
?>