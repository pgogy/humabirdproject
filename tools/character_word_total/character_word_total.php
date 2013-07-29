<?PHP

	class character_word_total extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Play Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=character_word_total";
			$classification->name = "Character word total";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/character_word_total/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/character_word_total/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/character_word_total", "Character Word Total") . "</h2>
						   <ul>
								<li>
									<a href='?tool=character_word_total&action=instructions'>" . $this->language->translate("tools/character_word_total", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=character_word_total&action=display'>" . $this->language->translate("tools/character_word_total", "Analyse character word total") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/character_word_total", "help");
			
			return $output . "<p><a href='?tool=character_word_total'>" . $this->language->translate("tools/character_word_total", "Return to Character Word Total") . "</a></p>";
				
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
							
						}
						
						$text = str_replace("'", "", str_replace("  ", " ", str_replace("!", "", str_replace(".", "", str_replace(",", "", str_replace("?", "", str_replace(":", "", str_replace(";", "", str_replace(")", "", $inner_data[4])))))))));
						
						$words = explode(" ", strtolower(substr($text,0,strlen($text))));
						
						while($word = array_shift($words)){
						
							if(isset($play->{$speaker}->words)){
							
								$play->{$speaker}->words++;
							
							}else{
								
								$play->{$speaker}->words = 1;
							
							}
						
						}
					
					}
					
				}
				
				$output = "<h2>" . $this->language->translate("tools/character_word_total", "Character word totals") . "</h2>";
				
				foreach($play as $speaker => $data){
				
					$output .= "<p><strong>" . $speaker . "</strong></p>";					
					$output .= "<p>" . $data->words . "</p>";
					
				}
				
				return $output . "<p><a href='?tool=character_word_total'>" . $this->language->translate("tools/character_word_total", "Return to Character word total") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder_files_only("data/play/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/character_word_total", "Character Cloud") . "</h2>
							   <p>" . $this->language->translate("tools/character_word_total", "Select a play") . "</p>
							   <form action='' method='POST'>
									<select name='playfile'>
										<option>" . $this->language->translate("tools/character_word_total", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . str_replace(".csv","",$plain) . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/character_word_total", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/character_word_total", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}