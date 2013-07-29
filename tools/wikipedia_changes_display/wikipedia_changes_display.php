<?PHP

	class wikipedia_changes_display extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Wikipedia Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=wikipedia_changes_display";
			$classification->name = "Wikipedia Edit changes display";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/wikipedia_changes_display/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/wikipedia_changes_display/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/wikipedia_changes_display", "Wikipedia Edit changes display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=wikipedia_changes_display&action=instructions'>" . $this->language->translate("tools/wikipedia_changes_display", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=wikipedia_changes_display&action=display'>" . $this->language->translate("tools/wikipedia_changes_display", "Display Changes") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/wikipedia_changes_display", "help");
			
			return $output . "<p><a href='?tool=wikipedia_changes_display'>" . $this->language->translate("tools/wikipedia_changes_display", "Return to Wikipedia Changes Display") . "</a></p>";
				
		}
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/wikipedia_download/files/aggregate/" . $_POST['wikifile'])){
				
					$data = unserialize($file_process->file_get_all("data/wikipedia_download/files/aggregate/" . $_POST['wikifile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/wikipedia_changes_display", "Wikipedia edit changes for file ") . " : " . $_POST['wikifile'] . "</h2>";
				$output .= "<p>" . $this->language->translate("tools/wikipedia_changes_display", "Oldest change first") . "</p>";
				
				$data = array_reverse($data);
				
				$changes = array();
				
				$max_change = 0;
				$max = 0;
				$min = 0;
				
				for($x=0;$x<count($data);$x++){
				
					
					if(isset($data[$x+1])){
				
						$changes[$x] = $data[$x+1]['size'] - $data[$x]['size'];
					
						if($changes[$x] > $max){
						
							$max = $changes[$x];
						
						}
						
						if($changes[$x] < $min){
						
							$min = $changes[$x];
						
						}
					
					}
				
				}
				
				for($x=0;$x<count($changes);$x++){
				
					$output .= "<p>" . $this->language->translate("tools/wikipedia_changes_display", "Revision ") . " " . $x . " " . $this->language->translate("tools/wikipedia_changes_display", "Change size ") . " " . $changes[$x] . "</p>";
				
				}
				
				return $output . "<p><a href='?tool=wikipedia_changes_display'>" . $this->language->translate("tools/wikipedia_changes_display", "Return to Wikipedia Editors Display changes") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$edits = $file_process->read_folder("data/wikipedia_download/files/aggregate/");
				
				arsort($edits);
				
				if(count($edits)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/wikipedia_changes_display", "Display a Wikipedia Edits file") . "</h2>
							   <p>" . $this->language->translate("tools/wikipedia_changes_display", "Choose a Wikipedia Edit file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='wikifile'>
										<option>" . $this->language->translate("tools/wikipedia_changes_display", "Select a file") . "</output>";
								
					while($plain = array_pop($edits)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/wikipedia_changes_display", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/wikipedia_changes_display", "No Wikipedia download files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}