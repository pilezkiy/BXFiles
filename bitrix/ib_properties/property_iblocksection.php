<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $APPLICATION;
class CIBlockPropertySection
{
    function GetUserTypeDescription()
    {
        return array(
			"PROPERTY_TYPE" => "S",
			"USER_TYPE" => "iblock_section_ib",
			"DESCRIPTION" => 'Привязка к разделу инфоблока',
			"GetPropertyFieldHtml" => array("CIBlockPropertySection","GetPropertyFieldHtml"),
			"ConvertToDB" => array("CIBlockPropertySection","ConvertToDB"),
			"ConvertFromDB" => array("CIBlockPropertySection","ConvertFromDB"),
			);
    }

    function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return $value['VALUE'];
    }

    function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {    
        return $value['VALUE'];
    }

    function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
		CUtil::InitJSCore(array('jquery'));
		ob_start();
		$fieldName = $strHTMLControlName["VALUE"];
		?>
		<style>
		.utLabel{font-size:8pt; display:inline-block;width:70px;}
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
		<div>
		<label class="utLabel">Инфоблок:</label>
		<?
		$ibTypes = array();
		$rs = CIBlockType::GetList();
		while($r = $rs->Fetch())
		{
			$ibType = CIBlockType::GetByIDLang($r["ID"], LANG);
			$ibTypes[$ibType["ID"]] = $ibType["NAME"];
		}
		?>
		<select name="<?=$fieldName?>[IBLOCK]" class='utSelectIblock' style='width:200px;'>
			<option value=''>(выберите инфоблок)</option>
			<?
			$rs = CIBlock::GetList(array("iblock_type"=>"ASC","name"=>"ASC"),array("ACTIVE"=>"Y"));
			
			while($r = $rs->Fetch())
			{
				?><option value="<?=$r["ID"]?>" <?=$value["VALUE"]["IBLOCK"]==$r["ID"]?"selected":""?>><?=$ibTypes[$r["IBLOCK_TYPE_ID"]]?> : <?=$r["NAME"]?></option><?
			}
			?>
		</select>
		</div>
		<div>
		<label class="utLabel">Раздел:</label>
		<select name="<?=$fieldName?>[SECTION]" rel="<?=$fieldName?>[IBLOCK]">
			<option value=''>(выберите раздел)</option>
			<?
			$IBLOCK_ID = intval($value["VALUE"]["IBLOCK"]);
			if($IBLOCK_ID>0)
			{
				$rs = CIBlockSection::GetList(array("NAME"=>"ASC"),array("ACTIVE"=>"Y","IBLOCK_ID"=>$IBLOCK_ID),false,array("ID","NAME"));
				while($r = $rs->Fetch())
				{
					?><option value="<?=$r["ID"]?>" <?=$value["VALUE"]["SECTION"]==$r["ID"]?"selected":""?>><?=$r["NAME"]?></option><?
				}
			}
			?>
		</select>
		</div>
		<?
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
    }

    function ConvertToDB($arProperty, $value)
    {
		
		$IID = intval($value["VALUE"]["IBLOCK"]);
		if($IID>0)
		{
			$value["VALUE"] = serialize($value["VALUE"]);
			return $value;
		}
		return;
    }

    function ConvertFromDB($arProperty, $value)
    {   
		//PR($value);
		$value["VALUE"] = unserialize($value["VALUE"]);
		return $value;
    }
}

AddEventHandler('iblock', 'OnIBlockPropertyBuildList', array('CIBlockPropertySection', 'GetUserTypeDescription'));

?>
