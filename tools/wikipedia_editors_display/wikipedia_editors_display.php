<?PHP

	class wikipedia_editors_display extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Wikipedia Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=wikipedia_editors_display";
			$classification->name = "Wikipedia Editors Count";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/wikipedia_editors_display/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/wikipedia_editors_display/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/wikipedia_editors_display", "Wikipedia Editors Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=wikipedia_editors_display&action=instructions'>" . $this->language->translate("tools/wikipedia_editors_display", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=wikipedia_editors_display&action=display'>" . $this->language->translate("tools/wikipedia_editors_display", "Display Editor count") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/wikipedia_editors_display", "help");
			
			return $output . "<p><a href='?tool=wikipedia_editors_display'>" . $this->language->translate("tools/wikipedia_editors_display", "Return to Wikipedia Editors Display") . "</a></p>";
				
		}
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/wikipedia_download/files/aggregate/" . $_POST['wikifile'])){
				
					$data = unserialize($file_process->file_get_all("data/wikipedia_download/files/aggregate/" . $_POST['wikifile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/wikipedia_editors_display", "Wikipedia edit Display for file ") . " : " . $_POST['wikifile'] . "</h2>";
				
				$edits = array();
				
				foreach($data as $edit){
				
					if(isset($edits[$edit['user']])){

						$edits[$edit['user']]++;

					}else{
					
						$edits[$edit['user']] = 1;
					
					}
				
				}
				
				arsort($edits);
				
				$output = "<p>" . $this->language->translate("tools/wikipedia_editors_display", "Wikipedia editors for this file ") . " : " . count($edits) . "</p>";
				
				foreach($edits as $edit => $times){
				
					$output .= "<p>" . $this->language->translate("tools/wikipedia_editors_display", "User ") . " " . $edit . " |  " . $this->language->translate("tools/wikipedia_editors_display", "Edits ") . " " . $times . "</p>";
				
				}
				
				return $output . "<p><a href='?tool=wikipedia_editors_display'>" . $this->language->translate("tools/wikipedia_editors_display", "Return to Wikipedia Editors Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$edits = $file_process->read_folder("data/wikipedia_download/files/aggregate/");
				
				arsort($edits);
				
				if(count($edits)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/wikipedia_editors_display", "Display a Wikipedia Edits file") . "</h2>
							   <p>" . $this->language->translate("tools/wikipedia_editors_display", "Choose a Wikipedia Edit file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='wikifile'>
										<option>" . $this->language->translate("tools/wikipedia_editors_display", "Select a file") . "</output>";
								
					while($plain = array_pop($edits)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/wikipedia_editors_display", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/wikipedia_editors_display", "No Wikipedia download files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}