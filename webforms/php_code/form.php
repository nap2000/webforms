<?php

/*****************************************************************************

This file is part of SMAP.

SMAP is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

SMAP is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with SMAP.  If not, see <http://www.gnu.org/licenses/>.

******************************************************************************/

/*
 * Class to manage the retrieval of Form data from the smap server
 */
class Form {
	public $xml;				// XML element
	public $xml_data;			// XML element
	public $form;
	public $html_manifest_url;	// The URL that will return the manifest for this form
	public $data_to_edit;		// The instance XML of the data to be edited
	public $data_to_edit_id;	// The GUID that identifies a record to be updated uniquely
	//  note the datakeyvalue would not uniquely identify a record as
	//  replaced records can have the same key value
	
	function __construct($user, $server_url, $form_id) {
		$this->user = $user;
		$this->server_url = $server_url;
		$this->form_id = $form_id;
		
		$this->form = '';
		$this->data_to_edit = '';
		$this->data_to_edit_id = '';
	}

	/*
	 * Convert media names into URLs
	*/
	
	function getXML() {

		$form_url = $this->server_url . '/formXML?key=' . $this->form_id . '&user=' . $this->user;

		// Get the manifest URL (if this form modifies existing data then the data keys will be added later)
		$this->html_manifest_url = '/htmlManifest/' . $this->form_id;
		
		user_error('Form Url: ' . $form_url);
			
		$httpRequest_OBJ = new HttpRequest($form_url, HTTP_METH_GET, null);
		$httpRequest_OBJ->setContentType = 'Content-Type : text/xml';
			
		try {
			$result = $httpRequest_OBJ->send();
		} catch (HttpException $ex) {
			echo  'Form retrieval error: ' . $ex . '<br/>';
			error_log('Form retrieval error: ' . $ex, 0);
		}
			
		if ($result->getResponseCode() == 200) {
			$this->xml = new SimpleXMLElement($result->getBody());
			
		} else {
			echo 'formXML response code: ' . $result->getResponseCode() . '</br>';
			error_log('formXML response code: ' . $result->getResponseCode(), 0);
		}
			
	}

	/*
	 * Function to retrieve the data for an existing survey instance
	*/
	function getData($datakey, $datakeyvalue) {
		user_error('Getting data, Data key: ' . $datakey.' : ' . $datakeyvalue);
		$instance_url = $this->server_url . '/instanceXML/' . $this->form_id . '/0?user=' . $this->user .
			'&key=' . $datakey . '&keyval=' . $datakeyvalue;

		// Clear the html manifest URL - Single shot survey updates are not cached
		$this->html_manifest_url = "";
		
		$httpRequest_OBJ = new HttpRequest($instance_url, HTTP_METH_GET, null);
		$httpRequest_OBJ->setContentType = 'Content-Type : text/xml';

		try {
			$result = $httpRequest_OBJ->send();
		} catch (HttpException $ex) {
			echo  'Data retrieval error: ' . $ex . '<br/>';
			error_log('Data retrieval error: ' . $ex, 0);
		}

		if ($result->getResponseCode() == 200) {
			$this->xml_data = new SimpleXMLElement($result->getBody());

			// clear the instance id so it can be passed back from the webForm as the key of the data to be replaced
			$this->data_to_edit_id = json_encode((string)$this->xml_data->meta->instanceID);
			$this->xml_data->meta->instanceID = '';
			
			$this->data_to_edit = json_encode($this->xml_data->asXML());

		} else {
			error_log('InstanceXML response code: ' . $result->getResponseCode(), 0);
		}

	}
	
	/*
	 * Do the actual transform
	 */
	function xslt_transform($xml, $xsl)
	{
		$result = new SimpleXMLElement('<root></root>');
	
		$proc = new XSLTProcessor;
		if (!$proc->hasExsltSupport()) {
			error_log('XSLT Processor at server has no EXSLT Support',0);
			echo 'Error: XSLT Processor at server has no EXSLT Support';
		} else {
			libxml_use_internal_errors(true);
			libxml_clear_errors();
			$proc->importStyleSheet($xsl);
			$start_time = time();
			$output = $proc->transformToXML($xml);
			$errors = libxml_get_errors();
			//libxml_clear_errors();
	
			if($output) {
				$result = simplexml_load_string($output);
			}
			/*
			array_push($errors, (object) array(
				'message' => 'XML to HTML transformation for '.$name.' took '.round((microtime(true) - $start), 2).' seconds',
				'level' => 0) );
			$errors = $this->_error_msg_process($errors);
			echo 'Errors: ' . $errors;
			$result = $this->_add_errors($errors, 'xsltmessages', $result);
			*/
		}
		return $result;
	}
	
	/*
	 * Replace the java Rosa media stems with the url to the filename
	 */
	function fix_media_urls($xml) {
		//echo $sHtml;
		foreach ($xml->xpath('/root/form/descendant::*[@src]') as $el) {
			$src = (string) $el['src'];
			$el['src'] = '/media/'.$src;
		}
	}
}

?>