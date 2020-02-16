<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$report=$_ENV['datagrid']['report'];
$form=$_ENV['datagrid']['form_new'];

printDataGridDual($report,$form,$form,[
		"slug"=>"module/type/refid"
	]);
?>