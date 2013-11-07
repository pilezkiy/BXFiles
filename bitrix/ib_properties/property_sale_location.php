<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

AddEventHandler("iblock", "OnIBlockPropertyBuildList", array("CIBlockPropertySaleLocation", "GetUserGroupsDescription"));

class CIBlockPropertySaleLocation
{
	function GetUserGroupsDescription()
	{
		return array(
			"PROPERTY_TYPE"		=>"N",
			"USER_TYPE"		=>"SaleLocation",
			"DESCRIPTION"		=>"Привязка к местоположению (sale)",
			"GetPublicViewHTML"	=>array("CIBlockPropertySaleLocation","GetPublicViewHTML"),
			"GetAdminListViewHTML"	=>array("CIBlockPropertySaleLocation","GetAdminListViewHTML"),
			"GetPropertyFieldHtml"	=>array("CIBlockPropertySaleLocation","GetPropertyFieldHtml"),
			"ConvertToDB"		=>array("CIBlockPropertySaleLocation","ConvertToDB"),
			"ConvertFromDB"		=>array("CIBlockPropertySaleLocation","ConvertFromDB"),
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
		CModule::IncludeModule("sale");
		if($loc = CSaleLocation::GetByID($locID))
		{
			ob_start();
			?><?=$loc["CITY_NAME"]?> (<?=$loc["COUNTRY_NAME"]?>)<?
			$return = ob_get_clean();
		}
		return $return;
	}

	// Редактирование в админке
	function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		$strHTMLControlName["VALUE"] = htmlspecialcharsEx($strHTMLControlName["VALUE"]);
		ob_start();
		CModule::IncludeModule("sale");
		$rs = CSaleLocation::GetList(
				array(
						"COUNTRY_NAME_LANG" => "ASC",
						"CITY_NAME_LANG" => "ASC"
					),
				array("LID" => LANGUAGE_ID),
				false,
				false,
				array()
			);
		
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
				?><option value="<?=$loc["ID"]?>" <?=($loc["ID"] == $value['VALUE']) ? 'selected' : ''?>  ><?=$loc["CITY_NAME"]?> (<?=$loc["COUNTRY_NAME"]?>)</option><?
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
