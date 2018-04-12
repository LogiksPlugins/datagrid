<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("printDataGrid")) {
	function printDataGrid($report,$newForm,$editForm,$params=[],$dbKey="app") {
		$defaultParams=[
						"slug"=>"type/refid",
						"glink"=>_uri(),
						"add_record"=>"Add Record",
						"edit_record"=>"Edit Record",
						"add_class"=>"btn btn-primary",
						"edit_class"=>"",
						"add_icon"=>"fa fa-plus",
						"edit_icon"=>"fa fa-pencil",
					];
		if($params==null) $params=[];
		$params=array_merge($defaultParams,$params);
		
		$slug=_slug($params['slug']);
		if(!$slug['type']) {
			$slug['type']="report";
		}
		
		$glink=$params['glink'];
		
		if(!file_exists($report)) {
			trigger_error("Report file not found.");
		}
		
		if(!isset($slug['refid'])) {
			$slug['type']="new";
		}
		
		switch($slug['type']) {
			case "report":
				loadModuleLib("reports","api");
				
				$reportConfig=findReport($report);

				if(isset($newForm) || $newForm!=null && strlen($newForm)>2) {
					if(!isset($reportConfig['actions']) || !isset($reportConfig['actions']['addRecord'])) {
						$reportConfig['actions']['addRecord']=[
											"label"=> $params['add_record'],
											"icon"=> "<i class='{$params['add_icon']}'></i>",
											"class"=> $params['add_class'],
									];
					}
				}
				
				if(isset($editForm) || $editForm!=null && strlen($editForm)>2) {
					if(!isset($reportConfig['buttons']) || !isset($reportConfig['buttons']['editRecord'])) {
						$reportConfig['buttons']['editRecord']=[
											"label"=> $params['edit_record'],
											"icon"=> $params['edit_icon'],
											"class"=> $params['edit_class'],
									];
					}
				}
				
				ob_start();
				echo _css("reports");
				echo "<div class='reportholder' style='width:100%;height:100%;'>";
				$a=printReport($reportConfig,$dbKey);
				if(!$a) {
					echo "<h3 align=center>Panel Source Corrupted</h3>";
				}
				echo "</div>";
				echo _js(["filesaver","html2canvas","reports"]);
				$html=ob_get_contents();
				ob_end_clean();

				echo $html;
			break;
			case "new":
				loadModuleLib("forms","api");
				printForm("new",$newForm,$dbKey,[],['gotolink'=>$glink,'reportlink'=>$glink]);
				echo "<script>$(function() {initFormUI()});</script>";
			break;
			case "edit":
				loadModuleLib("forms","api");
				$where=['md5(id)'=>$slug['refid']];//"guid"=>$_SESSION['SESS_GUID']
				printForm("edit",$editForm,$dbKey,$where,['gotolink'=>$glink,'reportlink'=>$glink]);
				echo "<script>$(function() {initFormUI()});</script>";
			break;
		}
		//printArray($slug);
	}
}
?>
<script>
function editRecord(row) {
	hashid=$(row).data("hash");
	uri="<?=_url()?>";
	uri=uri.split("?");
	uriX=uri[0]+"/edit/"+hashid;
	if(uri[1]!=null) uriX+="?"+uri[1];
	window.location=uriX;
}
function addRecord() {
	uri="<?=_url()?>";
	uri=uri.split("?");
	uriX=uri[0]+"/new";
	if(uri[1]!=null) uriX+="?"+uri[1];
	window.location=uriX;
}
</script>