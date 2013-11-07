<?
class CYetiDebug
{
	public static $tStart = 0;
	public static $debugName = "";
	public static function startDebug($debugName = "")
	{
		global $DB;
		$DB->ShowSqlStat = true;
		self::$tStart = getmicrotime();
		self::$debugName = $debugName;
	}
	
	public static function endDebug($show = false, $console = true,$showQueries = false)
	{
		global $DB;
		$queryCnt = count($DB->arQueryDebug);
		$queryTime = 0;
		foreach($DB->arQueryDebug as $stat) $queryTime += $stat['TIME'];
		$execTime = getmicrotime() - self::$tStart;
		
		$queryTime = sprintf("%.10f",$queryTime);
		$execTime = sprintf("%.10f",$execTime);
		
		
		if($show)
		{
			?>
			<div class="yeti-debug">
			<h4><?=self::$debugName?></h4>
			Количество запросов: <?=$queryCnt?><br/>
			Время выполнения запросов: <?=$queryTime?> сек.
			Общее время выполнения: <?=$execTime?> сек.
			<?
			if($showQueries)
			{
				?><h5>Запросы:</h5><?
				foreach($DB->arQueryDebug as $stat)
				{
					?>
					<div class='yeti-debug-query'>
						<div><b>Время выполнения запроса: <?=$stat['TIME']?> сек.</b></div>
						<div><?=$stat['QUERY']?></div>
					</div>
					<?
				}
			}
			?>
			</div>
			<?
		}
		
		if($console)
		{
			$arConsole = array(
				"debugName" => self::$debugName,
				"Query count" => $queryCnt,
				"Query time" => $queryTime. "s",
				"All time" => $execTime. "s",
			);

			if($showQueries)
			{
				$arConsole["queries"] = array();
				foreach($DB->arQueryDebug as $stat)
				{
					$arConsole["queries"][] = array(
						"time" => $stat['TIME'],
						"query" => $stat['QUERY'],
					);
				}
			}
			?>
			<script>
			console.log(<?=CUtil::PhpToJsObject($arConsole);?>);
			</script>
			<?
		}
		$DB->ShowSqlStat = false;
		$DB->arQueryDebug = array();
	}
	
}
?>