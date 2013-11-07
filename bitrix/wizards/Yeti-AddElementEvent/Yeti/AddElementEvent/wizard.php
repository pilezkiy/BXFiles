<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); 

class Step1 extends CWizardStep
{ 
    function InitStep()
	{ 
		$this->SetStepID("step1"); 
		$this->SetTitle("Add elements mail event wizard"); 
		$this->SetNextStep($this->stepID);
		$this->SetFinishStep("cancel");
		$this->SetFinishCaption("Quit");
    }
    
    function ShowStep()
	{ 
        $this->content = "Choose action :<br />"; 
        $this->content .= $this->ShowSelectField(
            "action",
            Array(
                "add" => "Create element insert mail event",
                "del" => "Delete some element insert mail event", 
            )
        ); 
    }

    function OnPostForm()
	{
		$wizard = &$this->GetWizard();
		$obStep =& $wizard->GetCurrentStep();
		if($obStep->stepID == 'cancel')return;
		
		$templ = $wizard->GetVar("action");

		if($templ){
			switch ($templ) {
			case 'del':
				$wizard->SetCurrentStep('step2');
				break;
				
			case 'add':
				$wizard->SetCurrentStep('step3');
				break;
			}
		}
    }
} 


/*******************************************
Шаг удаления. выводится список инфоблоков к которым привязаны почтовые события
*******************************************/
class Step2 extends CWizardStep
{ 
    function InitStep()
	{ 
        $this->SetStepID("step2");
        $this->SetTitle("Delete event");
        $this->SetSubTitle("Choose some infoblock");
	
        $this->SetNextStep($this->stepID); // эта же страница (кнопка удалить)
		$this->SetNextCaption("Delete");
		
		$this->SetFinishStep("cancel");
		$this->SetFinishCaption("Quit");
		
		$this->SetPrevStep("step1");
    }
    
    function ShowStep()
	{
		CModule::IncludeModule("iblock");
		$rsLang = CLanguage::GetList();
		$arLang = $rsLang->Fetch();
		$rsET = CEventType::GetList(Array("LID" => $arLang['LID']));
		$IBlock_Event = Array();
		while ($arET = $rsET->Fetch())
		{
			preg_match('#^YETI_ELEMENT_ADD_IBLOCK_([0-9]+)$#smi', $arET['EVENT_NAME'], $id);
			if(!isset($id[1]))continue;
			
			$res = CIBlock::GetByID($id[1]);
			if($ar_res = $res->GetNext())
			{
				$IBlock_Event[] = Array(
					'IBLOCK' => $id[1],
					'EVENTID' => $arET['EVENT_NAME'],
					'IBLOCK_NAME' => $ar_res['NAME']
				);
			}
		}
		
		foreach($IBlock_Event as $line)
		{
			$this->content .= $this->ShowCheckboxField("templ[]", $line['EVENTID'], Array("ID"=>$line['EVENTID'])). '<label for="'.$line['EVENTID'].'">'.$line['IBLOCK_NAME'].' ['.$line['IBLOCK'].']</label><br />';
		}
    }
    
    function OnPostForm()
	{
		$wizard = &$this->GetWizard();
		$templ = $wizard->GetVar("templ");
		
		// для работы кнопок назад и отмена
		$obStep =& $wizard->GetCurrentStep();
		if($this->stepID != $obStep->stepID)return;

		if(!$templ)$this->SetError('Choose template', "templ[]");
		else
		{
			$et = new CEventType;
			foreach($templ as $id)
			{
				$arFilter = Array(
					"TYPE_ID"       => $id, 
				); 
				// отцепляем шаблоны
				$cevmsg = new CEventMessage;
				$rsMess = $cevmsg->GetList($by="site_id", $order="desc", $arFilter);
				while ($msg = $rsMess->GetNext())
				{
					$cevmsg->Delete($msg['ID']);
				}
				// удаляем тип
				$et->Delete($id);
			}
			$wizard->SetCurrentStep($this->stepID);
		}
    }
}

/********************************************
Добавление почтового события на добавление элемента в выбранный инфоблок
********************************************/
class Step3 extends CWizardStep
{
    function InitStep()
	{ 
		$this->SetStepID("step3"); 
        $this->SetTitle("Create new mail event"); 
        $this->SetSubTitle("Choose some infoblock"); 
	
        $this->SetNextStep($this->stepID);
       	$this->SetNextCaption("Create");
       	
       	$this->SetFinishStep("cancel");
		$this->SetFinishCaption("Quit");

        $this->SetPrevStep("step1"); 
    } 
    
    function ShowStep()
	{ 
		CModule::IncludeModule("iblock");
		$rsET = CEventType::GetList(Array());
		$IBlock_Event = Array();
		while ($arET = $rsET->Fetch())
		{
			preg_match('#^YETI_ELEMENT_ADD_IBLOCK_([0-9]+)$#smi', $arET['EVENT_NAME'], $id);
			if(!isset($id[1]))continue;
			$IBlock_Event[] = $id[1];
		}

		$res = CIBlock::GetList(Array("IBLOCK_TYPE_ID"=>"ASC"), Array('ACTIVE'=>'Y'), true); 
	
		$this->content = "Choose infoblocks :<br />";
		$iblocks = Array();
		while($ar_res = $res->Fetch())
		{
			if(in_array($ar_res['ID'], $IBlock_Event))continue;
			$iblocks[$ar_res['ID']] = $ar_res['IBLOCK_TYPE_ID'].": ".$ar_res['NAME']." [".$ar_res['ID']."]";
		}
		$this->content .= $this->ShowSelectField("iblock[]", $iblocks, Array("multiple" => "multiple", "size" => "10", "style" => "width: 550px; height: 225px;")); 
    }

    function OnPostForm()
	{
    	CModule::IncludeModule("iblock");    
		$wizard = &$this->GetWizard();
		$iblocks = $wizard->GetVar("iblock");
		
		// для работы кнопок назад и отмена
		$obStep =& $wizard->GetCurrentStep();
		if($this->stepID != $obStep->stepID)return;

		//$version = $wizard->package->arDescription['VERSION'];
		
		if(!defined("YETI_ADD_IBLOCKELEMENT_WIZ"))
		{
			CopyDirFiles(
				$_SERVER['DOCUMENT_ROOT']. $wizard->GetPath().'/yeti',
				$_SERVER['DOCUMENT_ROOT']. '/bitrix/php_interface/yeti',
				$rewrite = true,
				$recursive = true
			);
			
			$cr = fopen($_SERVER['DOCUMENT_ROOT']. '/bitrix/php_interface/init.php', 'a');
			/*if($cr === false){
				$this->SetError('Can`t install events handler!');
				return;
			}*/
			fwrite($cr,
				'<? $incFile = $_SERVER["DOCUMENT_ROOT"]. "/bitrix/php_interface/yeti/element_add_event.php"; if(file_exists($incFile)) require_once($incFile);?>'
			);
			fclose($cr);
		}
		
		foreach($iblocks as $iblock)
		{
			$tp_code = 'YETI_ELEMENT_ADD_IBLOCK_'. $iblock;	// код шаблона

			$res = CIBlock::GetByID($iblock);		// название шаблона
			$res = $res->GetNext();
			$tp_title = 'Add element to infoblock "'. $res['NAME']. ' ['.$res['ID'].']"';
			
			$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$iblock));
			$tp_desc = "#NAME# - element name\n";
			$tp_desc2 = "Element name: #NAME#\n";
			while ($prop_fields = $properties->GetNext())
			{
				if(empty($prop_fields['CODE'])) $prop_fields['CODE'] = $prop_fields['ID'];
				$tp_desc .= '#PROPERTY_'. $prop_fields['CODE']. '# - '. $prop_fields['NAME']. "\n";
				$tp_desc2 .= $prop_fields['NAME']. ': #PROPERTY_'. $prop_fields['CODE']. "#\n";
			}
			
			$tp_desc .= "#SECTIONS# - element section\n";
			$tp_desc .= "#PREVIEW_TEXT# - anounce\n";
			$tp_desc .= "#DETAIL_TEXT# - detaile text\n";
			$tp_desc .= "#DIRECT_LINK# - adminstrative edit link\n";
			
			$et = new CEventType;
			$rsLang = CLanguage::GetList();
			while ($arLang = $rsLang->Fetch()){
				$et->Add(array(
					"SITE_ID"       => $arLang['LID'],
					"EVENT_NAME"    => $tp_code,
					"NAME"          => $tp_title, 
					"DESCRIPTION"   => $tp_desc 
					)
				);
			}

			$arr["ACTIVE"] = "Y";
			$arr["EVENT_NAME"] = $tp_code; 
			$arr["LID"] = array($res['LID']); 
			$arr["EMAIL_FROM"] = "#DEFAULT_EMAIL_FROM#";
			$arr["EMAIL_TO"] = "#DEFAULT_EMAIL_FROM#";
			$arr["BCC"] = "#BCC#";
			$arr["SUBJECT"] = $tp_title;
			$arr["BODY_TYPE"] = "text"; 
			$arr["MESSAGE"] = "On #SITE_NAME# (#SERVER_NAME#) created new elements\n";
			$arr["MESSAGE"] .= $tp_desc2;
			$emess = new CEventMessage; 
			$emess->Add($arr);
		}
    }
}

/********************************************/
class CancelStep extends CWizardStep
{
    function InitStep()
	{ 
        $this->SetStepID("cancel"); 
        $this->SetCancelStep("cancel"); 
        $this->SetCancelCaption("Close"); 
    } 
    
    function ShowStep()
	{
        $this->content .= "Good bye!";
    } 
}
?> 