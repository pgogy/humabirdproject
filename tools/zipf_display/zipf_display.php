<?PHP

	class zipf_display extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Text Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=zipf_display";
			$classification->name = "Zipf Display";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/zipf_display/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/zipf_display/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/zipf_display", "Zipf Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=zipf_display&action=instructions'>" . $this->language->translate("tools/zipf_display", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=zipf_display&action=display'>" . $this->language->translate("tools/zipf_display", "Display") . "</a>
								</li>
								<li>
									<a href='?tool=zipf_display&action=compare'>" . $this->language->translate("tools/zipf_display", "Compare") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/zipf_display", "help");
			
			return $output . "<p><a href='?tool=zipf_display'>" . $this->language->translate("tools/zipf_display", "Return to Zipf display") . "</a></p>";
				
		}
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/zipf/files/" . $_POST['zipffile']));
				
				$output = "<h2>" . $this->language->translate("tools/zipf_display", "Zipf Display for file ") . " : " . $_POST['zipffile'] . "</h2>";
				
				foreach($data as $word => $occurrence){
				
					$output .= "<p>" . $this->language->translate("tools/zipf_display", "Word") . " : " . $word . " | " . $this->language->translate("tools/zipf_display", "Occurrences") . " : " . $occurrence . "</p>";
				
				}
				
				return $output . "<p><a href='?tool=zipf_display'>" . $this->language->translate("tools/zipf_display", "Return to Zipf Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/zipf/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/zipf_display", "Display a Zipf file") . "</h2>
							   <p>" . $this->language->translate("tools/zipf_display", "Choose a Zipf file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='zipffile'>
										<option>" . $this->language->translate("tools/zipf_display", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/zipf_display", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/zipf_display", "No Zipf files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function compare(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/zipf/files/" . $_POST['zipffileone']));
				
				$output = "<h2>" . $this->language->translate("tools/zipf_display", "Zipf data compared between ") . " </h2>";
				$output .= "<p>" . $this->language->translate("tools/zipf_display", "Results for ") . " " . $_POST['zipffileone'] . " " . $this->language->translate("tools/zipf_display", "and") . " " . $_POST['zipffiletwo'] . "</p>";
				$output .= "<div class='zipf_compare'>";
				
				foreach($data as $word => $occurrence){
				
					$output .= "<p>" . $this->language->translate("tools/zipf_display", "Word") . " " . $word . " : " . $this->language->translate("tools/zipf_display", "Occurrences") . " " . $occurrence . "</p>";
				
				}
				
				$output .="</div>";
				
				$data = unserialize($file_process->file_get_all("data/zipf/files/" . $_POST['zipffiletwo']));
			
				$output .= "<div class='zipf_compare'>";
				
				foreach($data as $word => $occurrence){
				
					$output .= "<p>" . $this->language->translate("tools/zipf_display", "Word") . " " . $word . " : " . $this->language->translate("tools/zipf_display", "Occurrences") . " " . $occurrence . "</p>";
				
				}
				
				$output .="</div>";
				
				return $output . "<p><a href='?tool=zipf_display'>" . $this->language->translate("tools/zipf_display", "Return to Zipf display") . "</a></p>";
			
			}else{
		
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/zipf/files/");
				$second_plain_text = $plain_text;
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/zipf_display", "Compare two Zipf files") . "</h2>
							   <form action='' method='POST'>
									<label>
										" . $this->language->translate("tools/zipf_display", "File one") . "
									</label>
									<select name='zipffileone'>
										<option>" . $this->language->translate("tools/zipf_display", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
					
					$output .= "<label>
									" . $this->language->translate("tools/zipf_display", "File two") . "
								</label>
								<select name='zipffiletwo'>
										<option>" . $this->language->translate("tools/zipf_display", "Select a file") . "</output>";
								
					while($plain = array_pop($second_plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/zipf_display", "Compare") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/zipf_display", "No Zipf files exist, please create one before using this tool") . "</p>";				
				
				}
							
			}
		
		}
		
	}