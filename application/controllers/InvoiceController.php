<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class InvoiceController extends CI_Controller
{
	function __construct()
	{
		parent::__construct(); 
		$this->load->model('InvoiceModel');
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

	//--add item
	public function addItem()
	{
		//-- get params
		$itemData['item_compid'] = isset($this->requestData->compId)?$this->requestData->compId:"";
		$itemData['item_name'] = isset($this->requestData->inm)?$this->requestData->inm:"";
		$itemData['item_description'] = isset($this->requestData->ides)?$this->requestData->ides:"";
		$itemData['item_part_no'] = isset($this->requestData->pno)?$this->requestData->pno:"";
		$itemData['item_quantity'] = isset($this->requestData->qty)?$this->requestData->qty:"";
		$itemData['item_rate'] = isset($this->requestData->rate)?$this->requestData->rate:"";
		$itemData['item_discount'] = isset($this->requestData->discount)?$this->requestData->discount:"";
        $itemData['item_type'] = isset($this->requestData->type)?$this->requestData->type:"";
		$itemData['item_isactive'] = 1;
		$itemData['item_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		$itemData['item_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		try
		{
			//-- call model function addItem
			$arrayResponse = $this->InvoiceModel->addItem($itemData);
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

	//-get item list
	public function getItemList()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		$search = isset($this->requestData->search)?$this->requestData->search:"";
		try
		{
			//-- call model function getItemList
			$responseArray = $this->InvoiceModel->getItemList($compId,$limit,$index,$search);
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

	//--get item details
	public function getItemDetail()
	{
		//-- get params
		$itemId = isset($this->requestData->itemId)?$this->requestData->itemId:"";
		try
		{
			//-- call model function getItemDetail
			$arrayResponse = $this->InvoiceModel->getItemDetail($itemId);
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

	//--update item
	public function updateItem()
	{
		//-- get params
		$itemId = isset($this->requestData->itemId)?$this->requestData->itemId:"";
		$itemData['item_name'] = isset($this->requestData->inm)?$this->requestData->inm:"";
		$itemData['item_description'] = isset($this->requestData->ides)?$this->requestData->ides:"";
		$itemData['item_part_no'] = isset($this->requestData->pno)?$this->requestData->pno:"";
		$itemData['item_quantity'] = isset($this->requestData->qty)?$this->requestData->qty:"";
		$itemData['item_rate'] = isset($this->requestData->rate)?$this->requestData->rate:"";
		$itemData['item_discount'] = isset($this->requestData->discount)?$this->requestData->discount:"";
        $itemData['item_isactive'] = isset($this->requestData->isactive)?$this->requestData->isactive:"";;
		$itemData['item_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		try
		{
			//-- call model function updateItem
			$arrayResponse = $this->InvoiceModel->updateItem($itemId,$itemData);
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

	//delete item
	public function deleteItem()
	{
		//-- get params
		$itemId = isset($this->requestData->itemId)?$this->requestData->itemId:"";
		try
		{
			//-- call model function deleteItem
			$arrayResponse = $this->InvoiceModel->deleteItem($itemId);
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

	//--add invoice
	public function addInvoice()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$invData['inv_parentid'] = isset($this->requestData->parentId)?$this->requestData->parentId:"";
		$invData['inv_jobid'] = isset($this->requestData->jobId)?$this->requestData->jobId:"";
		$invData['inv_cltid'] = isset($this->requestData->cltId)?$this->requestData->cltId:"";
		$invData['inv_client_name'] = isset($this->requestData->nm)?$this->requestData->nm:"";
		$invData['inv_client_address'] = isset($this->requestData->adr)?$this->requestData->adr:"";
		$invData['inv_discount'] = isset($this->requestData->discount)?$this->requestData->discount:"";
		$invData['inv_total'] = isset($this->requestData->total)?$this->requestData->total:"";
		$invData['inv_paid'] = isset($this->requestData->paid)?$this->requestData->paid:"";
		$invData['inv_note'] = isset($this->requestData->note)?$this->requestData->note:"";
		$invData['inv_duedate'] = $this->CommonModel->changeTimeInUTC(isset($this->requestData->dueDate)?$this->requestData->dueDate:"");
		$invData['inv_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		$invData['inv_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		$newItem = isset($this->requestData->newItem)?$this->requestData->newItem:array();
		// $newItem = json_decode(
		// 	'[
		// 	 	{"inm":"test11","ides":"very good item","qty":"5","rate":"100","discount":"5","type":"1"},
		// 	 	{"inm":"test12","ides":"very good item","qty":"5","rate":"100","discount":"5","type":"1"},
		// 	 	{"inm":"test13","ides":"","qty":"","rate":"200","discount":"","type":"2"}
		// 	 ]'
		// );
		$itemData = isset($this->requestData->itemData)?$this->requestData->itemData:array();
		// $itemData = json_decode(
		// 	'[
		// 		{"itemId":"1","jobId":"1","itmmId":"","type":"1","rate":"10","qty":"1","discount":"20","tax":[
		// 			{"taxId":"1","txRate":"3"},
		// 			{"taxId":"2","txRate":"3"},
		// 			{"taxId":"3","txRate":"4"}
		// 		  ]
		// 	    },
		// 		{"itemId":"1","jobId":"1","itmmId":"","type":"2","rate":"10","qty":"1","discount":"20","tax":[
		// 			{"taxId":"1","txRate":"3"},
		// 			{"taxId":"2","txRate":"3"},
		// 			{"taxId":"3","txRate":"4"}
		// 		  ]
		// 	    },
		// 		{"itemId":"2","jobId":"1","itmmId":"","type":"2","rate":"10","qty":"1","discount":"20","tax":[
		// 			{"taxId":"1","txRate":"3"},
		// 			{"taxId":"2","txRate":"3"},
		// 			{"taxId":"3","txRate":"4"}
		// 		  ]
		// 	    }

		// 	]'
		// );
		$groupByData = isset($this->requestData->groupByData)?$this->requestData->groupByData:array();
		// $groupByData = json_decode(
		// 	'[
		// 		{"gnm":"Labour","rate":"20","qty":"2","discount":"40"}

		// 	]'
		// );
		try
		{
			$arrayResponse = $this->InvoiceModel->addInvoice($compId,$invData,$newItem,$itemData,$groupByData);
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

	//--get invoice list
	public function getInvoiceList	()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		$search = isset($this->requestData->search)?$this->requestData->search:"";
		try
		{
			//-- call model function getInvoiceList
			$responseArray = $this->InvoiceModel->getInvoiceList($compId,$limit,$index,$search);
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

	//--get invoice details
	public function getInvoiceDetail()
	{
		//-- get params
		$invId = isset($this->requestData->invId)?$this->requestData->invId:"";
		$jobId = isset($this->requestData->jobId)?$this->requestData->jobId:"";
		try
		{
			//-- call model function getInvoiceDetail
			$arrayResponse = $this->InvoiceModel->getInvoiceDetail($invId,$jobId);
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

	//--update invoice
	public function updateInvoice()
	{
		//-- get params
		$invId = isset($this->requestData->invId)?$this->requestData->invId:"";
		$invData['inv_client_name'] = isset($this->requestData->nm)?$this->requestData->nm:"";
		$invData['inv_client_address'] = isset($this->requestData->adr)?$this->requestData->adr:"";
		$invData['inv_discount'] = isset($this->requestData->discount)?$this->requestData->discount:"";
		$invData['inv_total'] = isset($this->requestData->total)?$this->requestData->total:"";
		$invData['inv_paid'] = isset($this->requestData->paid)?$this->requestData->paid:"";
		$invData['inv_note'] = isset($this->requestData->note)?$this->requestData->note:"";
		$invData['inv_duedate'] = $this->CommonModel->changeTimeInUTC(isset($this->requestData->dueDate)?$this->requestData->dueDate:"");
		$invData['inv_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');

		//--get new item data
		$newItem = isset($this->requestData->newItem)?$this->requestData->newItem:array();
		// $newItem = json_decode(
		// '[
		// {"inm":"test111","ides":"very good item","qty":"5","rate":"100","discount":"5","type":"1"},
		// {"inm":"test121","ides":"very good item","qty":"5","rate":"100","discount":"5","type":"1"},
		// {"inm":"test131","ides":"","qty":"","rate":"200","discount":"","type":"2"}
		// ]'
		// );

		//--get item data
		$itemData = isset($this->requestData->itemData)?$this->requestData->itemData:array();
		// $itemData = json_decode(
		// '[
		// {"itemId":"1","jobId":"1","isGroup":"","type":"1","rate":"10","qty":"1","discount":"20","tax":[
		// {"taxId":"1","txRate":"5"},
		// {"taxId":"2","txRate":"3"},
		// {"taxId":"3","txRate":"4"}
		// ]
		// },
		// {"itemId":"1","jobId":"1","isGroup":"","type":"2","rate":"10","qty":"1","discount":"20","tax":[
		// {"taxId":"1","txRate":"5"},
		// {"taxId":"2","txRate":"3"},
		// {"taxId":"3","txRate":"4"}
		// ]
		// },
		// {"itemId":"2","jobId":"1","isGroup":"","type":"2","rate":"10","qty":"1","discount":"20","tax":[
		// {"taxId":"1","txRate":"5"},
		// {"taxId":"2","txRate":"3"},
		// {"taxId":"3","txRate":"4"}
		// ]
		// }

		// ]'
		// );

		//--get labour data
		$groupByData = isset($this->requestData->groupByData)?$this->requestData->groupByData:array();
		// $groupByData = json_decode(
		// '[
		// {"gnm":"Labour","rate":"20","qty":"2","discount":"40"}

		// ]'
		// );
		try
		{
			//-- call model function updateInvoice
			//<<<<<<< .mine
			//$arrayResponse = $this->InvoiceModel->updateInvoice($invId,$invData);
			//||||||| .r1383
			//$arrayResponse = $this->JobModel->updateInvoice($invId,$invData);
			//=======
			$arrayResponse = $this->InvoiceModel->updateInvoice($invId,$invData,$newItem,$itemData,$groupByData);
			//>>>>>>> .r1390
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


	//delete invoice
	public function deleteInvoice()
	{
		//-- get params
		$invId = isset($this->requestData->invId)?$this->requestData->invId:"";
		try
		{
			//-- call model function deleteInvoice
			$arrayResponse = $this->InvoiceModel->deleteInvoice($invId);
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

	//--add inovice template
	public function addInvoiceTemplate()
	{
		//-- get params
		$tplData['tpl_compid'] = isset($this->requestData->compId)?$this->requestData->compId:"";
		$tplData['tpl_html'] = isset($this->requestData->html)?$this->requestData->html:"";
		$tplData['tpl_default'] = isset($this->requestData->def)?$this->requestData->def:"";
		$tplData['tpl_isactive'] = 1;
		try
		{
			//-- call model function addInvoiceTemplate
			$arrayResponse = $this->InvoiceModel->addInvoiceTemplate($tplData);
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

	//-get invoice template list
	public function getInvoiceTemplateList()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		try
		{
			//-- call model function getInvoiceTemplateList
			$responseArray = $this->InvoiceModel->getInvoiceTemplateList($compId,$limit,$index);
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

	//--get invoice template detail
	public function getInvoiceTemplateDetail()
	{
		//-- get params
		$tplId = isset($this->requestData->tplId)?$this->requestData->tplId:"";
		try
		{
			//-- call model function getInvoiceTemplateDetail
			$arrayResponse = $this->InvoiceModel->getInvoiceTemplateDetail($tplId);
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

	//--update invoice template
	public function updateInvoiceTemplate()
	{
		//-- get params
		$tplId = isset($this->requestData->tplId)?$this->requestData->tplId:"";
		$tplData['tpl_html'] = isset($this->requestData->html)?$this->requestData->html:"";
		$tplData['tpl_default'] = isset($this->requestData->def)?$this->requestData->def:"";
		$tplData['tpl_isactive'] = isset($this->requestData->isactive)?$this->requestData->isactive:"";
		try
		{
			//-- call model function updateInvoiceTemplate
			$arrayResponse = $this->InvoiceModel->updateInvoiceTemplate($tplId,$tplData);
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

	//--get invoice setting detail
	public function getInvoiceSettingDetail()
	{
		//--get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		try
		{
			//--call model function getInvoiceSettingDetail
			$arrayResponse = $this->InvoiceModel->getInvoiceSettingDetail($compId);
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

	//--update invoice setting provider
	public function updateInvoiceSettingProvider()
	{
		//-- get params
		$invsetId = isset($this->requestData->invsetId)?$this->requestData->invsetId:"";
		$provider = isset($this->requestData->pro)?$this->requestData->pro:"";
		$isactive = isset($this->requestData->isactive)?$this->requestData->isactive:"";;
		try
		{
			//--call model function updateInvoiceSettingProvider
			$arrayResponse = $this->InvoiceModel->updateInvoiceSettingProvider($invsetId,$provider,$isactive);
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

	//--update invoice setting logo
	public function updateInvoiceSettingLogo()
	{
		//--logic for image upload if get else blank
		if (isset($_FILES['logo']) && !empty($_FILES['logo']['tmp_name'])){
			$logoName = $this->CommonModel->imageUpload('logo','./uploads/companyLogo');
			$this->CommonModel->createThumbnail($logoName);
		}
		else{
			$logoName = '';
		}

		//-- get params
		$invsetId = isset($this->requestData->invsetId)?$this->requestData->invsetId:"";
		$logoType = isset($this->requestData->logoType)?$this->requestData->logoType:"";
		$isactive = isset($this->requestData->isactive)?$this->requestData->isactive:"";
		try
		{
			//--call model function updateInvoiceSettingLogo
			$arrayResponse = $this->InvoiceModel->updateInvoiceSettingLogo($invsetId,$logoName,$logoType,$isactive);
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

	//--update invoice setting travel time
	public function updateInvoiceSettingTravelTime()
	{
		//-- get params
		$invsetId = isset($this->requestData->invsetId)?$this->requestData->invsetId:"";	
		$isactive = isset($this->requestData->isactive)?$this->requestData->isactive:"";
		try
		{
			//--call model function updateInvoiceSettingTravelTime
			$arrayResponse = $this->InvoiceModel->updateInvoiceSettingTravelTime($invsetId,$isactive);
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
}
?>
