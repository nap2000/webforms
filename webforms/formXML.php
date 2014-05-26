<?php

	// Functions to generate the form's data
	
	require 'php_code/webforms.php';		// Configuration data
	require 'php_code/form.php';			// Form class
	require 'php_code/formXML_fns.php';	// Functions and variables used by this service
	
	$html = $form->xslt_transform($xml_doc, $jr2HTML5_doc);		//SimpleXMLElement
	$form->fix_media_urls($html);
	$data = $form->xslt_transform($xml_doc, $jr2Data_doc);	//SimpleXMLElement
	$data_to_edit = $form->data_to_edit;
	$data_to_edit_id = $form->data_to_edit_id;

	// Write out the html
?>		
<!DOCTYPE html>
<html lang="en"  class="no-js" 
				<?php if (!empty($form->html_manifest_url)): ?>
					manifest="<?php echo $form->html_manifest_url; ?>"
				<?php endif; ?>
				>
<?php
	require 'elements/start.inc';
		
	// Add the data to the form
?>

	<script type="text/javascript">
		var modelStr = <?php echo json_encode($data->model->asXML());  ?>;
		<?php if (!empty($data_to_edit)): ?>
			var instanceStrToEdit = <?php echo $data_to_edit; ?>;
		<?php else: ?>
			var instanceStrToEdit = undefined;
		<?php endif; ?>
		<?php if (!empty($data_to_edit_id)): ?>
			var instanceStrToEditId = <?php echo $data_to_edit_id; ?>;
		<?php else: ?>
			var instanceStrToEditId = undefined;
		<?php endif; ?>
	</script>
<?php
	require 'elements/start2_prod.inc';
	require 'elements/dialogs.inc';	
	include 'elements/form-header.inc';	
	echo $html->asXML();
	
	// Form footer
	echo("<section class=\"form-footer\">");
	echo("<div class=\"content\">");
	echo("<fieldset class=\"draft question\"><div class=\"option-wrapper\"><label class=\"select\"><input class=\"ignore\" type=\"checkbox\" name=\"draft\"/><span class=\"option-label\">Save as Draft</span></label></div></fieldset>");
	echo("<div class=\"main-controls\">");
	echo("<a class=\"previous-page disabled\" href=\"#\">Back</a>");
	
	if(empty($data_to_edit_id)) {
		echo "<button id=\"submit-form\" class=\"btn btn-primary btn-large\" >Submit</button>";
	} else {
		echo "<button id=\"submit-form-single\" class=\"btn btn-primary btn-large\" >Submit</button>";
	}
	echo("<a class=\"btn btn-primary large next-page\" href=\"#\">Next</span></a>");
	
	echo("</div>");	// main controls
	echo("<a class=\"btn btn-default disabled first-page\" href=\"#\">Return to Beginning</a>");
	echo("<a class=\"btn btn-default disabled last-page\" href=\"#\">Go to End</a>");
	echo("</div");	// content
	echo("</article>");

?>
