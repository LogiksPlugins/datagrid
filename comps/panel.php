<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$report=$_ENV['datagrid']['report'];
$form=$_ENV['datagrid']['form_new'];

printDataGrid($report,$form,$form,[]);
?>