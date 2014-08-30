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

	$user = null;			// User ident
	$form_id = null;		// Form identifier
	$datakey = null;		// Key to be used to find existing data for update
	$datakeyvalue = null;	// Identify the record to be updated
	$getdata = false;		// Set to true if existing data is to be updated					
	$server_url	= null;		// Protocol and domain name of server
	$instance_url = null;	// Url of the instance data for this form
	
	$jr2HTML5_doc = new DOMDocument;
	$jr2Data_doc = new DOMDocument;
	$xml_doc = new DOMDocument;	// The Form

	/*
	 * Get the request parameters
	 */
	if (!isset($_GET['key'])) {
		header("HTTP/1.0 404 Not Found");
		echo('404 Not Found - Form Id is not set');
		user_error('Form id not set', E_USER_ERROR);
		return;
	}
	$form_id = $_GET['key'];
	
	$user = $_SERVER['REMOTE_USER'];
	
	if (isset($_GET['datakey']) && isset($_GET['datakeyvalue'])) {		// Get the keys that identify a record for update, if they exist
		$datakey = $_GET['datakey'];
		$datakeyvalue = $_GET['datakeyvalue'];
		$getdata = TRUE;
	} 
	
	/*
	 * We may need to call make a web service call to the server to get the data for an instance
	 * normally localhost can be used as the address of the server.  However if appache virtual hosts
	 * are used then the call should be made to the original host name so that it is directed to the correct
	 * virtual host.
	 */
	if($config['virtualhost']) {
		if(empty($_SERVER['HTTPS'])) {
			$protocol = 'http://';
		} else {
			$protocol = 'https://';
		}
		$domain = $_SERVER['SERVER_NAME'];
		$server_url = $protocol.$domain;
	} else {
		$server_url = 'http://localhost';	// smap simplification
	}
	
	/*
	 * Get XSLT documents for the form and the form's model
	 */
	$success = $jr2HTML5_doc->load('xslt/openrosa2html5form_php5.xsl');
	$success = $jr2Data_doc->load('xslt/openrosa2xmlmodel.xsl');
	
	/*
	 * Get the form's data
	 */
	$form = new Form($user, $server_url, $form_id);		// Create a form object to hold data about the form
	$form->getXML($form_id, $server_url);				// Get the xml for the form
	if($getdata) {
		$form->getData($datakey, $datakeyvalue);		// Get the data for this form
	}
	
	/*
	 * Get the form as a document
	 */
	$success = $xml_doc->loadXML($form->xml->asXML());
	
?>