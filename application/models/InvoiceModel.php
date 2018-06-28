<?php
class InvoiceModel extends CI_Model
{	
	function __construct()
	{
		parent::__construct();
	}

	public function addItem($itemData)
	{
		$responseArray = array();

		//--check required params
		if (!$itemData['item_compid']) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			//--check part number already exist or not
			$fields = 'item_part_no';
			$tblName = 'eot_item';
			$condition = array('item_part_no' => $itemData['item_part_no']);
			$result = $this->CommonModel->getData($fields,$tblName,$condition);
			if ($result->num_rows()) 
			{
				$responseArray = array("success"=>false,'message'=>'Item part no. already exist, please try with different item.');
			}
            else
            { 
				$itemId = $this->CommonModel->insertData($tblName,$itemData);
				if($itemId)
				{
					$itemResult = $this->getitemDetail($itemId);
					$responseArray = array('success'=> true, 'message'=>'Item added successfully.','data' => $itemResult['data']);
				}
				else
					$responseArray = array("success"=>false,'message'=>'Item not added, please try again.', 'data'=>[]);
		    }
		}
		return $responseArray;
	}

	public function getItemList($compId,$limit,$index,$search)
	{
		$responseArray = array();

		//--check required params
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$this->db->select('SQL_CALC_FOUND_ROWS item_id as itemId,item_name as inm,item_description as ides,item_part_no as pno,item_quantity as qty,item_rate as rate,item_discount as discount,item_type as type,item_isactive as isactive',FALSE);
			$this->db->from('eot_item');
			$this->db->where('item_compid',$compId);
			$this->db->where('item_type!=' ,2);
			if ($search){
				$this->db->like('item_name',$search);
			}
			if ($limit || $index){
				$this->db->limit($limit,$index);
			}
			$this->db->order_by('item_updatedate','desc');
			$itemResult = $this->db->get();

			//for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
			$total_count = $total_record->row();
			$total_count = $total_count->total_count;

			//--final result
			$responseArray = array("success"=>true,'data'=>$itemResult->result_array(),'count'=>$total_count);
		}
		return $responseArray;
	}

	public function getItemDetail($itemId)
	{
		$responseArray = array();

		//--check required params
		if (!$itemId) 
			$responseArray = array('success'=> false, 'message' => 'Item id required.', 'data'=>[]);
		else
		{
			$fields = "item_id as itemId,item_name as inm,item_description as ides,item_part_no as pno,item_quantity as qty,item_rate as rate,item_discount as discount,item_type as type,item_isactive as isactive";
			$tblName = 'eot_item';
			$condition = array('item_id' => $itemId);
			$itemResult = $this->CommonModel->getData($fields,$tblName,$condition);
			if($itemResult->num_rows())
				$responseArray = array('success'=> true, 'data' => $itemResult->row());
			else
				$responseArray = array("success"=>false,'message'=>'Item not get, please try again.', 'data'=>[]);
		}
		return $responseArray; 
	}
	public function updateItem($itemId,$itemData)
	{
		$responseArray = array();

		//--check required params
		if (!$itemId) 
			$responseArray = array('success'=> false, 'message' => 'Item id required.', 'data'=>[]);
		else
		{
			$tblName = 'eot_item';
			$condition = array('item_id' => $itemId);
			$result = $this->CommonModel->updateData($tblName,$itemData,$condition);
			if($result)
			{
				$itemResult = $this->getitemDetail($itemId);
				$responseArray = array('success'=> true, 'message'=>'Item updated successfully.','data' => $itemResult['data']);
			}
			else
				$responseArray = array("success"=>false,'message'=>'Item not update, please try again.', 'data'=>[]);
		}
		return $responseArray;
	}

	public function deleteItem($itemId)
	{
		$this->db->trans_begin();//--db transaction start
		$responseArray = array();

		//--check required params
		if (!$itemId) 
			$responseArray = array('success'=> false, 'message' => 'Item id required.', 'data'=>[]);
		else
		{
			//--check item record in item tax table
			$fields = 'itmm_itemid';
			$tblName = 'eot_item_tax_mm';	
			$condition = array('itmm_itemid' => $itemId);
			$taxResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$taxResult = $taxResult->num_rows();

			//--check item record in job table
			$fields = 'ijmm_itemid';
			$tblName = 'eot_item_job_mm';	
			$condition = array('ijmm_itemid' => $itemId);
			$jobResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$jobResult = $jobResult->num_rows();

			//--check item record in quotation table
			$fields = 'iqmm_itemid';
			$tblName = 'eot_item_quotation_mm';	
			$condition = array('iqmm_itemid' => $itemId);
			$quotationResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$quotationResult = $quotationResult->num_rows();

			if(empty($taxResult) && empty($jobResult) && empty($quotationResult))
			{
				$tblName = 'eot_item';
				$condition = array('item_id' => $itemId);
				$result = $this->CommonModel->deleteData($tblName,$condition);
				if($result)
					$responseArray = array("success"=>true,'message'=>'Item deleted successfully.', 'data'=>[]);
				else
					$responseArray = array("success"=>false,'message'=>'Item not deleted, please try again.', 'data'=>[]);
			}
			else
			{
				$responseArray=	array("success"=>false,'message'=>"You can't delete this item, because it is already used.");
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

	public function addInvoice($compId,$invData,$newItem,$itemData,$groupByData)
	{
		$this->db->trans_begin();//--db transaction start
		$responseArray = array();

		//--check required params
		if (!($invData['inv_jobid'] || $invData['inv_cltid'] || $invData['inv_client_name'])) 
			$responseArray = array('success'=> false, 'message' => 'Job or client required.', 'data'=>[]);
		else if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$tblName = 'eot_invoice';
 			$invData['inv_compid'] = $compId;
			$invId = $this->CommonModel->insertData($tblName,$invData);
			if($invId)
			{
                //--insert data for grouped labour
                $grpId = 0;
                if (count($groupByData)) 
                {
                	$grpData['grp_name'] = $groupByData[0]->gnm;
					$grpData['grp_rate'] = $groupByData[0]->rate;
					$grpData['grp_quantity'] = $groupByData[0]->qty;
					$grpData['grp_discount'] = $groupByData[0]->discount;
					$tblName = 'eot_grouped_worker';	
					$grpId = $this->CommonModel->insertData($tblName,$grpData);
                }
				
				//--insert data in new item
				if (count($newItem)) 
				{
					foreach ($newItem as $key) 	
					{
						//--insert inventory item
						if ($key->type == "1") 
						{
							$item['item_compid'] = $compId;
							$item['item_name'] = $key->inm;
							$item['item_description'] = $key->ides;
							$item['item_quantity'] = $key->qty;
							$item['item_rate'] = $key->rate;
							$item['item_discount'] = $key->discount;
							$item['item_type'] = $key->type;
							$item['item_isactive'] = 1;
							$item['item_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
							$item['item_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
							$tblName = 'eot_item';	
						    $newId = $this->CommonModel->insertData($tblName,$item);

						    //--insert in mapping item job table
							if($newId)
							{
								$newData['ijmm_itemid'] = $newId;
								$newData['ijmm_invid'] = $invId;
								$newData['ijmm_type'] = 1;
								$newData['ijmm_rate'] = $key->rate;
								$newData['ijmm_quantity'] = $key->qty;
								$newData['ijmm_discount'] = $key->discount;
								$tblName1 = 'eot_item_job_mm';	
								$this->CommonModel->insertData($tblName1,$newData);
							}
					    }
						
						//--insert saperate irme data
						if ($key->type == "2") 
						{
							$item['item_compid'] = $compId;
							$item['item_name'] = $key->inm;
							$item['item_rate'] = $key->rate;
							$item['item_type'] = $key->type;
							$item['item_isactive'] = 1;
							$item['item_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
							$item['item_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
							$tblName = 'eot_item';	
						    $this->CommonModel->insertData($tblName,$item);
						}
					}
				}

				//--insert data in item job mapping
				if (count($itemData)) 
				{
					foreach ($itemData as $key) 	
					{
						$data['ijmm_grpid'] = 0;
						$data['ijmm_itemid'] = $key->itemId;
						$data['ijmm_jobid'] = $key->jobId;
						$data['ijmm_invid'] = $invId;
						//$data['ijmm_itmmid'] = $key->itmmId;
						if ($key->isGroup == "1") {
							$data['ijmm_grpid'] = $grpId;
						}
						$data['ijmm_type'] = $key->type;
						$data['ijmm_rate'] = $key->rate;
						$data['ijmm_quantity'] = $key->qty;
						$data['ijmm_discount'] = $key->discount;
						$tblName = 'eot_item_job_mm';	
					    $itemId = $this->CommonModel->insertData($tblName,$data);
                        
                        //--insert data in item job tax mapping  
					    if($itemId)
					    {
					    	foreach ($key->tax as $tx) 
					    	{
					    		$tax['ijtmm_ijmmid'] = $itemId;
					    		$tax['ijtmm_taxid'] = $tx->taxId;
					    		$tax['ijtmm_tax_rate'] = $tx->txRate;
					    		$tblName = 'eot_item_job_tax_mm';	
					            $this->CommonModel->insertData($tblName,$tax);
                            }
					    }
                    }
				}
				$responseArray = array('success'=> true, 'message'=>'Invoice added successfully.', 'data'=>[] );
			}
			else
			{
				$responseArray = array("success"=>false,'message'=>'Invoice not added, please try again.', 'data'=>[]);
			}

			//--use db tansaction
			if($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
				$responseArray = array("success" => false, "message" => 'Something went wrong, please try again.', "data" => '');
			}
			else{
				$this->db->trans_commit();
			} 
		}
		return $responseArray;
	}

	public function getInvoiceList($compId,$limit,$index,$search)
	{
		$responseArray = array();

		//--check required params 
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$invoiceSql = "
			SELECT
				SQL_CALC_FOUND_ROWS inv.inv_id as invId,inv.inv_parentid as parentId,inv.inv_cltid as cltId,inv.inv_jobid as jobId,CASE WHEN inv.inv_client_name !='' THEN inv.inv_client_name ELSE (SELECT clt_name FROM eot_client JOIN eot_invoice ON eot_invoice.inv_cltid = eot_client.clt_id LIMIT 1) END as nm,inv.inv_client_address,inv.inv_discount as discount,inv.inv_total as total,inv.inv_paid as paid,inv.inv_note as note,inv.inv_duedate as duedate,inv.inv_createdate as createdate,job.job_label as label 
			FROM eot_invoice as inv
			LEFT JOIN eot_job as job ON job.job_id = inv.inv_jobid
			WHERE inv.inv_compid = '".$compId."'";
			if ($search){
				$invoiceSql = $invoiceSql . " AND (inv.inv_client_name LIKE '%".$search."%' OR job.job_label LIKE '%".$search."%')";
			}

			$invoiceSql = $invoiceSql . " ORDER BY inv_id DESC";

			if ($limit || $index){
				$invoiceSql = $invoiceSql . " LIMIT ".$index." , ".$limit;
			}
			$invoiceResult = $this->db->query($invoiceSql);

			//for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
			$total_count = $total_record->row();
			$total_count = $total_count->total_count;

			//--final result
			$responseArray = array("success"=>true,'data'=> $invoiceResult->result_array(),'count'=> $total_count);
		}
		return $responseArray;
	}

	public function getInvoiceDetail($invId,$jobId)
	{
		$responseArray = array();

		//--check required params
		if (!($invId || $jobId))
			$responseArray = array('success'=> false, 'message' => 'Invoice or job id required.', 'data'=>[]);
		else
		{
			$invoiceSql = "
			SELECT
				inv.inv_id as invId,inv.inv_parentid as parentId,inv.inv_cltid as cltId,inv.inv_jobid as jobId,CASE WHEN inv.inv_client_name !='' THEN inv.inv_client_name ELSE (SELECT clt_name FROM eot_client JOIN eot_invoice ON eot_invoice.inv_cltid = eot_client.clt_id LIMIT 1) END as nm,inv.inv_client_address,inv.inv_discount as discount,inv.inv_total as total,inv.inv_paid as paid,inv.inv_note as note,inv.inv_duedate as duedate,inv.inv_createdate as createdate,job.job_label as label 
			FROM eot_invoice as inv
			LEFT JOIN eot_job as job ON job.job_id = inv.inv_jobid";

			if ($invId)
				$invoiceSql = $invoiceSql . " WHERE inv.inv_id = '".$invId."'";
			else
				$invoiceSql = $invoiceSql . " WHERE inv.inv_id = '".$jobId."'";
			
			$invoiceResult = $this->db->query($invoiceSql);
			if($invoiceResult->num_rows())
			{
				$data = $invoiceResult->row();

				//--get items
				$this->db->select('ijmm.ijmm_id as ijmmId,ijmm.ijmm_itemid as itemId,ijmm.ijmm_jobid as jobId,ijmm.ijmm_grpid as groupId,ijmm.ijmm_type as type,ijmm.ijmm_rate as rate,ijmm.ijmm_quantity as qty,ijmm.ijmm_discount as discount,item.item_name as inm,job.job_label as label');
				$this->db->from('eot_item_job_mm as ijmm');
				$this->db->join('eot_item as item','item.item_id = ijmm.ijmm_itemid','left');
				$this->db->join('eot_job as job','job.job_id = ijmm.ijmm_jobid','left');
				$this->db->where('ijmm_invid',$data->invId);	
				$itemResult = $this->db->get();
				$itemResult = $itemResult->result_array();

				//--for getting item tax
				$groupId = array();
				$i =0;
				foreach ($itemResult as $key) 
				{
					if ($key['groupId']) {
						$groupId[$i] = $key['groupId'];
					}
					
					$fields = 'ijtmm_id as ijtmmId,ijtmm_ijmmid as ijmmId,ijtmm_taxid as taxId,ijtmm_tax_rate as rate';
					$tblName = 'eot_item_job_tax_mm';	
					$condition = array('ijtmm_ijmmid' => $key['ijmmId']);
					$taxResult = $this->CommonModel->getData($fields,$tblName,$condition);
					$itemResult[$i]['tax'] = $taxResult->result_array();
					$i++;
				}
				$data->itemData = $itemResult;
				
				//--make array of grouped data
				$j = 0;
				if (array_count_values($groupId)) 
				{
					foreach ($groupId as $key) 
					{
						$fields = 'grp_id as grpId,grp_name as gnm,grp_rate as rate,grp_quantity as qty,grp_discount as discount';
						$tblName = 'eot_grouped_worker';		
						$condition = array('grp_id' => $key);
						$grpResult = $this->CommonModel->getData($fields,$tblName,$condition);
						$data->groupData[$j] = $grpResult->result_array();
						$j++;
					}
				}
				$responseArray = array('success'=> true, 'message' => 'Invoice data found.', 'data' => $data);
			}
			else
			{
				$responseArray = array("success"=>false,'message'=>'Invoice not get, please try again.', 'data'=>[]);
			} 
		}
		return $responseArray; 
	}

	public function updateInvoice($invId,$invData,$newItem,$itemData,$groupByData)
	{
		$this->db->trans_begin();//--db transaction start
		$responseArray = array();

		//--check required params
		if (!$invId) 
			$responseArray = array('success'=> false, 'message' => 'Invoice id required.', 'data'=>[]);
		else
		{
			$tblName = 'eot_invoice';
			$condition = array('inv_id' => $invId);
			$result = $this->CommonModel->updateData($tblName,$invData,$condition);
			if($result)
			{
				$invoiceResult = $this->getInvoiceDetail($invId);
				$result = $invoiceResult['data'];
				$compId = $result->compId;

				$itemResult = $invoiceResult['data']->itemData;

				//--first delete previous item tax data 
				$ijmmId = array();
				$i = 0;
				foreach ($itemResult as $key) 
				{
					if ($key['ijmmId']) 
					{
						$ijmmId = $key['ijmmId'];

						$tblName = 'eot_item_job_tax_mm';
						$condition = array('ijtmm_ijmmid' => $ijmmId);
						$this->CommonModel->deleteData($tblName,$condition);
						$i++;
					}
				}	

				//--first delete previous item data
				$tblName = 'eot_item_job_mm';
				$condition = array('ijmm_invid' => $invId);
				$this->CommonModel->deleteData($tblName,$condition);

				//--insert data for grouped labour
				$grpId = 0;
				if (count($groupByData)) 
				{
					$grpData['grp_name'] = $groupByData[0]->gnm;
					$grpData['grp_rate'] = $groupByData[0]->rate;
					$grpData['grp_quantity'] = $groupByData[0]->qty;
					$grpData['grp_discount'] = $groupByData[0]->discount;
					$tblName = 'eot_grouped_worker';	
					$grpId = $this->CommonModel->insertData($tblName,$grpData);
				}

				//--insert data in new item
				if (count($newItem)) 
				{
					foreach ($newItem as $key) 
					{
						//--insert inventory item
						if ($key->type == "1") 
						{
							$item['item_compid'] = $compId;
							$item['item_name'] = $key->inm;
							$item['item_description'] = $key->ides;
							$item['item_quantity'] = $key->qty;
							$item['item_rate'] = $key->rate;
							$item['item_discount'] = $key->discount;
							$item['item_type'] = $key->type;
							$item['item_isactive'] = 1;
							$item['item_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
							$item['item_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
							$tblName = 'eot_item';	
							$this->CommonModel->insertData($tblName,$item);
						}

						//--insert saperate irme data
						if ($key->type == "2") 
						{
							$item['item_compid'] = $compId;
							$item['item_name'] = $key->inm;
							$item['item_rate'] = $key->rate;
							$item['item_type'] = $key->type;
							$item['item_isactive'] = 1;
							$item['item_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
							$item['item_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
							$tblName = 'eot_item';	
							$this->CommonModel->insertData($tblName,$item);
						}
					}
				}

				//--insert data in item job mapping
				if (count($itemData)) 
				{
					foreach ($itemData as $key) 
					{
						$data['ijmm_grpid'] = 0;
						$data['ijmm_itemid'] = $key->itemId;
						$data['ijmm_jobid'] = $key->jobId;
						$data['ijmm_invid'] = $invId;
						//$data['ijmm_itmmid'] = $key->itmmId;
						if ($key->isGroup == "1") {
						$data['ijmm_grpid'] = $grpId;
						}
						$data['ijmm_type'] = $key->type;
						$data['ijmm_rate'] = $key->rate;
						$data['ijmm_quantity'] = $key->qty;
						$data['ijmm_discount'] = $key->discount;
						$tblName = 'eot_item_job_mm';	
						$itemId = $this->CommonModel->insertData($tblName,$data);

						//--insert data in item job tax mapping 
						if($itemId)
						{
							foreach ($key->tax as $tx) 
							{
								$tax['ijtmm_ijmmid'] = $itemId;
								$tax['ijtmm_taxid'] = $tx->taxId;
								$tax['ijtmm_tax_rate'] = $tx->txRate;
								$tblName = 'eot_item_job_tax_mm';	
								$this->CommonModel->insertData($tblName,$tax);
							}
						}
					}
				}
				$responseArray = array('success'=> true, 'message'=>'Invoice updated successfully.','data' => $invoiceResult['data']);
			}
			else
			{
				$responseArray = array("success"=>false,'message'=>'Invoice not update, please try again.', 'data'=>[]);
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

	public function deleteInvoice($invId)
	{
		$this->db->trans_begin();//--db transaction start
		$responseArray = array();

		//--check required params
			if (!$invId) 
		$responseArray = array('success'=> false, 'message' => 'Invoice id required.', 'data'=>[]);
		else
		{
			//--check invoice record in item job table
			$fields = 'ijmm_invid';
			$tblName = 'eot_item_job_mm';	
			$condition = array('ijmm_invid' => $invId);
			$itemResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$itemResult = $itemResult->num_rows();

			//--check invoice record in ledger table
			$fields = 'led_invid';
			$tblName = 'eot_ledger';	
			$condition = array('led_invid' => $invId);
			$ledResult = $this->CommonModel->getData($fields,$tblName,$condition);
			$ledResult = $ledResult->num_rows();

			if(empty($itemResult) && empty($ledResult))
			{
				$tblName = 'eot_invoice';
				$condition = array('inv_id' => $invId);
				$result = $this->CommonModel->deleteData($tblName,$condition);
				if($result)
					$responseArray = array("success"=>true,'message'=>'Invoice deleted successfully.', 'data'=>[]);
				else
					$responseArray = array("success"=>false,'message'=>'Invoice not deleted, please try again.', 'data'=>[]);
			}
			else
			{
				$responseArray=	array("success"=>false,'message'=>"You can't delete this tax, because it is already used.", 'data'=>[]);
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

	public function addInvoiceTemplate($tplData)
	{
		$responseArray = array();

		//--check required params
		if (!$tplData['tpl_compid']) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$tblName = 'eot_invoice_template';

			//--logic if default template get true
			if ($tplData['tpl_default']){
				$data = array('tpl_default' => 0);
				$condition = array('tpl_default' => 1);
				$this->CommonModel->updateData($tblName,$data,$condition);
			}
			$tplId = $this->CommonModel->insertData($tblName,$tplData);
			if($tplId)
				$responseArray = array('success'=> true, 'message'=>'Invoice template added successfully.','data' => []);
			else
				$responseArray = array("success"=>false,'message'=>'Invoice template not added, please try again.', 'data'=>[]);
		}
		return $responseArray;
	}

	public function getInvoiceTemplateList($compId,$limit,$index)
	{
		$responseArray = array();

		//--check required params
		if (!$compId) 
			$responseArray = array('success'=> false, 'message' => 'Company id required.', 'data'=>[]);
		else
		{
			$this->db->select('SQL_CALC_FOUND_ROWS tpl_id as tplId,tpl_compid as compId,tpl_html as html,tpl_default as def,tpl_isactive as isactive',FALSE);
			$this->db->from('eot_invoice_template');
			$this->db->where("(tpl_compid = $compId OR tpl_compid = 0)");
			if ($limit || $index){
				$this->db->limit($limit,$index);
			}
			$this->db->order_by('tpl_id','desc');
			$tplResult = $this->db->get();

			//for pagignation
			$total_record = $this->db->query("SELECT FOUND_ROWS() as total_count");
			$total_count = $total_record->row();
			$total_count = $total_count->total_count;

			//--final result
			$responseArray = array("success"=>true,'data'=>$tplResult->result_array(),'count'=>$total_count);
		}
		return $responseArray;
	}

	public function getInvoiceTemplateDetail($tplId)
	{
		$responseArray = array();

		//--check required params
		if (!$tplId) 
			$responseArray = array('success'=> false, 'message' => 'Invoice template id required.', 'data'=>[]);
		else
		{
			$fields = "tpl_id as tplId,tpl_html as html,tpl_default as def,tpl_isactive as isactive";
			$tblName = 'eot_invoice_template';
			$condition = array('tpl_id' => $tplId);
			$tplResult = $this->CommonModel->getData($fields,$tblName,$condition);
			if($tplResult->num_rows())
				$responseArray = array('success'=> true, 'data' => $tplResult->row());
			else
				$responseArray = array("success"=>false,'message'=>'Invoice template not get, please try again.', 'data'=>[]);
		}
		return $responseArray; 
	}

	public function updateInvoiceTemplate($tplId,$tplData)
	{
		$responseArray = array();

		//--check required params
		if (!$tplId) 
			$responseArray = array('success'=> false, 'message' => 'Invoice template id required.', 'data'=>[]);
		else
		{
			$tblName = 'eot_invoice_template';

			//--logic if default template get true
			if ($tplData['tpl_default']){
				$data = array('tpl_default' => 0);
				$condition = array('tpl_default' => 1);
				$this->CommonModel->updateData($tblName,$data,$condition);
			}
			$condition = array('tpl_id' => $tplId);
			$result = $this->CommonModel->updateData($tblName,$tplData,$condition);
			if($result)
				$responseArray = array('success'=> true, 'message'=>'Invoice template updated successfully.','data' => []);
			else
				$responseArray = array("success"=>false,'message'=>'Invoice template not update, please try again.', 'data'=>[]);
		}
		return $responseArray;
	}

	public function getInvoiceSettingDetail($compId) 
	{
		$responseArray = array();

		//--check required params 
		if (!$compId)
			$responseArray = array('success'=>false,'message'=>'Company id required.','data'=>[]);
		else
		{
			$this->db->select('invset_id as invsetId,CASE WHEN invset_logo_type = "1x" THEN CONCAT("uploads/companyLogo/",invset_value) WHEN invset_logo_type = "2x" THEN CONCAT("uploads/companyLogo/logo2/",invset_value) WHEN invset_logo_type = "3x" THEN CONCAT("uploads/companyLogo/logo3/",invset_value) ELSE invset_value END as val,invset_logo_type as logoType,invset_isactive as isactive');
			$this->db->from('eot_invoice_setting');
			$this->db->where('invset_compid',$compId);
			$invsetResult = $this->db->get();	
			if($invsetResult->num_rows())
				$responseArray = array('success'=>true,'message'=>'Invoice setting data found.','data'=>$invsetResult->result_array());
			else
				$responseArray = array("success"=>false,'message'=>'Invoice setting not get, please try again.','data'=>[]);
		}
		return $responseArray; 
	}

	public function updateInvoiceSettingProvider($invsetId,$provider,$isactive)
	{
		$responseArray = array();

		//--check required params
		if (!$invsetId)
			$responseArray = array('success'=>false,'message'=>'Invoice setting id required.','data'=>[]);
		else
		{
			$tblName = 'eot_invoice_setting';
			$invsetData['invset_value'] = $provider;
			$invsetData['invset_isactive'] = $isactive;
			$condition = array('invset_id' => $invsetId);
			$updateResult = $this->CommonModel->updateData($tblName,$invsetData,$condition);
			if($updateResult)
				$responseArray = array('success'=> true,'message'=>'Invoice setting updated successfully.','data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Invoice setting not update, please try again.','data'=>[]);
		}
		return $responseArray;
	}

	public function updateInvoiceSettingLogo($invsetId,$logo,$logoType,$isactive)
	{
		$responseArray = array();

		//--check required params
		if (!$invsetId)
			$responseArray = array('success'=>false,'message'=>'Invoice setting id required.','data'=>[]);
		else
		{
			if (isset($logo)) 
			{
				$fields = 'invset_value';
				$tblName = 'eot_invoice_setting';
				$condition = array('invset_id' => $invsetId);
				$invsetResult = $this->CommonModel->getData($fields,$tblName,$condition);
				$invsetResult = $invsetResult->row();
				$logoName = $invsetResult->invset_value;
				if ($logoName!= '')
				{
					//--delete all three images from folder
					unlink('./uploads/companyLogo/'.$logoName);
					unlink('./uploads/companyLogo/logo2/'.$logoName);
					unlink('./uploads/companyLogo/logo3/'.$logoName);
				}
			}

			$tblName = 'eot_invoice_setting';
			$invsetData['invset_value'] = $logo;
			$invsetData['invset_logo_type'] = $logoType;
			$invsetData['invset_isactive'] = $isactive;
			$condition = array('invset_id' => $invsetId);
			$updateResult = $this->CommonModel->updateData($tblName,$invsetData,$condition);
			
			if($updateResult)
				$responseArray = array('success'=> true,'message'=>'Invoice setting logo updated successfully.','data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Invoice setting logo not update, please try again.','data'=>[]);
		}
		return $responseArray;
	}

	public function updateInvoiceSettingTravelTime($invsetId,$isactive)
	{
		$responseArray = array();

		//--check required params
		if (!$invsetId)
			$responseArray = array('success'=>false,'message'=>'Invoice setting id required.','data'=>[]);
		else
		{
			$tblName = 'eot_invoice_setting';
			$invsetData['invset_isactive'] = $isactive;
			$condition = array('invset_id' => $invsetId);
			$updateResult = $this->CommonModel->updateData($tblName,$invsetData,$condition);
			if($updateResult)
				$responseArray = array('success'=> true,'message'=>'Invoice setting updated successfully.','data'=>[]);
			else
				$responseArray = array("success"=>false,'message'=>'Invoice setting not update, please try again.','data'=>[]);
		}
		return $responseArray;
	}
}
?>
