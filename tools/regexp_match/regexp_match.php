<?PHP

	class regexp_match extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Text Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=regexp_match";
			$classification->name = "Regular Expression Match";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/regexp_match/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/regexp_match/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/regexp_match", "Regular Expression Match") . "</h2>
						   <ul>
								<li>
									<a href='?tool=regexp_match&action=instructions'>" . $this->language->translate("tools/regexp_match", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=regexp_match&action=display'>" . $this->language->translate("tools/regexp_match", "List words that match") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/regexp_match", "help");
			
			return $output . "<p><a href='?tool=regexp_match'>" . $this->language->translate("tools/regexp_match", "Return to Regular Expression Match") . "</a></p>";
				
		}
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = $file_process->file_get_all("data/plain_text/files/" . $_POST['concordancefile']);	
				
				preg_match_all("/" . str_replace("?","[A-Za-z]*", str_replace("?","[A-Za-z]", str_replace("*","[A-Za-z]+",$_POST['term']))) . "/", $data, $out, PREG_PATTERN_ORDER);
				
				$out = array_unique($out[0]);
				
				sort($out);
				
				$output = "<h2>" . $this->language->translate("tools/regexp_match", "Matching term - ") . " " . $_POST['term'] . "</h2>";
				
				while($word = array_shift($out)){
				
					$output .= "<p>" . $this->language->translate("tools/regexp_match", "Match - ") . " " . $word . " </p>";
				
				}
				
				return $output . "<p><a href='?tool=regexp_match'>" . $this->language->translate("tools/regexp_match", "Return to Regular Expression Match") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/plain_text/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/regexp_match", "Regular Expression Match") . "</h2>
							   <p>" . $this->language->translate("tools/regexp_match", "Choose a plain text file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='concordancefile'>
										<option>" . $this->language->translate("tools/regexp_match", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<p>" . $this->language->translate("tools/regexp_match", "Term to search for") . "</p>
									<p>" . $this->language->translate("tools/regexp_match", "Use a question mark for single character") . "</p>
									<p>" . $this->language->translate("tools/regexp_match", "An Asterisk is multiple characters") . "</p>
									<p>" . $this->language->translate("tools/regexp_match", "A Plus is an optional character") . "</p>
									<input type='text' name='term' value='" . $this->language->translate("tools/regexp_match", "Enter term here") . "' />
									<input type='submit' value='" . $this->language->translate("tools/regexp_match", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/regexp_match", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}