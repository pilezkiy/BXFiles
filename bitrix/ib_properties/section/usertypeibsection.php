<?
IncludeModuleLangFile(__FILE__);
global $APPLICATION;
$APPLICATION->AddHeadScript("/js/jquery-1.6.1.min.js");

class ITCUserTypeIBlockSection// extends CUserTypeEnum
{
	public static $firstVal = true;
	public static $valCnt = 0;
	
	function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID"	=> "iblock_section_ib",
			"CLASS_NAME"	=> "ITCUserTypeIBlockSection",
			"DESCRIPTION"	=> "Привязка к разделу инфоблока (с выбором инфоблока)",
			"BASE_TYPE"		=> "string",
			"ConvertToDB"	=>	array("ITCUserTypeIBlockSection","ConvertToDB"),
		);
	}
	
	function GetDBColumnType($arUserField)
	{
		global $DB;
		switch(strtolower($DB->type))
		{
			case "mysql":
				return "text";
			case "oracle":
				return "varchar2(2000 char)";
			case "mssql":
				return "varchar(2000)";
		}
	}

	function PrepareSettings($arUserField)
	{
		$height = intval($arUserField["SETTINGS"]["LIST_HEIGHT"]);
		$disp = $arUserField["SETTINGS"]["DISPLAY"];
		if($disp!="CHECKBOX" && $disp!="LIST")$disp = "LIST";
		$iblock_type = $arUserField["SETTINGS"]["IBLOCK_TYPE_ID"];
		$iblock_id = intval($arUserField["SETTINGS"]["IBLOCK_ID"]);
		if($iblock_id <= 0)$iblock_id = "";
		$section_id = intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
		if($section_id <= 0)$section_id = "";
		$active_filter = $arUserField["SETTINGS"]["ACTIVE_FILTER"] === "Y"? "Y": "N";

		return array(
			"DISPLAY" => $disp,
			"LIST_HEIGHT" => ($height < 2? 5: $height),
			"IBLOCK_TYPE" => $iblock_type,
			"IBLOCK_ID" => $iblock_id,
			"DEFAULT_VALUE" => $section_id,
			"ACTIVE_FILTER" => $active_filter,
		);
	}
	
	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		ob_start();
		$VALUE = false;
		if(strlen($arHtmlControl["VALUE"])>0)$VALUE = unserialize(htmlspecialchars_decode($arHtmlControl["VALUE"]));
		?>
		<?
		if(ITCUserTypeIBlockSection::$firstVal)
		{
			?>
			<style>
			#ibs-table{width:500px;}
			#ibs-table th{font-size:8pt;text-align:left;}
			</style>
			<script>
			$(function(){
				$("select.utSelectIblock").live("click",function(){
					var name = $(this).attr("name");
					var iblock_id = $(this).val();
					if(iblock_id!=='')
					{
						$.post("/ajax.php?action=utGetSectionList",{"IBLOCK_ID":iblock_id},function(data){
							$("select[rel='"+name+"']").html(data);
						});
					}
				});
				
				$(".utDeleteButton").click(function(){
					var selName = $(this).attr("rel");
					$("select[name='"+selName+"']").val("");
				});
				
			});
			</script>
			<?
		}
		?>
		
		<table id="ibs-table">
		<?if(ITCUserTypeIBlockSection::$firstVal){?><tr><th>Инфоблок</th><th>Раздел</th><th></th></tr><?}?>
		<tr>
			<td style='width:200px;'>
				<select name="<?=$arHtmlControl["NAME"]?>[IBLOCK_ID]"  class='utSelectIblock' style='width:200px;'>
					<option value="">(выберите инфоблок)</option>
					<?
					$ibFilter = array("ACTIVE"=>"Y");
					if($arUserField["SETTINGS"]["IBLOCK_TYPE"])$ibFilter["TYPE"] = $arUserField["SETTINGS"]["IBLOCK_TYPE"];
					
					$rs = CIBlock::GetList(array("SORT"=>"ASC"),$ibFilter);
					while($r = $rs->Fetch())
					{
						?>
						<option value="<?=$r["ID"]?>" <?=$VALUE["IBLOCK_ID"] == $r["ID"]?"selected":""?>><?=$r["NAME"]?></option>
						<?
					}
					?>
				</select>
			</td>
			<td>
				<select name="<?=$arHtmlControl["NAME"]?>[SECTION_ID]" rel="<?=$arHtmlControl["NAME"]?>[IBLOCK_ID]">
					<option value="">(выберите раздел)</option>
					<?
					if($VALUE["IBLOCK_ID"])
					{
						$rs = CIBlockSection::GetList(array("NAME"=>"ASC"),array("ACTIVE"=>"Y","IBLOCK_ID"=>$VALUE["IBLOCK_ID"]),false,array("ID","NAME"));
						while($r = $rs->Fetch())
						{
							?><option value="<?=$r["ID"]?>" <?=$VALUE["SECTION_ID"] == $r["ID"]?"selected":""?> ><?=$r["NAME"]?></option><?
						}
					}
					?>
				</select>
			</td>
			<td>
				<?
				if(is_array($arUserField["VALUE"]) && ITCUserTypeIBlockSection::$valCnt != count($arUserField["VALUE"]) && count($arUserField["VALUE"])>0)
				{
					?><input type="submit" name="apply" rel="<?=$arHtmlControl["NAME"]?>[IBLOCK_ID]" class='utDeleteButton' value="Удалить"/><?
				}
				?>
			</td>
		</tr>
		</table>
		<?
		$return = ob_get_contents();
		ob_end_clean();
		ITCUserTypeIBlockSection::$firstVal = false;
		ITCUserTypeIBlockSection::$valCnt++;
		return  $return;
	}
	
	function OnBeforeSave($arProperty, $value)
	{
		if(is_array($value) && intval($value["IBLOCK_ID"])>0)
		{
			$value = serialize($value);
			return $value;
		}
		return FALSE;
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';

		if($bVarsFromForm)
			$iblock_id = $GLOBALS[$arHtmlControl["NAME"]]["IBLOCK_ID"];
		elseif(is_array($arUserField))
			$iblock_id = $arUserField["SETTINGS"]["IBLOCK_ID"];
		else
			$iblock_id = "";
		
		if($arUserField["SETTINGS"]["IBLOCK_TYPE"])$result.="<tr><td>Сохраненный тип инфоблока:</td><td>".$arUserField["SETTINGS"]["IBLOCK_TYPE"]."</td></tr>";
		
		if(CModule::IncludeModule('iblock'))
		{
			$result .= '
			<tr valign="top">
				<td>'.GetMessage("USER_TYPE_IBSEC_DISPLAY").':</td>
				<td>
					'.GetIBlockDropDownList($iblock_id, $arHtmlControl["NAME"].'[IBLOCK_TYPE_ID]', $arHtmlControl["NAME"].'[IBLOCK_ID]').'
				</td>
			</tr>
			';
		}
		else
		{
			$result .= '
			<tr valign="top">
				<td>'.GetMessage("USER_TYPE_IBSEC_DISPLAY").':</td>
				<td>
					<input type="text" size="6" name="'.$arHtmlControl["NAME"].'[IBLOCK_ID]" value="'.htmlspecialchars($value).'">
				</td>
			</tr>
			';
		}
		return $result;
	}

	function CheckFields($arUserField, $value)
	{
		$aMsg = array();
		return $aMsg;
	}

	
}
//if(CModule::IncludeModule('iblock'))
//{
	
	AddEventHandler("main", "OnUserTypeBuildList", array("ITCUserTypeIBlockSection", "GetUserTypeDescription"));
//}
?>