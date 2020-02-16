<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("printDataGrid")) {
	function printDataGrid2($panel, $report,$newForm,$editForm,$params=[],$dbKey="app") {
		switch ($panel) {
			case 'dualpane':
				printDataGridDual($report,$newForm,$editForm,$params,$dbKey);
				break;
			case 'default':case 'single':
			default:
				printDataGrid($report,$newForm,$editForm,$params,$dbKey);
				break;
		}
	}

	function datagridProcessParams($params) {
		$defaultParams=[
						"slug"=>"type/refid",
						"glink"=>_uri(),
						"add_record"=>"Add Record",
						"edit_record"=>"Edit Record",
						"add_class"=>"btn btn-primary",
						"edit_class"=>"",
						"add_icon"=>"fa fa-plus",
						"edit_icon"=>"fa fa-pencil",
						"dualpane-width"=>6,
						"dualpane-width-lg"=>8,
						"dualpane-width-sm"=>12
					];
		if($params==null) $params=[];
		$params=array_merge($defaultParams,$params);

		if(isset($params['policy']) && strlen($params['policy'])>0) {
			$allow=checkUserPolicy($params['policy']);
			if(!$allow) {
				trigger_logikserror("Sorry, you are not allowed to access this datagrid");
				return false;
			}
		}
		
		return $params;
	}

	function printDataGridDual($report,$newForm,$editForm,$params=[],$dbKey="app") {
		$params = datagridProcessParams($params);

		if(!file_exists($report)) {
			trigger_error("Report file not found.");
		}

		$glink=$params['glink'];

		$slug=_slug($params['slug']);
		if(!$slug['type']) {
			$slug['type']="report";
		}
		
		if(!isset($slug['refid'])) {
			$slug['type']="new";
		}

		//echo "Hello World";
		// printArray($slug);

		if(!($slug['type']=="" || $slug['type']=="report")) {
			printDataGrid($report,$newForm,$editForm,$params,$dbKey,false);
		?>
<script>
function formsSubmitStatus(formid,msgObj,msgType,gotoLink) {
	if(msgType==null) msgType="SUCCESS";
	else msgType=msgType.toUpperCase();

	if($("form[data-formkey='"+formid+"']").length>0) {
		formBox=$("form[data-formkey='"+formid+"']");

		switch(msgType) {
			case "ERROR":
				lgksToast("<i class='glyphicon glyphicon-ban-circle'></i>&nbsp;"+msgObj);
				formBox.parent().find(".ajaxloading").detach();
				formBox.show();
				return;
			break;
			case "INFO":
				lgksToast("<i class='glyphicon glyphicon-comment'></i>&nbsp;"+msgObj);
			break;
			case "SUCCESS":
				lgksToast("<i class='glyphicon glyphicon-comment'></i>&nbsp;Successfully update database.");
				LGKSReportsInstances[Object.keys(LGKSReportsInstances)[0]].reloadDataGrid();
				$.each(msgObj,function(k,v) {
					try {
						if(formBox.find("input[name='"+k+"'],textarea[name='"+k+"'],select[name='"+k+"']").attr("type")=="file") {
							formBox.find("input[name='"+k+"'],textarea[name='"+k+"'],select[name='"+k+"']").val('');
						} else {
							formBox.find("input[name='"+k+"'],textarea[name='"+k+"'],select[name='"+k+"']").val(v);
						}
					} catch($e) {
					}
				});
			formBox.parent().find(".ajaxloading").detach();
			formBox.show();
			break;
			default:
				lgksToast("<i class='glyphicon glyphicon-info-sign'></i>&nbsp;"+msgObj);
		}
	} else {
		//console.warn(formid+">>"+msgType+">>"+msgObj);
		lgksToast("<i class='glyphicon glyphicon-info-sign'></i>Error with form, try reloading");
	}

	postsubmit=formBox.data('postsubmit');
	if(postsubmit!=null && typeof window['postsubmit']=="function") {
		window['postsubmit'](formid,msgObj,msgType);
	}

	//console.log(formid,msgObj,msgType,gotoLink);
}
</script>
		<?php
			return;
		}

		$lgForm = 12 - (int)$params["dualpane-width-lg"];
		$mdForm = 12 - (int)$params["dualpane-width"];
		$smForm = 12 - (int)$params["dualpane-width-sm"];

		echo "<div class='row-grid'>";
		echo "<div class='col-xs-12 col-sm-12 col-md-{$params["dualpane-width"]} col-lg-{$params["dualpane-width-lg"]} datagrid-column datagrid-column-report'>";
		//Report
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

		if(isset($_REQUEST['tmpl']) && strlen($_REQUEST['tmpl'])>0) {
  			$reportConfig['template']=$_REQUEST['tmpl'];
		} elseif(isset($_COOKIE['RPTVIEW-'.$reportConfig['reportgkey']])) {
  			$reportConfig['template']=$_COOKIE['RPTVIEW-'.$reportConfig['reportgkey']];
		}
		//printArray($reportConfig);

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
		echo "</div>";
		echo "<div class='col-xs-12 col-sm-12 col-md-{$mdForm} col-lg-{$lgForm} datagrid-column datagrid-column-form'>";
		//Form
		echo "<div id='reportTargetFrameTopbar' class='datagridFormFrameTopbar'></div>";
		echo "<div id='reportTargetFrame' class='datagridFormFrame'></div>";
		echo "</div>";
		echo "</div>";
		?>
<style type="text/css">
.datagrid-column {
    padding: 0px;
    margin: 0px;
}
.datagridFormFrame {
	width: 100%;height: 100%;
	margin: 0px;
	padding: 0px;
	border-left: 1px solid #DDD;
}
.datagridFormFrame .formbox-content {
	border: 0px;
    box-shadow: none;
    padding: 20px;
    background: transparent;
}
.datagridFormFrameTopbar {

}
.datagridFormFrameTopbar .modal-header {
    padding: 8px;
}
</style>
<script>
$(function() {
	addRecord();
});
function editRecord(row) {
	hashid=$(row).data("hash");
	uri="<?=_url()?>";
	uri=uri.split("?");
	uriX=uri[0].replace("singlepage","popup").replace("modules","popup")+"/edit/"+hashid;
	if(uri[1]!=null) uriX+="?"+uri[1];

	$("#reportTargetFrame").html("<div class='ajaxloading ajaxloading5'></div>");
	$("#reportTargetFrameTopbar").html('<div class="modal-header"><h4 class="modal-title">'+_ling("Edit Record")+'</h4></div>');
	$("#reportTargetFrame").load(uriX);
}
function addRecord() {
	uri="<?=_url()?>";
	uri=uri.split("?");
	uriX=uri[0].replace("singlepage","popup").replace("modules","popup")+"/new";
	if(uri[1]!=null) uriX+="?"+uri[1];

	$("#reportTargetFrame").html("<div class='ajaxloading ajaxloading5'></div>");
	$("#reportTargetFrameTopbar").html('<div class="modal-header"><h4 class="modal-title">'+_ling("Create Record")+'</h4></div>');
	$("#reportTargetFrame").load(uriX);
}
function infoviewRecord() {
	uri="<?=_url()?>";
	uri=uri.split("?");
	uriX=uri[0].replace("singlepage","popup").replace("modules","popup")+"/details";
	if(uri[1]!=null) uriX+="?"+uri[1];

	$("#reportTargetFrame").html("<div class='ajaxloading ajaxloading5'></div>");
	$("#reportTargetFrameTopbar").html('<div class="modal-header"><h4 class="modal-title">'+_ling("Details of Record")+'</h4></div>');
	$("#reportTargetFrame").load(uriX);
}
</script>
		<?php

	}
	function printDataGrid($report,$newForm,$editForm,$params=[],$dbKey="app") {
		$params = datagridProcessParams($params);

		if(!file_exists($report)) {
			trigger_error("Report file not found.");
		}

		$glink=$params['glink'];

		$slug=_slug($params['slug']);
		if(!$slug['type']) {
			$slug['type']="report";
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
        
        		if(isset($_REQUEST['tmpl']) && strlen($_REQUEST['tmpl'])>0) {
          			$reportConfig['template']=$_REQUEST['tmpl'];
        		} elseif(isset($_COOKIE['RPTVIEW-'.$reportConfig['reportgkey']])) {
          			$reportConfig['template']=$_COOKIE['RPTVIEW-'.$reportConfig['reportgkey']];
        		}
        		//printArray($reportConfig);
        
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
			<?php
			break;
			case "new":
				loadModuleLib("forms","api");
				printForm("new",$newForm,$dbKey,[],['gotolink'=>$glink,'reportlink'=>$glink]);
				//echo "<script>$(function() {initFormUI()});</script>";
			break;
			case "edit":
				loadModuleLib("forms","api");
				$where=['md5(id)'=>$slug['refid']];//"guid"=>$_SESSION['SESS_GUID']
				printForm("edit",$editForm,$dbKey,$where,['gotolink'=>$glink,'reportlink'=>$glink]);
				//echo "<script>$(function() {initFormUI()});</script>";
			break;
			case "details":
				loadModuleLib("infoview","api");
				$where=['md5(id)'=>$slug['refid']];//"guid"=>$_SESSION['SESS_GUID']
				printInfoView($editForm,$dbKey,$where,['gotolink'=>$glink,'reportlink'=>$glink]);
			break;
		}
		//printArray($slug);
	}
}
?>