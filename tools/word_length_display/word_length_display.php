<?PHP

	class word_length_display extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Text Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=word_length_display";
			$classification->name = "Word Length Display";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/word_length_display/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/word_length_display/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/word_length_display", "Word length display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=word_length_display&action=instructions'>" . $this->language->translate("tools/word_length_display", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=word_length_display&action=display'>" . $this->language->translate("tools/word_length_display", "Display") . "</a>
								</li>
								<li>
									<a href='?tool=word_length_display&action=compare'>" . $this->language->translate("tools/word_length_display", "Compare") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/sentence_length_display", "help");
			
			return $output . "<p><a href='?tool=word_length_display'>" . $this->language->translate("tools/word_length_display", "Return to Word Length display") . "</a></p>";
				
		}
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/word_length/files/" . $_POST['wlfile']));
			
				arsort($data);
			
				$output = "<h2>Word length for " . $_POST['wlfile'] . "</h2>";
				
				foreach($data as $length => $occurrences){
				
					$output .= "<p>" . $this->language->translate("tools/word_length_display", "Length") . " : " . $length . " | " . $this->language->translate("tools/word_length_display", "Occurrences") . " : " . $occurrences . "</p>"; 
				
				}
				
				sort($data);
			
				$output .= "<h2>Word length for " . $_POST['wlfile'] . "</h2>";
				
				foreach($data as $length => $occurrences){
				
					$output .= "<p>" . $this->language->translate("tools/word_length_display", "Length") . " : " . $length . " | " . $this->language->translate("tools/word_length_display", "Occurrences") . " : " . $occurrences . "</p>"; 
				
				}
				
				return $output . "<p><a href='?tool=word_length_display'>" . $this->language->translate("tools/word_length_display", "Return to Word length display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/word_length/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/word_length_display", "Search a Word length file") . "</h2>
							   <p>" . $this->language->translate("tools/word_length_display", "Choose a Word length file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='wlfile'>
										<option>" . $this->language->translate("tools/word_length_display", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/word_length_display", "Display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/word_length_display", "No word length display files exist, please create one before using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function compare(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/word_length/files/" . $_POST['wlfileone']));
				
				$output = "<h2>" . $this->language->translate("tools/word_length_display", "Word lengths compared") . "</h2>";
				$output .= "<p>" . $this->language->translate("tools/word_length_display", "Results for") . " " . $_POST['wlfileone'] . " " . $this->language->translate("tools/word_length_display", "and") . " " . $_POST['wlfiletwo'] . "</p>";
				$output .= "<div class='wl_compare'>";
				
				arsort($data);
				
				foreach($data as $word => $occurrence){
				
					$output .= "<p>" . $this->language->translate("tools/word_length_display", "Word ") . " " . $word . " : " . $this->language->translate("tools/word_length_display", "length") . " " . $occurrence . "</p>";
				
				}
				
				$output .="</div>";
				
				$data = unserialize($file_process->file_get_all("data/word_length/files/" . $_POST['wlfiletwo']));
			
				arsort($data);
			
				$output .= "<div class='wl_compare'>";
				
				foreach($data as $word => $occurrence){
				
					$output .= "<p>" . $this->language->translate("tools/word_length_display", "Word ") . " " . $word . " : " . $this->language->translate("tools/word_length_display", "Length") . " " . $occurrence . "</p>";
				
				}
				
				$output .="</div>";
				
				return $output . "<p><a href='?tool=word_length_display'>" . $this->language->translate("tools/word_length_display", "Return to Word length display") . "</a></p>";
			
			}else{
		
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/word_length/files/");
				$second_plain_text = $plain_text;
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/word_length_display", "Compare two word length files") . "</h2>
							   <form action='' method='POST'>
									<label>
										" . $this->language->translate("tools/word_length_display", "File one") . "
									</label>
									<select name='wlfileone'>
										<option>" . $this->language->translate("tools/word_length_display", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
					
					$output .= "<label>
									" . $this->language->translate("tools/word_length_display", "File two") . "
								</label>
								<select name='wlfiletwo'>
										<option>" . $this->language->translate("tools/word_length_display", "Select a file") . "</output>";
								
					while($plain = array_pop($second_plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/word_length_display", "Compare") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/word_length_display", "No word length files exist, please create one before using this tool.") . "</p>";				
				
				}
							
			}
		
		}
		
	}