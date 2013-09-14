<?PHP

	class twitter_merge extends data{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
			
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Twitter setup and harvest";
			$classification->link = "?data=twitter_merge";
			$classification->name = "Twitter merge";
			
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

				$output = "<h2>" . $this->language->translate("data/twitter_setup", "Twitter merge") . "</h2>
						   <ul>
								<li>
									<a href='?data=twitter_merge&action=instructions'>" . $this->language->translate("data/twitter_merge", "Instructions") . "</a>
								</li>
								<li>
									<a href='?data=twitter_merge&action=slice_file'>" . $this->language->translate("data/twitter_merge", "Merge twitter files") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("data/twitter_merge", "help");
			
			return $output . "<p><a href='?data=twitter_merge'>" . $this->language->translate("data/twitter_merge", "Return to Twitter Harvest") . "</a></p>";
				
		}
		
		private function slice_file(){
				
			if(count($_POST)!==0){
				
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				$data_store = array();
				$tweets = array();
				
				while($file = array_pop($_POST['plaintextfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $file));
					
					foreach($data as $tweet){
					
						if(!in_array($tweet->id_str,$tweets)){
						
							array_push($tweets, $tweet->id_str);
							array_push($data_store, $tweet);
						
						}
					
					}							
					
				}
				
				$response = $file_process->update_file("data/twitter_harvest/files/aggregate/" . $_POST['new_file_name'] . ".json", serialize($data_store));
					
				$output = "<h2>" . $this->language->translate("data/twitter_merge", "Twitter merge") . "</h2>";
				
				$output .= "<p>" . $this->language->translate("data/twitter_merge", "File Created") . "</p>";
					
				$output .= "<p><a href='?data=twitter_merge'>" . $this->language->translate("data/twitter_merge", "Return to Twitter merge") . "</a></p>";
					
				return $output;
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();		
					
				$files = $file_process->read_folder_files_only("data/twitter_harvest/files/aggregate/");
					
				if(count($files)!==0){
			
					$output = "<h2>" . $this->language->translate("data/twitter_merge", "Choose a twitter file") . "</h2>
									<p>" . $this->language->translate("data/twitter_merge", "Choose files to merge") . "</p>
									<form enctype='multipart/form-data' action='' method='POST'>";
						
					$output .= "<select multiple style='height:400px' name='plaintextfile[]'>
										<option>" . $this->language->translate("data/twitter_merge", "Select a file") . "</output>";
								
					while($plain = array_pop($files)){
						
							$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
						
					}
		
					$output .=	"</select><br />";
						
					$output .= "<input type='hidden' name='time_choose' />";
											
					$output .= "<label>" . $this->language->translate("data/twitter_merge", "Enter a new file name") . "</label>";						
											
					$output .=	"<input type='text' name='new_file_name' /></br>";
					
					$output .=	"<input type='submit' value='" . $this->language->translate("data/twitter_merge", "Merge") . "' />
									</form>";
				}else{
					
					$output = "<p>" . $this->language->translate("data/twitter_merge", "No files have been uploaded yet") . " - <a href='?data=twitter_merge'>" . $this->language->translate("data/twitter_merge", "Return to plain text") . "</a></p>";
					
				}
					
				return $output;
				
			}
			
		}		
				
	}