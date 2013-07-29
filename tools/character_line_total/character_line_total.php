<?PHP

	class character_line_total extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Play Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=character_line_total";
			$classification->name = "Character line total";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/character_line_total/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/character_line_total/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/character_line_total", "Character Line Total") . "</h2>
						   <ul>
								<li>
									<a href='?tool=character_line_total&action=instructions'>" . $this->language->translate("tools/character_line_total", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=character_line_total&action=display'>" . $this->language->translate("tools/character_line_total", "Analyse character word total") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/character_line_total", "help");
			
			return $output . "<p><a href='?tool=character_line_total'>" . $this->language->translate("tools/character_line_total", "Return to Character Line Total") . "</a></p>";
				
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
						
						if(isset($play->{$speaker}->lines)){
							
							$play->{$speaker}->lines++;
						
						}else{
							
							$play->{$speaker}->lines = 1;
						
						}
					
					}
					
				}
				
				$output = "<h2>" . $this->language->translate("tools/character_line_total", "Character line totals") . "</h2>";
				
				foreach($play as $speaker => $data){
				
					$output .= "<p><strong>" . $speaker . "</strong></p>";					
					$output .= "<p>" . $data->lines . "</p>";
					
				}
				
				return $output . "<p><a href='?tool=character_line_total'>" . $this->language->translate("tools/character_line_total", "Return to Character line total") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder_files_only("data/play/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/character_line_total", "Character Line total") . "</h2>
							   <p>" . $this->language->translate("tools/character_line_total", "Select a play") . "</p>
							   <form action='' method='POST'>
									<select name='playfile'>
										<option>" . $this->language->translate("tools/character_line_total", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . str_replace(".csv","",$plain) . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/character_line_total", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/character_line_total", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}