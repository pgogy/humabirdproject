<?PHP

	class twitter_time_slice extends data{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
			
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Twitter setup and harvest";
			$classification->link = "?data=twitter_time_slice";
			$classification->name = "Twitter time slice";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='look/" . $theme . "/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='look/" . $theme . "/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("data/twitter_setup", "Twitter time slice") . "</h2>
						   <ul>
								<li>
									<a href='?data=twitter_time_slice&action=instructions'>" . $this->language->translate("data/twitter_time_slice", "Instructions") . "</a>
								</li>
								<li>
									<a href='?data=twitter_time_slice&action=slice_file'>" . $this->language->translate("data/twitter_time_slice", "Cut a twitter file according to time") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("data/twitter_time_slice", "help");
			
			return $output . "<p><a href='?data=twitter_time_slice'>" . $this->language->translate("data/twitter_time_slice", "Return to Twitter Harvest") . "</a></p>";
				
		}
		
		private function slice_file(){
				
			if(count($_POST)!==0){
				
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				$time = array();
				
				if(isset($_POST['time_choose'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['plaintextfile']));
					
					$select = "";
					
					$timestore = array();
					
					foreach($data as $tweet){
					
						$time = explode(" ", $tweet->created_at);
					
						$clock = explode(":", $time[3]);
						
						switch($time[1]){
						
							case "Jan" : $month = 1; break;
							case "Feb" : $month = 2; break;
							case "Mar" : $month = 3; break;
							case "Apr" : $month = 4; break;
							case "May" : $month = 5; break;
							case "Jun" : $month = 6; break;
							case "Jul" : $month = 7; break;
							case "Aug" : $month = 8; break;
							case "Sep" : $month = 9; break;
							case "Oct" : $month = 10; break;
							case "Nov" : $month = 11; break;
							case "Dec" : $month = 12; break;
						
						}
						
						$seconds = mktime($clock[0],$clock[1],$clock[2],$month,$time[2],array_pop($time));
					
						$timestore[$seconds] = $tweet->created_at;
					
					}
					
					arsort($timestore);
					
					foreach($timestore as $time => $timetext){
					
						$select .= "<option value='" . $time . "'>" . $timetext . "</option>";
					
					}
					
					$output = "<h2>" . $this->language->translate("data/twitter_time_slice", "Choose time periods to remove") . "</h2>
									<form enctype='multipart/form-data' action='' method='POST'>";
					
					$output .= "<label>" . $this->language->translate("data/twitter_time_slice", "Start") . "</label>";

					$output .= "<select name='start'>" . $select . "</select>";
	
					$output .= "<label>" . $this->language->translate("data/twitter_time_slice", "End") . "</label>";
					
					$output .= "<select name='end'>" . $select . "</select>";
					
					$output .= "<label>" . $this->language->translate("data/twitter_time_slice", "Name for new file") . "</label>";
	
					$output .= "<input type='text' name='time_slices_file_name' />";
					
					$output .= "<input type='hidden' name='plaintextfile' value='" . $_POST['plaintextfile'] . "' />";
											
					$output .=	"<input type='submit' value='" . $this->language->translate("data/twitter_time_slice", "Save") . "' />
									</form>";
					
				}else if(isset($_POST['time_slices_file_name'])){
					
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['plaintextfile']));
					
					$new_data = array();
					
					foreach($data as $tweet){
					
						$time = explode(" ", $tweet->created_at);
					
						$clock = explode(":", $time[3]);
						
						switch($time[1]){
						
							case "Jan" : $month = 1; break;
							case "Feb" : $month = 2; break;
							case "Mar" : $month = 3; break;
							case "Apr" : $month = 4; break;
							case "May" : $month = 5; break;
							case "Jun" : $month = 6; break;
							case "Jul" : $month = 7; break;
							case "Aug" : $month = 8; break;
							case "Sep" : $month = 9; break;
							case "Oct" : $month = 10; break;
							case "Nov" : $month = 11; break;
							case "Dec" : $month = 12; break;
						
						}
						
						$seconds = mktime($clock[0],$clock[1],$clock[2],$month,$time[2],array_pop($time));
					
						if($_POST['start'] <= $seconds){
						
							if($seconds <= $_POST['end']){
						
								//echo date("g:i:s",$_POST['start']) . " " . $_POST['start'] . "--" . " " . date("g:i:s", $seconds) . " " . $seconds . "--". date("g:i:s",$_POST['end']) . " " . $_POST['end'] . "<br />";
						
								if(!isset($new_data[$seconds])){
							
									$new_data[$seconds] = array();						
									
								}
								
								array_push($new_data[$seconds],$tweet);
								
							}
						
						}
					
					}
					
					krsort($new_data);
					
					$new_file = array();
					
					foreach($new_data as $set){
					
						foreach($set as $tweet){
						
							array_push($new_file, $tweet);
						
						}
					
					}
					
					$response = $file_process->update_file("data/twitter_harvest/files/aggregate/" . $_POST['time_slices_file_name'] . ".json", serialize($new_file));
					
					$output = $this->language->translate("data/twitter_time_slice", "File Created");
					
				}
					
				$output .= "<p><a href='?data=twitter_time_slice'>" . $this->language->translate("data/twitter_time_slice", "Return to Twitter time slice") . "</a></p>";
					
				return $output;
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();		
					
				$files = $file_process->read_folder_files_only("data/twitter_harvest/files/aggregate/");
					
				if(count($files)!==0){
			
					$output = "<h2>" . $this->language->translate("data/twitter_time_slice", "Choose a twitter file") . "</h2>
									<form enctype='multipart/form-data' action='' method='POST'>";
						
					$output .= "<select name='plaintextfile'>
										<option>" . $this->language->translate("data/twitter_time_slice", "Select a file") . "</output>";
								
					while($plain = array_pop($files)){
						
							$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
						
					}
		
					$output .=	"</select><br />";
						
					$output .= "<input type='hidden' name='time_choose' />";
											
					$output .=	"<input type='submit' value='" . $this->language->translate("data/twitter_time_slice", "Edit") . "' />
									</form>";
				}else{
					
					$output = "<p>" . $this->language->translate("data/twitter_time_slice", "No files have been uploaded yet") . " - <a href='?data=twitter_time_slice'>" . $this->language->translate("data/twitter_time_slice", "Return to plain text") . "</a></p>";
					
				}
					
				return $output;
				
			}
			
		}		
				
	}