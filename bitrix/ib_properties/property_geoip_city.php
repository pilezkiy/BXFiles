<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

AddEventHandler("iblock", "OnIBlockPropertyBuildList", array("CIBlockPropertyGeoIPCity", "GetUserGroupsDescription"));

class CIBlockPropertyGeoIPCity
{
	function GetUserGroupsDescription()
	{
		return array(
			"PROPERTY_TYPE"		=>"N",
			"USER_TYPE"		=>"GeoIPCity",
			"DESCRIPTION"		=>"Привязка к GeoIP местоположению",
			"GetPublicViewHTML"	=>array("CIBlockPropertyGeoIPCity","GetPublicViewHTML"),
			"GetAdminListViewHTML"	=>array("CIBlockPropertyGeoIPCity","GetAdminListViewHTML"),
			"GetPropertyFieldHtml"	=>array("CIBlockPropertyGeoIPCity","GetPropertyFieldHtml"),
			"ConvertToDB"		=>array("CIBlockPropertyGeoIPCity","ConvertToDB"),
			"ConvertFromDB"		=>array("CIBlockPropertyGeoIPCity","ConvertFromDB"),
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
		$locID = intval($value["VALUE"]);
		global $DB;
		$q = "select * from b_stat_city where NAME <> '' AND `ID`='".$locID."' ORDER BY NAME ASC";
		if($loc = $DB->Query($q)->fetch())
		{
			ob_start();
			?><?=$loc["NAME"]?> (<?=$loc["REGION"]?>) <?=$loc["COUNTRY_ID"]?><?
			$return = ob_get_clean();
		}
		return $return;
	}

	// Редактирование в админке
	function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		$strHTMLControlName["VALUE"] = htmlspecialcharsEx($strHTMLControlName["VALUE"]);
		ob_start();
		
		global $DB;
		$q = "select * from b_stat_city where NAME <> '' ORDER BY NAME ASC";
		$rs = $DB->Query($q);
		$locations = array();
		while($r = $rs->Fetch())
		{
			$locations[] = $r;
		}
		?>
		<select name="<?=$strHTMLControlName["VALUE"]?>">
			<option value="" <?=("" == $value['VALUE']) ? 'selected' : ''?>>Не выбрано</option>
			<?
			foreach($locations as $loc)
			{
				?><option value="<?=$loc["ID"]?>" <?=($loc["ID"] == $value['VALUE']) ? 'selected' : ''?>  ><?=$loc["NAME"]?> (<?=$loc["REGION"]?>) <?=$loc["COUNTRY_ID"]?></option><?
			}
			?>
		</select>
		<?
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
