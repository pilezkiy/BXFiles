<?
class CYetiYouTube
{
	function getYoutubeCodeFromURL($url)
	{
		$yCode = "";
		$url = trim($url);
		$url = parse_url($url);
		if(preg_match("/youtube/i",$url["host"]))
		{
			$urlParamsSrc = explode("&",$url["query"]);
			$urlParams = array();
			foreach($urlParamsSrc as $p)
			{
				list($k,$v) = explode("=",$p);
				$urlParams[$k] = $v;
			}
			
			$yCode = false;
			if(!empty($urlParams["v"]))$yCode = $urlParams["v"];
			return $yCode;
		}
		elseif(preg_match("/youtu\.be/i",$url["host"]))
		{ // http://youtu.be/oB5CsThejug
			$yCode = str_replace("/", "", $url["path"]);
			if(!empty($yCode)) return $yCode;
		}
		return false;
	}
	
	function getHQPreviewByLink($url)
	{
		$yCode = self::getYoutubeCodeFromURL($url);
		if($yCode!==false)return "http://i2.ytimg.com/vi/".$yCode."/hqdefault.jpg";//480x360
		else return false;
	}
	
	function getSmallPreviewByLink($url)
	{
		$yCode = self::getYoutubeCodeFromURL($url);
		if($yCode!==false)return "http://i2.ytimg.com/vi/".$yCode."/default.jpg";//120x90
		else return false;
	}
	
	function insertHTMLVideo($url, $width=480, $height=390)
	{
		$yCode = self::getYoutubeCodeFromURL($url);
		if($yCode!==false)
		{
			?><iframe width="<?=$width?>" height="<?=$height?>" src="//www.youtube.com/embed/<?=$yCode?>" frameborder="0" allowfullscreen></iframe><?
		}
		else return false;
	}
	
	function getViewsCount($url)
	{
		$yCode = self::getYoutubeCodeFromURL($url);
		if($yCode!==false)
		{
			$apiUrl = "http://gdata.youtube.com/feeds/api/videos/".$yCode;
			$str = file_get_contents($apiUrl);
			preg_match("#viewCount=['\"]([0-9]+)['\"]#i",$str,$matches);
			return intval($matches[1]);
		}
		else return false;
	}
	
}
?>