<?php
	include 'simple_html_dom.php';
	$uni_URL = 'http://www.unipordenone.it';
	$calendar_path = '/mrbs/day.php';

	function GetHTMLCalendar($day, $month, $year, $area){
		global $uni_URL, $calendar_path;
		$post_array = array();

		if($day != null)
			$post_array["day"] = $day;

		if($month != null)
			$post_array["month"] = $month;

		if($year != null)
			$post_array["year"] = $year;

		if($area != null)
			$post_array["area"] = $area;
		
		//url-ify the data for the POST
		$fields_string = "";
		foreach($post_array as $key=>$value) 
		{ 
			$fields_string .= $key.'='.$value.'&'; 
		}

		rtrim($fields_string, '&');
		//print_r($post_array);
		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $uni_URL . $calendar_path);
		curl_setopt($ch,CURLOPT_POST, count($post_array));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//execute post
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);
		return str_get_html($result); ;
	}

	function GetInformations($day, $month, $year, $area){
		$hrtml = GetHTMLCalendar($day, $month, $year, $area);
		$el = $hrtml->find('table[id=timetable]', 0);
		$es = $el;
		$rows = array();
		$array_result = array();

		$hr = 8;
		$min = 0;
		$inc = 30;
		$found = false;
		$aroom = -1;

		foreach ($el->find('tr') as $row ) {
			if (strpos($row->innertext, 'TSAC') !== false)
    			array_push($rows, $row);
		}

		foreach ($rows as $r) {
			foreach ($r->find('td') as $col) {
				if($col->class != "room"){
					//echo "Element: H: ". $hr .":". $min ."</br>" . $col;
					//echo "<hr>";
					if($col->colspan != null){
						//echo "Element: </br>" . $col;
						if(strpos($col->innertext, 'TSAC') != false){

							$item = array();
							$item["room"] = $aroom;
							$item["starth"] = $hr;
							$item["startm"] = $min;
							$item["descrizione"] = $col->find('a', 0)->innertext;
							if(isset($col->find('h3', 0)->innertext)){$item["docente"] = $col->find('h3', 0)->innertext;}
							else{$item["docente"] = "null";}
							$mult = $col->colspan * $inc;
							$hr += floor($mult / 60);
							$min += $mult % 60;
							$item['endh'] = $hr;
							$item['endm'] = $min;

							array_push($array_result, $item);
						}
						else{
							$mult = $col->colspan * $inc;
							$hr += floor($mult / 60);
							$min += $mult % 60;
						}
					}
					else{
						if($min == $inc){
							$hr++;
							$min = 0;
						}
						else{
							$min += $inc;
							if($min == 60){
								$hr++;
								$min = 0;
							}
						}
					}
					
					
				}
				else{
					$aroom = $col->find('h3', 0)->innertext;
				}
			}
		}

		//print_r($array_result);

		return $array_result;

	}

	if(isset($_GET['action'])){
		//echo 'Action: ' . $_GET['action'] . '</br>';
		if($_GET['action'] == 'gettsacday'){
			if(isset($_GET['d']))
			{
				$day = $_GET['d'];
			}
			else
			{
				$now = date("d");
				$day = $now;
			}
			//echo 'Day: ' . $day . '</br>';

			if(isset($_GET['m']))
			{
				$month = $_GET['m'];
			}
			else
			{
				$now = date("m");
				$month = $now;
			}
			//echo 'Month: ' . $month . '</br>';

			if(isset($_GET['y']))
			{
				$year = $_GET['y'];
			}
			else
			{
				$now = date("y");
				$year = $now;
			}

			if(isset($_GET['r']))
			{
				$room = $_GET['r'];
			}
			else
			{
				$room = 'B';
			}

			//echo 'Room: ' . $room . '</br>';

			if($room == 'B'){
				$room = 4;
			}
			else if($room == 'S'){
				$room = 5;
			}
			header('Content-type: text/html');
			print(json_encode(GetInformations($day, $month, $year, $room )));
		}
	}

?>