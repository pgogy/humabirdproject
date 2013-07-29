<?PHP

	class regexp_concordance extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Concordance";
			$classification->column = "Tools";
			$classification->link = "?tool=regexp_concordance";
			$classification->name = "Regular Exppression Concordance";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/regexp_concordance/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/regexp_concordance/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/regexp_concordance", "Regular Expression Concordance") . "</h2>
						   <ul>
								<li>
									<a href='?tool=regexp_concordance&action=instructions'>" . $this->language->translate("tools/regexp_concordance", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=regexp_concordance&action=display'>" . $this->language->translate("tools/regexp_concordance", "List matches") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/regexp_concordance", "help");
			
			return $output . "<p><a href='?tool=regexp_concordance'>" . $this->language->translate("tools/regexp_concordance", "Return to Regular Expression Concordance") . "</a></p>";
				
		}
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = $file_process->file_get_all("data/plain_text/files/" . $_POST['concordancefile']);	
				
				if($_POST['case']==="on"){
				
					$data = strtolower($data);
				
				}
				
				preg_match_all("/" . str_replace("?","[A-Za-z]*", str_replace("?","[A-Za-z]", str_replace("*","[A-Za-z]+",$_POST['term']))) . "/", $data, $out, PREG_PATTERN_ORDER);
				
				$out = array_unique($out[0]);
				
				while($word = array_shift($out)){
				
					if(strpos($data, $word)===FALSE){
					
						$output .= "<p>" . $this->language->translate("tools/regexp_concordance", "No results for term") . " " . $word . " " . $this->language->translate("tools/regexp_concordance", "in") . " " . $_POST['concordancefile'] . "</p>";
					
					}else{
					
						$output .= "<p><strong>" . $this->language->translate("tools/regexp_concordance", "Matches for") . " " . $word . "</strong></p>";
					
						$wrap = round($_POST['length'] / 2);
					
						$offset = 0;
						
						while(stripos($data, $word,$offset)!==FALSE){
						
							$output .= "<p>" . str_replace($word, "<strong>" . $word . "</strong>", substr($data, (stripos($data, $word,$offset) - $wrap), strlen($word) + $_POST['length'])) . "</p>";
							
							$offset = stripos($data, $word,$offset) + 1;
					
						}
						
					}
				
				}
				
				return $output . "<p><a href='?tool=regexp_concordance'>" . $this->language->translate("tools/regexp_concordance", "Return to Regular Expression Concordance") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/plain_text/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/regexp_concordance", "Regular Expression Concordance") . "</h2>
							   <p>" . $this->language->translate("tools/regexp_concordance", "Choose a plain text file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='concordancefile'>
										<option>" . $this->language->translate("tools/regexp_concordance", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<p>" . $this->language->translate("tools/regexp_concordance", "Term to search for") . "</p>
									<p>" . $this->language->translate("tools/regexp_concordance", "Use a question mark for single character") . "</p>
									<p>" . $this->language->translate("tools/regexp_concordance", "An Asterisk is multiple characters") . "</p>
									<p>" . $this->language->translate("tools/regexp_concordance", "A Plus is an optional character") . "</p>
									<input type='text' name='term' value='" . $this->language->translate("tools/regexp_concordance", "Enter term here") . "' />
									<p>" . $this->language->translate("tools/regexp_concordance", "Length of text around match") . "</p>
									<input type='text' name='length' size=100 value='" . $this->language->translate("tools/regexp_concordance", "Enter length of surrounding text here") . "' />
									<p>" . $this->language->translate("tools/regexp_concordance", "Case Sensitive - check for on") . "
									<input type='checkbox' name='case' value='on' /></p>
									<input type='submit' value='" . $this->language->translate("tools/regexp_concordance", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/regexp_concordance", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}