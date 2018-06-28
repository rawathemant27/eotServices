<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class CompanyController extends CI_Controller
{
	function __construct()
	{
		parent::__construct(); 
		$this->load->model('CompanyModel');
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

	//--add client
	public function addClient()
	{
		//-- get params
		//--client data
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

		//--site data
		$siteData['site_name'] = isset($this->requestData->snm)?$this->requestData->snm:"";
		$siteData['site_address'] = isset($this->requestData->adr)?$this->requestData->adr:"";
		$siteData['site_city'] = isset($this->requestData->city)?$this->requestData->city:"";
		$siteData['site_state'] = isset($this->requestData->state)?$this->requestData->state:"";
		$siteData['site_country'] = isset($this->requestData->ctry)?$this->requestData->ctry:"";
		$siteData['site_zipcode'] = isset($this->requestData->zip)?$this->requestData->zip:"";
		$siteData['site_default'] = 1;
		$siteData['site_isactive'] = 1;
		$siteData['site_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		$siteData['site_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');

		//--contact data
		$conData['con_name'] = isset($this->requestData->cnm)?$this->requestData->cnm:"";
		$conData['con_email'] = isset($this->requestData->email)?$this->requestData->email:"";
		$conData['con_mobile1'] = isset($this->requestData->mob1)?$this->requestData->mob1:"";
		$conData['con_mobile2'] = isset($this->requestData->mob2)?$this->requestData->mob2:"";
		$conData['con_fax'] = isset($this->requestData->fax)?$this->requestData->fax:"";
		$conData['con_twitter'] = isset($this->requestData->twitter)?$this->requestData->twitter:"";
		$conData['con_skype'] = isset($this->requestData->skype)?$this->requestData->skype:"";
		$conData['con_default'] = 1;
		$conData['con_isactive'] = 1;
		$conData['con_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		$conData['con_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		try
		{
			//-- call model function addClient
			$arrayResponse = $this->CompanyModel->addClient($cltData,$siteData,$conData);
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

	public function getClientList()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		$search = isset($this->requestData->search)?$this->requestData->search:"";
		try
		{
			//-- call model function getSiteDetail
			$responseArray = $this->CompanyModel->getClientList($compId,$limit,$index,$search);
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

	//--get client details
	public function getClientDetail()
	{
		//-- get params
		$cltId = isset($this->requestData->cltId)?$this->requestData->cltId:"";
		try
		{
			//-- call model function getClientDetail
			$arrayResponse = $this->CompanyModel->getClientDetail($cltId);
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

	//--update client
	public function updateClient()
	{
		//-- get params
		$cltId = isset($this->requestData->cltId)?$this->requestData->cltId:"";
		$cltData['clt_name'] = isset($this->requestData->nm)?$this->requestData->nm:"";
		$cltData['clt_payment_type'] = isset($this->requestData->pymtType)?$this->requestData->pymtType:"";
		$cltData['clt_gst_no'] = isset($this->requestData->gstNo)?$this->requestData->gstNo:"";
		$cltData['clt_tin_no'] = isset($this->requestData->tinNo)?$this->requestData->tinNo:"";
		$cltData['clt_industry'] = isset($this->requestData->industry)?$this->requestData->industry:"";
		$cltData['clt_note'] = isset($this->requestData->note)?$this->requestData->note:"";
		$cltData['clt_isactive'] = isset($this->requestData->isactive)?$this->requestData->isactive:"";
		$cltData['clt_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		try
		{
			//-- call model function updateClient
			$arrayResponse = $this->CompanyModel->updateClient($cltId,$cltData);
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

	//--delete client
	public function deleteClient()
	{
		//-- get params
		$cltId = isset($this->requestData->cltId)?$this->requestData->cltId:"";
		try
		{
			//-- call model function deleteClient
			$arrayResponse = $this->CompanyModel->deleteClient($cltId);
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

	//--add client site
	public function addClientSite()
	{
		//-- get params
		$siteData['site_cltid'] = isset($this->requestData->cltId)?$this->requestData->cltId:"";
		$siteData['site_name'] = isset($this->requestData->snm)?$this->requestData->snm:"";
		$siteData['site_address'] = isset($this->requestData->adr)?$this->requestData->adr:"";
		$siteData['site_city'] = isset($this->requestData->city)?$this->requestData->city:"";
		$siteData['site_state'] = isset($this->requestData->state)?$this->requestData->state:"";
		$siteData['site_country'] = isset($this->requestData->ctry)?$this->requestData->ctry:"";
		$siteData['site_zipcode'] = isset($this->requestData->zip)?$this->requestData->zip:"";
		$siteData['site_default'] = isset($this->requestData->def)?$this->requestData->def:"";
		$siteData['site_isactive'] = 1;
		$siteData['site_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		try
		{
			//-- call model function addClientSite
			$responseArray = $this->CompanyModel->addClientSite($siteData);
			if(empty($responseArray))
				throw new Exception('Response not get, please try again.');
		}
		catch(Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage()));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($responseArray);
	}

	//--get client site list
	public function getClientSiteList()
	{
		//-- get params
		$cltId = isset($this->requestData->cltId)?$this->requestData->cltId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		$search = isset($this->requestData->search)?$this->requestData->search:"";
		try
		{
			//-- call model function getClientSiteList
			$responseArray = $this->CompanyModel->getClientSiteList($cltId,$limit,$index,$search);
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

	//--get Client site details
	public function getClientSiteDetail()
	{
		//-- get params
		$siteId = isset($this->requestData->siteId)?$this->requestData->siteId:"";
		try
		{
			//-- call model function getClientSiteDetail
			$responseArray = $this->CompanyModel->getClientSiteDetail($siteId);
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

	//--update Client site
	public function updateClientSite()
	{
		//-- get params
		$siteId = isset($this->requestData->siteId)?$this->requestData->siteId:"";
		$siteData['site_name'] = isset($this->requestData->snm)?$this->requestData->snm:"";
		$siteData['site_address'] = isset($this->requestData->adr)?$this->requestData->adr:"";
		$siteData['site_city'] = isset($this->requestData->city)?$this->requestData->city:"";
		$siteData['site_state'] = isset($this->requestData->state)?$this->requestData->state:"";
		$siteData['site_country'] = isset($this->requestData->ctry)?$this->requestData->ctry:"";
		$siteData['site_zipcode'] = isset($this->requestData->zip)?$this->requestData->zip:"";
		$siteData['site_isactive'] = isset($this->requestData->isactive)?$this->requestData->isactive:"";
		$siteData['site_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		try
		{
			//-- call model function updateClientSite
			$responseArray = $this->CompanyModel->updateClientSite($siteId,$siteData);
			if(empty($responseArray))
				throw new Exception('Response not get, please try again.');
		}
		catch(Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage(), 'data' => []));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($responseArray);
	}

	//--delete Client site
	public function deleteClientSite()
	{
		//-- get params
	 	$siteId = isset($this->requestData->siteId)?$this->requestData->siteId:"";
		try
		{
			//-- call model function deleteClientSite
			$responseArray = $this->CompanyModel->deleteClientSite($siteId);
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

	//--add Client contact
	public function addClientContact()
	{
		//-- get params
		$conData['con_cltid'] = isset($this->requestData->cltId)?$this->requestData->cltId:"";
		$conData['con_name'] = isset($this->requestData->cnm)?$this->requestData->cnm:"";
		$conData['con_email'] = isset($this->requestData->email)?$this->requestData->email:"";
		$conData['con_mobile1'] = isset($this->requestData->mob1)?$this->requestData->mob1:"";
		$conData['con_mobile2'] = isset($this->requestData->mob2)?$this->requestData->mob2:"";
		$conData['con_fax'] = isset($this->requestData->fax)?$this->requestData->fax:"";
		$conData['con_twitter'] = isset($this->requestData->twitter)?$this->requestData->twitter:"";
		$conData['con_skype'] = isset($this->requestData->skype)?$this->requestData->skype:"";
		$conData['con_default'] = isset($this->requestData->def)?$this->requestData->def:"";
		$conData['con_isactive'] = 1;
		$conData['con_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		try
		{
			//-- call model function addClientContact
			$arrayResponse = $this->CompanyModel->addClientContact($conData);
			if(empty($arrayResponse))
				throw new Exception('Response not get, please try again.');
		} 
		catch (Exception $e)
		{
			echo $this->CommonModel->getJsonData(array('success'=> false, 'message' => $e->getMessage()));
			exit();
		}
		//-- convert arrayResponse to json
		echo $this->CommonModel->getJsonData($arrayResponse);	
	}
	
	//-get Client contact list
	public function getClientContactList()
	{
		//-- get params
		$cltId = isset($this->requestData->cltId)?$this->requestData->cltId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		$search = isset($this->requestData->search)?$this->requestData->search:"";
		try
		{
			//-- call model function getClientContactList
			$responseArray = $this->CompanyModel->getClientContactList($cltId,$limit,$index,$search);
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
	
	//--get Client contact details
	public function getClientContactDetail()
	{
		//-- get params
		$conId = isset($this->requestData->conId)?$this->requestData->conId:"";
		try
		{
			//-- call model function getClientContactDetail
			$arrayResponse = $this->CompanyModel->getClientContactDetail($conId);
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

	//--update Client contact
	public function updateClientContact()
	{
		//-- get params
		$conId = isset($this->requestData->conId)?$this->requestData->conId:"";
		$conData['con_name'] = isset($this->requestData->cnm)?$this->requestData->cnm:"";
		$conData['con_email'] = isset($this->requestData->email)?$this->requestData->email:"";
		$conData['con_mobile1'] = isset($this->requestData->mob1)?$this->requestData->mob1:"";
		$conData['con_mobile2'] = isset($this->requestData->mob2)?$this->requestData->mob2:"";
		$conData['con_fax'] = isset($this->requestData->fax)?$this->requestData->fax:"";
		$conData['con_twitter'] = isset($this->requestData->twitter)?$this->requestData->twitter:"";
		$conData['con_skype'] = isset($this->requestData->skype)?$this->requestData->skype:"";
		$conData['con_default'] = isset($this->requestData->def)?$this->requestData->def:"";
		$conData['con_isactive'] = isset($this->requestData->isactive)?$this->requestData->isactive:"1";
		$conData['con_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		try
		{
			//-- call model function updateClientContact
			$arrayResponse = $this->CompanyModel->updateClientContact($conId,$conData);
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

	//delete Client contact
	public function deleteClientContact()
	{
		//-- get params
		$conId = isset($this->requestData->conId)?$this->requestData->conId:"";
		try
		{
			//-- call model function deleteClientContact
			$arrayResponse = $this->CompanyModel->deleteClientContact($conId);
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

	//--add Company account info
	public function addCompanyAccountType()
	{
		//-- get params
		$accData['acct_compid'] = isset($this->requestData->compId)?$this->requestData->compId:"";
		$accData['acct_type'] = isset($this->requestData->type)?$this->requestData->type:"";
		try
		{
			//-- call model function addCompanyAccountType
			$arrayResponse = $this->CompanyModel->addCompanyAccountType($accData);
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

	public function getCompanyAccountTypeList()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		$search = isset($this->requestData->search)?$this->requestData->search:"";
		try
		{
			//-- call model function getCompanyAccountTypeList
			$responseArray = $this->CompanyModel->getCompanyAccountTypeList($compId,$limit,$index,$search);
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

	//--get client account info details
	public function getCompanyAccountDetails()
	{
		//-- get params
		$accId = isset($this->requestData->accId)?$this->requestData->accId:"";
		try
		{
			//-- call model function getCompanyAccountDetails
			$arrayResponse = $this->CompanyModel->getCompanyAccountDetails($accId);
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

	//--update client account info
	public function updateCompanyAccountType()
	{
		//-- get params
		$accId = isset($this->requestData->accId)?$this->requestData->accId:"";
		$accData['acct_type'] = isset($this->requestData->type)?$this->requestData->type:"";
		try
		{
			//-- call model function updateCompanyAccountType
			$arrayResponse = $this->CompanyModel->updateCompanyAccountType($accId,$accData);
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

	//--delete client account info
	public function deleteCompanyAccountType()
	{
		//-- get params
		$accId = isset($this->requestData->accId)?$this->requestData->accId:"";
		try
		{
			//-- call model function deleteCompanyAccountType
			$arrayResponse = $this->CompanyModel->deleteCompanyAccountType($accId);
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

	//--add company setting
	public function addCompanySetting()
	{
		//-- get params
		$setData['set_compid'] = isset($this->requestData->compId)?$this->requestData->compId:"";
		$setData['set_city'] = isset($this->requestData->city)?$this->requestData->city:"";
		$setData['set_state'] = isset($this->requestData->state)?$this->requestData->state:"";
		$setData['set_country'] = isset($this->requestData->ctry)?$this->requestData->ctry:"";
		$setData['set_email'] = isset($this->requestData->email)?$this->requestData->email:"";
		$setData['set_duration'] = isset($this->requestData->duration)?$this->requestData->duration:"";
		try
		{
			//-- call model function addCompanySetting
			$arrayResponse = $this->CompanyModel->addCompanySetting($setData);
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

	//--get company setting details
	public function getCompanySettingDetails()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		try
		{
			//-- call model function getCompanySettingDetails
			$arrayResponse = $this->CompanyModel->getCompanySettingDetails($compId);
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

	//--update company default location
	public function updateCompanyLocation()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$setData['set_city'] = isset($this->requestData->city)?$this->requestData->city:"";
		$setData['set_state'] = isset($this->requestData->state)?$this->requestData->state:"";
		$setData['set_country'] = isset($this->requestData->ctry)?$this->requestData->ctry:"";
		try
		{
			//-- call model function updateCompanySetting
			$trueMsg = "Company location updated successfully.";
			$falseMsg = "Company location not update, please try again.";
			$arrayResponse = $this->CompanyModel->updateCompanySetting($compId,$setData,$trueMsg,$falseMsg);
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

	//--update company default email
	public function updateCompanyEmail()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$setData['set_email'] = isset($this->requestData->email)?$this->requestData->email:"";
		$setData['set_bcc'] = isset($this->requestData->bcc)?$this->requestData->bcc:"";
		try
		{
			//-- call model function updateCompanySetting
			$trueMsg = "Company email updated successfully.";
			$falseMsg = "Company email not update, please try again.";
			$arrayResponse = $this->CompanyModel->updateCompanySetting($compId,$setData,$trueMsg,$falseMsg);
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

	//--update company default job duration
	public function updateCompanyJobDuration()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$setData['set_duration'] = isset($this->requestData->duration)?$this->requestData->duration:"";
		try
		{
			//-- call model function updateCompanySetting
			$trueMsg = "Company job duration updated successfully.";
			$falseMsg = "Company job duration not update, please try again.";
			$arrayResponse = $this->CompanyModel->updateCompanySetting($compId,$setData,$trueMsg,$falseMsg);
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

	//--for add company tax type
	public function addCompanyTax()
	{
		//-- get params
		$taxData['tax_compid'] = isset($this->requestData->compId)?$this->requestData->compId:"";
		$taxData['tax_label'] = isset($this->requestData->label)?$this->requestData->label:"";
		$taxData['tax_isactive'] = 1;
		try
		{
			//-- call model function addCompanyTax
			$arrayResponse = $this->CompanyModel->addCompanyTax($taxData);
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

	//--get tax list of comapny
	public function getTaxList()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		$search = isset($this->requestData->search)?$this->requestData->search:"";
		try
		{
			//-- call model function getTaxList
			$responseArray = $this->CompanyModel->getTaxList($compId,$limit,$index,$search);
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

	//--get tax details
	public function getTaxDetail()
	{
		//-- get params
		$taxId = isset($this->requestData->taxId)?$this->requestData->taxId:"";
		try
		{
			//-- call model function getTaxDetail
			$arrayResponse = $this->CompanyModel->getTaxDetail($taxId);
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

	//--update tax type
	public function updateCompanyTax()
	{
		//-- get params
		$taxId = isset($this->requestData->taxId)?$this->requestData->taxId:"";
		$taxData['tax_label'] = isset($this->requestData->label)?$this->requestData->label:"";
		$taxData['tax_isactive'] = isset($this->requestData->isactive)?$this->requestData->isactive:"";
		try
		{
			//-- call model function updateCompanyTax
			$arrayResponse = $this->CompanyModel->updateCompanyTax($taxId,$taxData);
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
	
	//delete company tax type
	public function deleteTaxType()
	{
		//-- get params
		$taxId = isset($this->requestData->taxId)?$this->requestData->taxId:"";
		try
		{
			//-- call model function deleteTaxType
			$arrayResponse = $this->CompanyModel->deleteTaxType($taxId);
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

	//--update company currrency
	public function updateCurrency()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$cur = isset($this->requestData->cur)?$this->requestData->cur:"";
		try
		{
			//-- call model function updateCurrency
			$arrayResponse = $this->CompanyModel->updateCurrency($compId,$cur);
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

	//--get company setting details by user
	public function getCompanySettingByUser()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		try
		{
			//-- call model function getCompanySettingByUser	
			$arrayResponse = $this->CompanyModel->getCompanySettingByUser($compId);
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