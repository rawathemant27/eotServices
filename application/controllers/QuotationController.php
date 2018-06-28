<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class QuotationController extends CI_Controller
{
	function __construct()
	{
		parent::__construct(); 
		$this->load->model('QuotationModel');
		$this->load->model('CommonModel');

		//--for get params from front end
     	$fileContent = file_get_contents("php://input");
		if(!empty($fileContent))
			$this->requestData = json_decode(file_get_contents("php://input"));
		else 
			$this->requestData = (object)$_REQUEST;
		$this->load->helper('pdf_helper');
	}

	/**
	 * addQuotation
	 * method for add quotation
	 * json
	 */
	public function addQuotation()
	{
		$quoteData = array();
		//-- get params
		$quoteData['quot_compid'] = isset($this->requestData->compId)?$this->requestData->compId:"";
		$quoteData['quot_cltid'] = isset($this->requestData->cltId)?$this->requestData->cltId:"";
		$quoteData['quot_siteid'] = isset($this->requestData->siteId)?$this->requestData->siteId:"";
		$quoteData['quot_conid'] = isset($this->requestData->conId)?$this->requestData->conId:"";
		$quoteData['quot_description'] = isset($this->requestData->des)?$this->requestData->des:"";
		$quoteData['quot_status'] = isset($this->requestData->status)?$this->requestData->status:"1";
		$quoteData['quot_author'] = isset($this->requestData->athr)?$this->requestData->athr:"";
		$quoteData['quot_referencedby'] = isset($this->requestData->referencedby)?$this->requestData->referencedby:"";

		$quoteData['quot_instruction'] = isset($this->requestData->inst)?$this->requestData->inst:"";
		$quoteData['quot_client_name'] = isset($this->requestData->nm)?$this->requestData->nm:"";
		$quoteData['quot_contact_name'] = isset($this->requestData->cnm)?$this->requestData->cnm:"";
		$quoteData['quot_site_name'] = isset($this->requestData->snm)?$this->requestData->snm:"";
		$quoteData['quot_email'] = isset($this->requestData->email)?$this->requestData->email:"";
		$quoteData['quot_mobile1'] = isset($this->requestData->mob1)?$this->requestData->mob1:"";
		$quoteData['quot_mobile2'] = isset($this->requestData->mob2)?$this->requestData->mob2:"";
		$quoteData['quot_address'] = isset($this->requestData->adr)?$this->requestData->adr:"";
		$quoteData['quot_city'] = isset($this->requestData->city)?$this->requestData->city:"";
		$quoteData['quot_state'] = isset($this->requestData->state)?$this->requestData->state:"";
		$quoteData['quot_country'] = isset($this->requestData->ctry)?$this->requestData->ctry:"";
		$quoteData['quot_zipcode'] = isset($this->requestData->zip)?$this->requestData->zip:"";
		$quoteData['quot_isactive'] = 1;
		$quoteData['quot_createdate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		$quoteData['quot_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		$quoteMembers = isset($this->requestData->memIds)?$this->requestData->memIds:array();

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
		
		try
		{
			//-- call model function addQuotation
			$arrayResponse = $this->QuotationModel->addQuotation($quoteData,$quoteMembers,$clientForFuture,$siteForFuture,$contactForFuture,$cltData,$siteData,$conData);
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



	/**
	 * getAdminQuoteList
	 * method for get quotation
	 * json
	 */
	public function getAdminQuoteList()
	{
		//-- get params
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";
		$limit = isset($this->requestData->limit)?$this->requestData->limit:"";
		$index = isset($this->requestData->index)?$this->requestData->index:"";
		$search = isset($this->requestData->search)?$this->requestData->search:"";
		try
		{
			//-- call model function getAdminQuoteList
			$responseArray = $this->QuotationModel->getAdminQuoteList($compId,$limit,$index,$search);
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

	/**
	 * getQuoteDetail
	 * method for get quotation detail
	 * json
	 */
	public function getQuoteDetail()
	{
		//-- get params
		$quotId = isset($this->requestData->quotId)?$this->requestData->quotId:"";
		try
		{
			//-- call model function getQuoteDetail
			$responseArray = $this->QuotationModel->getQuoteDetail($quotId);
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


	/**
	 * changeQuotStatus
	 * method for change quotation status
	 * json
	 */
	public function changeQuotStatus()
	{
		//-- get params
		$quotId = isset($this->requestData->quotId)?$this->requestData->quotId:"";
		$status = isset($this->requestData->status)?$this->requestData->status:"";

		// date formate : dd-mm-yyy hh:mm:ss am/pm
		$dateTime = $this->CommonModel->changeTimeInUTC(isset($this->requestData->dateTime)?$this->requestData->dateTime:"");
		try
		{
			//-- call model function changeQuotStatus
			$arrayResponse = $this->QuotationModel->changeQuotStatus($quotId,$status,$dateTime);
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



	/**
	 * updateQuotation
	 * method for update quotation
	 * json
	 */
	public function updateQuotation()
	{
		$quoteData = array();
		//-- get params
		$quotId = isset($this->requestData->quotId)?$this->requestData->quotId:"";
		$compId = isset($this->requestData->compId)?$this->requestData->compId:"";

		//-- get params
		$quoteData['quot_compid'] = isset($this->requestData->compId)?$this->requestData->compId:"";
		$quoteData['quot_cltid'] = isset($this->requestData->cltId)?$this->requestData->cltId:"";
		$quoteData['quot_siteid'] = isset($this->requestData->siteId)?$this->requestData->siteId:"";
		$quoteData['quot_conid'] = isset($this->requestData->conId)?$this->requestData->conId:"";
		$quoteData['quot_description'] = isset($this->requestData->des)?$this->requestData->des:"";
		$quoteData['quot_status'] = isset($this->requestData->status)?$this->requestData->status:"1";
		$quoteData['quot_author'] = isset($this->requestData->athr)?$this->requestData->athr:"";
		$quoteData['quot_referencedby'] = isset($this->requestData->referencedby)?$this->requestData->referencedby:"";

		$quoteData['quot_instruction'] = isset($this->requestData->inst)?$this->requestData->inst:"";
		$quoteData['quot_client_name'] = isset($this->requestData->nm)?$this->requestData->nm:"";
		$quoteData['quot_contact_name'] = isset($this->requestData->cnm)?$this->requestData->cnm:"";
		$quoteData['quot_site_name'] = isset($this->requestData->snm)?$this->requestData->snm:"";
		$quoteData['quot_email'] = isset($this->requestData->email)?$this->requestData->email:"";
		$quoteData['quot_mobile1'] = isset($this->requestData->mob1)?$this->requestData->mob1:"";
		$quoteData['quot_mobile2'] = isset($this->requestData->mob2)?$this->requestData->mob2:"";
		$quoteData['quot_address'] = isset($this->requestData->adr)?$this->requestData->adr:"";
		$quoteData['quot_city'] = isset($this->requestData->city)?$this->requestData->city:"";
		$quoteData['quot_state'] = isset($this->requestData->state)?$this->requestData->state:"";
		$quoteData['quot_country'] = isset($this->requestData->ctry)?$this->requestData->ctry:"";
		$quoteData['quot_zipcode'] = isset($this->requestData->zip)?$this->requestData->zip:"";
		$quoteData['quot_isactive'] = isset($this->requestData->isactive)?$this->requestData->isactive:"";
		$quoteData['quot_updatedate'] = strtotime(gmdate('Y-m-d h:i:s a').' UTC');
		$quoteMembers = isset($this->requestData->memIds)?$this->requestData->memIds:array();

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
		
		try
		{
			//-- call model function addQuotation
			$arrayResponse = $this->QuotationModel->updateQuotation($quotId, $compId, $quoteData,$quoteMembers,$siteForFuture,$contactForFuture,$siteData,$conData);
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



	/**
	 * deleteQuote
	 * method for delete quotation
	 * json
	 */
	public function deleteQuote()
	{
		//-- get params
		$quotId = isset($this->requestData->quotId)?$this->requestData->quotId:"";
		try
		{
			//-- call model function deleteJob
			$arrayResponse = $this->QuotationModel->deleteQuote($quotId);
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




	/**
	 * sendMail
	 * send mail with attachment
	 * json
	 */
	public function sendMail()
	{
		//-- get params
		$email = isset($this->requestData->email)?$this->requestData->email:"";
		$subject = isset($this->requestData->subject)?$this->requestData->subject:"";
		$message = isset($this->requestData->message)?$this->requestData->message:"";

		try
		{
			//-- email prepare
			$fileName = $this->pdf();
			$arrayResponse = $this->QuotationModel->sendEmail($email,$subject,$message,$fileName);
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



	function pdf()
	{
		    tcpdf();

		    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		    $pdf->SetCreator(PDF_CREATOR);
		    $pdf->SetAuthor('Manikandan');
		    $pdf->SetTitle('Quote');
		    $pdf->SetSubject('Quote');
		    $pdf->SetKeywords('PDF, Quote');

		    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		    $pdf->SetFont('times', '', 15); 

		    $pdf->AddPage();

		    // create some HTML content
		    $now = date("j F, Y");
		    $company_name = 'ABC test company';

		    $user_name = 'Mr. Manikandan';
		    $invoice_ref_id = '2013/12/03';


		    $html = '';
		    $html .= '<table style="padding-top:25px;">
		                <tr>
		                    <td colspan="2" align="left"><h4>Kannama & CO</h4></td>
		                    <td colspan="2" align="right"><h1 style="color:#6C8BB5;">Quote</h1></td>
		                </tr>

		                <tr>
		                    <td colspan="2" align="left"><table><tr><td>'.$user_name.'</td></tr><tr><td>Mob NO : 9791564278</td></tr></table></td>
		                    <td colspan="2" align="right"><table border="1" align="center"><tr><td>Date</td><td>'.$now.'</td></tr><tr><td>Quote</td><td>#0041</td></tr></table></td>
		                </tr>

		                <tr>
		                    <td colspan="2" align="left"><table><tr><td style="background-color: #2B7DB9;color:white;width:75%;">Customer</td></tr><tr><td>Dear senthil kumar,</td></tr><tr><td>39,Jawahar sangam street,Chockalingapuram,Aruppukottai.</td></tr><tr><td><b>Mob no : </b> 9791564821</td></tr></table></td>
		                </tr>


		             </table>';

		    $html .= '<br><br>
		              <table border="1" cellpadding="5">
		                <tr style="background-color: #2B7DB9;color:white;">
		                    <td colspan="4">Quote # Quote 001</td>            
		                </tr>
		                <tr>
		                    <td><b>Product</b></td>
		                    <td><b>Quantity</b></td>
		                    <td><b>Subtotal</b></td>
		                    <td align="right"><b>Amount (Rs.)</b></td>
		                </tr>
		                <tr style="background-color: #C1E1F8;">
		                    <td>Product 1</td>
		                    <td>30</td>
		                    <td>10</td>
		                    <td align="right">300</td>
		                </tr>
		                <tr>
		                    <td>Product 3</td>
		                    <td>15</td>
		                    <td>3</td>
		                    <td align="right">75</td>
		                </tr>
		                <tr style="background-color: #C0E1F8;">
		                    <td>Product 2</td>
		                    <td>15</td>
		                    <td>3</td>
		                    <td align="right">75</td>
		                </tr>
		                <tr>
		                    <td colspan="3" align="right"><b>Sub Total:</b></td>
		                    <td align="right"><b> 375</b></td>
		                </tr>
		                <tr>
		                    <td colspan="3" align="right"><b>GST:</b></td>
		                    <td align="right"><b> 5</b></td>
		                </tr>
		                <tr>
		                    <td colspan="3" align="right"><b>CGST:</b></td>
		                    <td align="right"><b> 10</b></td>
		                </tr>
		                <tr>
		                    <td colspan="3" align="right"><b>Delivery:</b></td>
		                    <td align="right"><b> 100</b></td>
		                </tr>
		                <tr>
		                    <td colspan="3" align="right"><b>Total:</b></td>
		                    <td align="right"><b> 1000</b></td>
		                </tr>
		             </table>';

		    $html = str_replace('{now}',$now, $html);
		    $html = str_replace('{company_name}',$company_name, $html);
		    $html = str_replace('{user_name}',$user_name, $html);
		    $html = str_replace('{invoice_ref_id}',$invoice_ref_id, $html);

		    $pdf->writeHTML($html, true, false, true, false, '');

		    $pdf->lastPage();

		    $fileName = time() . '.pdf';
		    ob_clean();
		    $pdf->Output(FCPATH . 'uploads/' . $fileName, 'F');

		    if (file_exists(FCPATH . 'uploads/' . $fileName)) {
		        return $fileName;
		    } else {
		        return false;
		    }

  }

	
}
?>