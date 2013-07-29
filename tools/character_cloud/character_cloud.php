<?PHP

	class character_cloud extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Play Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=character_cloud";
			$classification->name = "Character cloud";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/character_cloud/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/character_cloud/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/character_cloud", "Character Cloud") . "</h2>
						   <ul>
								<li>
									<a href='?tool=character_cloud&action=instructions'>" . $this->language->translate("tools/character_cloud", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=character_cloud&action=display'>" . $this->language->translate("tools/character_cloud", "Create clouds for characters") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}

		private function instructions(){
		
			$output = $this->language->translate_help("tools/character_cloud", "help");
			
			return $output . "<p><a href='?tool=character_cloud'>" . $this->language->translate("tools/character_cloud", "Return to Character Cloud") . "</a></p>";
				
		}
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = $file_process->file_get_all("data/play/files/" . $_POST['playfile']);	
				
				$rows = explode("\n", $data);
				
				array_shift($rows);
				
				$play = new StdClass();
				
				while($row = array_shift($rows)){
				
					$inner_data = explode("	", $row);
					
					$speaker = substr($inner_data[3],0,strlen($inner_data[3]));
					
					if(trim($speaker)!=""){
					
						if(!isset($play->{$speaker})){
						
							$play->{$speaker} = new StdClass();
							
							$play->{$speaker}->words = array();
						
						}
						
						$text = str_replace("'", "", str_replace("  ", " ", str_replace("!", "", str_replace(".", "", str_replace(",", "", str_replace("?", "", str_replace(":", "", str_replace(";", "", str_replace(")", "", $inner_data[4])))))))));
						
						$words = explode(" ", strtolower(substr($text,0,strlen($text))));
						
						while($word = array_shift($words)){
						
							if(isset($play->{$speaker}->words[$word])){
							
								$play->{$speaker}->words[$word]++;
							
							}else{
								
								$play->{$speaker}->words[$word] = 1;
							
							}
						
						}
					
					}
					
				}
				
				$output = "<h2>" . $this->language->translate("tools/character_cloud", "Character clouds") . "</h2>";
				
				foreach($play as $speaker => $data){
				
					$output .= "<p><strong>" . $speaker . "</strong> " . $this->language->translate("tools/character_cloud", "Distinct words") . " " . count($data->words) . "</p>";
					asort($data->words);
					foreach($data->words as $word => $total){
					
						$output .= "<p>" . $word . " : " . $total . "</p>";
					
					}
					
				}
				
				return $output . "<p><a href='?tool=character_cloud'>" . $this->language->translate("tools/character_cloud", "Return to Character cloud") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder_files_only("data/play/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/character_cloud", "Character Cloud") . "</h2>
							   <p>" . $this->language->translate("tools/character_cloud", "Select a play") . "</p>
							   <form action='' method='POST'>
									<select name='playfile'>
										<option>" . $this->language->translate("tools/character_cloud", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . str_replace(".csv","",$plain) . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/character_cloud", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/character_cloud", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}