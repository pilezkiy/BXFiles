<?
define("STOP_STATISTICS", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");

switch($_REQUEST["action"])
{
	case "actionName":
		break;
}
?>