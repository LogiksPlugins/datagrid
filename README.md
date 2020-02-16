Datagrid Module
===============

Datagrid combines the power of forms, reports and infoviews into a single module.

Now we have 2 modes for Datagrids,
1. Single Pane Mode
2. Dual Pane Mode



### How to use
loadModule("datagrid");

$newForm = APPROOT."misc/forms/masters/region.json";
$editForm = APPROOT."misc/forms/masters/region.json";
$report = APPROOT."misc/reports/masters/region.json";

printDataGrid2("default",$report,$newForm,$editForm,[
         "slug"=>"module/type/refid"
     ]);
