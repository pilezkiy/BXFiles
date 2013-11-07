<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

AddEventHandler("iblock", "OnIBlockPropertyBuildList", array("CIBlockPropertyEWebform", "GetUserGroupsDescription"));

class CIBlockPropertyEWebform
{
	function GetUserGroupsDescription()
	{
		return array(
			"PROPERTY_TYPE"		=>"N",
			"USER_TYPE"		=>"WebFormID",
			"DESCRIPTION"		=>"Привязка к веб-форме",
			"GetPublicViewHTML"	=>array("CIBlockPropertyEWebform","GetPublicViewHTML"),
			"GetAdminListViewHTML"	=>array("CIBlockPropertyEWebform","GetAdminListViewHTML"),
			"GetPropertyFieldHtml"	=>array("CIBlockPropertyEWebform","GetPropertyFieldHtml"),
			"ConvertToDB"		=>array("CIBlockPropertyEWebform","ConvertToDB"),
			"ConvertFromDB"		=>array("CIBlockPropertyEWebform","ConvertFromDB"),
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
		CModule::IncludeModule("form");
		$return = "";
		$webformID = intval($value["VALUE"]);
		if($webform = CForm::GetByID($webformID)->fetch())
		{
			$return = "[".$webform["ID"]."] ".$webform["NAME"];
		}
		return $return;
	}

	// Редактирование в админке
	function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		$strHTMLControlName["VALUE"] = htmlspecialcharsEx($strHTMLControlName["VALUE"]);
		ob_start();
		CModule::IncludeModule("form");
		$rs = CForm::GetList($by="NAME", $order="asc");
		$webforms = array();
		while($r = $rs->Fetch())
		{
			$webforms[] = $r;
		}
		
		?>
		<select name="<?=$strHTMLControlName["VALUE"]?>">
			<option value="" <?=("" == $value['VALUE']) ? 'selected' : ''?>>Не выбрано</option>
			<?
			foreach($webforms as $wf)
			{
				?><option value="<?=$wf["ID"]?>" <?=($wf["ID"] == $value['VALUE']) ? 'selected' : ''?>  >[<?=$wf["ID"]?>] <?=$wf["NAME"]?></option><?
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
