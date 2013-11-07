<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

AddEventHandler("iblock", "OnIBlockPropertyBuildList", array("CIBlockPropertyBannerType", "GetUserGroupsDescription"));

CModule::IncludeModule("advertising");

class CIBlockPropertyBannerType
{
	function GetUserGroupsDescription()
	{
		return array(
			"PROPERTY_TYPE"		=>"S",
			"USER_TYPE"		=>"ITCBannerType",
			"DESCRIPTION"		=>"Привязка к типу баннеров",
			"GetPublicViewHTML"	=>array("CIBlockPropertyBannerType","GetPublicViewHTML"),
			"GetAdminListViewHTML"	=>array("CIBlockPropertyBannerType","GetAdminListViewHTML"),
			"GetPropertyFieldHtml"	=>array("CIBlockPropertyBannerType","GetPropertyFieldHtml"),
			"ConvertToDB"		=>array("CIBlockPropertyBannerType","ConvertToDB"),
			"ConvertFromDB"		=>array("CIBlockPropertyBannerType","ConvertFromDB"),
		);
	}

	// Показ в публичной части (DISPLAY_VALUE)
	function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
	{
		return '';
	}

	// Показ списка в админке
	function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
	{
		$return = "";
		ob_start();
		echo $value["VALUE"];
		$return = ob_get_clean();
		return $return;
	}

	// Редактирование в админке
	function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		$strHTMLControlName["VALUE"] = htmlspecialcharsEx($strHTMLControlName["VALUE"]);
		ob_start();
		$by = "s_name";
		$order = "asc";
		$rs = CAdvType::GetList($by, $order);
		?>
		<select name="<?=$strHTMLControlName["VALUE"]?>">
		<option value="" <?=("" == $value['VALUE']) ? 'selected' : ''?>>Не выбрано</option>
		<?
		while($bt = $rs->fetch())
		{
			?><option value="<?=$bt["SID"]?>" <?=($bt["SID"] == $value['VALUE']) ? 'selected' : ''?>  ><?=$bt["NAME"]?></option><?
		}
		?></select><?

		$return = ob_get_contents();
		ob_end_clean();
		return  $return;
	}

	// Преобразование перед сохранением
	function ConvertToDB($arProperty, $value)
	{
		if(is_array($value["VALUE"]))
		{
			$value["VALUE"] = $value["VALUE"];
		}
		return $value;
	}

	// Преобразование после чтения
	function ConvertFromDB($arProperty, $value)
	{
		$value["VALUE"] = $value["VALUE"];
		return $value;
	}
}
?>
