<?php 
	//Doing this in PHP because time is limited and old habits die hard.	
	//...and because I can now easily fork a script that scraped RetroBaltimore pages and repurpose that very simple code for a simple 2013 FDA warning-letter scrape.
	$pagecount = 34;
	for($i=0;$i<$pagecount;$i++){	
		$num = rand(405,607);
		$den = rand(13,19);
		$sleepinterval = $num/$den;
		sleep($sleepinterval);
		$currentpage = $i + 1;
		file_put_contents(($i+1).'.txt',file_get_contents('http://www.fda.gov/ICECI/EnforcementActions/WarningLetters/2013/ucm20035841.htm?Page='.($currentpage)));	
		print("\n ... scraping page $currentpage of $pagecount  ... ");
	}
?>
