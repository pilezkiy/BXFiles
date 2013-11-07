<?
class CYetiThread
{
	protected $jobs, $handlers, $results = array();
	
	public function addJob($code, $filename, $args = array())
	{
		$this->jobs[$code] = array(
			"filename" => $_SERVER["DOCUMENT_ROOT"]."/".$filename,
			"args" => $args,
		);
	}
	
	public function execute()
	{
		foreach($this->jobs as $code => $job)
		{
			$args = array_map("escapeshellarg",$job["args"]);
			$args = join(" ", $args);
			$cmd = "timeout 30 php ".$job["filename"]." ".$args.' 2>&1';
			$this->handlers[$code] = popen($cmd,"r");
			stream_set_blocking($this->handlers[$code],0);
		}
		
		foreach($this->jobs as $code => $job)
		{
			while(!feof($this->handlers[$code]))
			{
				$this->results[$code] .= fread($this->handlers[$code],4096);
			}
		}
		
		foreach($this->jobs as $code => $job)
		{
			pclose($this->handlers[$code]);
		}
	}
	
	public function getResults()
	{
		return $this->results;
	}
}

/*
$thread = new CYetiThread;
for($i = 0; $i < 500;$i++)
{
	$thread->addJob("job_code_".$i,"bigfunction.php");
}
$thread->execute();
foreach($thread->getResults() as $job_code => $response)
{
	//echo $response;
}
*/
?>