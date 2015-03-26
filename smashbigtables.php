<?php

/*
Row Scarfer chews with big tables, then plays with its food.

This code is distributed under a ________ GNU license. (I'm coding this without a connection at the moment, so I'll need to fill this in.) As usual, no warranty or even useability for a given purpose is implied bla bla bla puppies bla bla bla Lee Abrams bla bla bla angry cats bla bla.

OVERVIEW: The goal of this script is to address a common use case: A simple scraper has gone through a bunch of paginated rows of data, downloading each page -- boilerplate and all. We now simply want to take the largest table on each page and append it to a giant mega-table that we're putting together. Since the last page will almost always have fewer rows than the others, we are for now ignoring it and spitting out a warning.

ASSUMPTIONS: 
- The files are all named sequentially by number. So, for example, we might smash the tables in 1.txt, 2.txt, 3.txt, 4.txt and 5.txt together into one long file.
- The numbers are integers, starting with 1
- No numbers are skipped
- The files with our desired extension (probably .txt) are ONLY number files. (So, for example, having a readme.txt file in the folder would break the script for now.)

TODO LIST: 
-- DONE: Build the basics of a script that loops through a bunch of files and uses phpQuery to get an array of tables
-- TODO: Now that the file list has been created, SORT IT before doing anything more, so that the list goes 1 2 3 instead of 1 10 11
-- TODO: Figure out which one is the biggest, then select that one as the one you want. 
-- TODO: Then append the contents of that table onto the $existingrows object
-- TODO: Then verify output of the entire script
-- TODO: Put in a real license link up-top. It's currently a blank line. Also get rid of the other cruft in there.
-- TODO: Consider moving more of your init logic into your row-"sorting" method and renaming it more accurately as something like getnumberedfiles()
-- TODO: Implement the class/method/property naming conventions mentioned in Abe's code review (on a different project -- you know the one) 
-- TODO: Then test the same script with a different style of table -- toggling the presence of the thead and tbody tags, for example
-- TODO: If not done already, try to clean up the file names (and references thereto!) in this git repository, which is now known as simple-sequential-scraper-tools
-- TODO: Also include some installation documentation. And move these TODOs out of this file into a master list!
-- TODO: ... or even consider doing actual github requests for each of the remaining changes. (Has the downside of not being locally accessible, in cases where you're developing without internet access)
-- TODO: (Much later): Take in zero, one or two arguments (eventually -- have it as a to-do to start). Default is to operate on any .txt files in the current directory: Arguments can specify other file types (like .txt in my case) or other folder paths.
-- TODO: (Much, *much* later): Have an optional "sniffboilerplate" method that eventually gets called before the appendcontents method. It should return an array containing some combination of selectors that targets our relevant table, ignoring boilerplate. In this way, we can eventually get rid of the warning at the end telling users to append the final table manually. 
*/



//By default, we'll set up our file list to be composed of any .txt files within our current folder.

$extension = '.txt';

class rowscarfer{
	public function init($extension){
		$this->extension = $extension;
		$this->skipfirstrow = true;
		$dir = getcwd();
		include($dir.DIRECTORY_SEPARATOR.'phpquery-onefile.php');
		$allfiles = scandir($dir);
		$ourfiles = array();
		foreach($allfiles as $index => $filename){
			if(strpos($filename,$extension) !== false){
				$ourfiles[] = $filename;
			}else{
				//print("\nDiscarding file $filename");
			}
		}
		$existingrows = array();
		$ourfiles = $this->customrowsort($ourfiles);
		foreach($ourfiles as $file){
			//print("\nUsing file $file");
			$existingrows = $this->appendcontents($existingrows,$file);
			//print("\nyarped");
		}
		$this->existingrows = $existingrows;
	}
	public function customrowsort($ourfiles){
		//For now, we just cut our array down to the debug number. 
		//Once we verify that the problem is still present, we can then fix it.
		$newfiles = array();
		$maxfilenumber = 1;
		$debug = 0;
		foreach($ourfiles as $file){
			//Remember that we're assuming our parent function has already filtered out any files that don't have our desired extension
			//That means we're just pulling out the numbere here, not doing any other filtering.
			$numberonly = preg_replace('/[^0-9]/','',$file);
			if($numberonly > $maxfilenumber){
				$maxfilenumber ++;
			}
		}
		for($i = 1; $i < $maxfilenumber; $i++ ){
			if($debug < 2){

				TIWIS: After upping the debug number from 1 to 2 and piping the output into an html file, you noticed that an incident with PARAYIL FOOD PRODUCTS at the end of page one was now getting doubled. Figure out whether that's a problem with your logic or part of the data. (If the latter, then it's a problem to be solved elsewhere, outside of the scope of this script. If hte form,er we should work on it here.)


				//TODO: Remove the "if" clause for debug restriction once we want to earnestly use this.
				//print("\nWhat we're looking for:".$i.$this->extension);
				$goodfileno = array_search($i.$this->extension,$ourfiles);
				//print("\nGoodfile: ".$ourfiles[$goodfileno]);
				$newfiles[] = $ourfiles[$goodfileno];
			}
			$debug++;
		}
		return($newfiles);
	}
	public function appendcontents($existingrows,$currentfile){
		//We're going to first see which table has longest body of innerHTML text.
		//That will be our way of determining which table is most important (thus USUALLY avoiding boilerplate.)
		//(We were going to use pq($table)->childNodes, but PHPQuery coughs on that. So we'll use static code length instead.)
		//We will then take that table and append the contents onto the existing set of compiled rows for our rowscarfer object. 
		//print("\nProcessing file $currentfile");
		$html = phpQuery::newDocument(file_get_contents($currentfile));
		$tables = array();
		$lengths = array();
		$headerstillcoming = true;
		foreach(pq('table') as $tablenum => $table){
			//print("\nthis is file $currentfile and tablenum $tablenum.");
			$table = pq($table);
			$codelength = strlen($table->html());
			//print("\nLength of code in ^that table:".$codelength);
			$lengths[$tablenum] = $codelength;
			$tables[$tablenum] = $table;
			
		}
		$topval = max($lengths);
		$topkey = array_search($topval,$lengths);
		//print("\nTopkey: $topkey");
		//Ok, so we now know that $tables[$topkey] will give us the table with the most code in it. Next up is to act on that.
		foreach(pq('tr',$tables[$topkey]) as $row){
			if($this->skipfirstrow == true && $headerstillcoming != false){
				$headerstillcoming = false;
			}else{
				//print("\nRow HTML: ");
				//print(pq($row)->html());
				$existingrows[] = "<tr>".pq($row)->html()."</tr>";
			}
		}	
		return($existingrows);
	}
	public function outputrows(){
		//Returns html for all rows in the giant table we've created.
		//(If by some chance we're not done creeating the giant table, this function sits tight until we are. See logic immediately below.)
		$output = "";
		while(!is_array($this->existingrows)){
			sleep(2);
		}
		foreach($this->existingrows as $row){
			$output .= $row;
		}
		return($output);
	}
}


$scarfer = new rowscarfer;
$scarfer->init($extension);
print("<!doctype html><html><head><title></title></head><body><table>");
print($scarfer->outputrows());
print("</table></body>");

?>
