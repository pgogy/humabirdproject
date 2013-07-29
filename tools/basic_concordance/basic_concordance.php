<?PHP

	class basic_concordance extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Concordance";
			$classification->column = "Tools";
			$classification->link = "?tool=basic_concordance";
			$classification->name = "Basic Concordance";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/basic_concordance/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/basic_concordance/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/basic_concordance", "Basic Concordance") . "</h2>
						   <ul>
								<li>
									<a href='?tool=basic_concordance&action=instructions'>" . $this->language->translate("tools/basic_concordance", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=basic_concordance&action=display'>" . $this->language->translate("tools/basic_concordance", "List matches") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/basic_concordance", "help");
			
			return $output . "<p><a href='?tool=basic_concordance'>" . $this->language->translate("tools/basic_concordance", "Return to Basic Concordance") . "</a></p>";
				
		}
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = $file_process->file_get_all("data/plain_text/files/" . $_POST['concordancefile']);	
				
				if($_POST['case']==="on"){
				
					$data = strtolower($data);
				
				}
				
				if(strpos($data, $_POST['term'])===FALSE){
				
					$output = "<p>" . $this->language->translate("tools/basic_concordance", "No results for term") . " " . $_POST['term'] . " " . $this->language->translate("tools/basic_concordance", "in") . " " . $_POST['concordancefile'] . "</p>";
				
				}else{
				
					$output = "<h2>" . $this->language->translate("tools/basic_concordance", "Matches") . "</h2>";
				
					$wrap = round($_POST['length'] / 2);
				
					$offset = 0;
					
					while(stripos($data, $_POST['term'],$offset)!==FALSE){
					
						$output .= "<p>" . str_replace($_POST['term'], "<strong>" . $_POST['term'] . "</strong>", substr($data, (stripos($data, $_POST['term'],$offset) - $wrap), strlen($_POST['term']) + $_POST['length'])) . "</p>";
						
						$offset = stripos($data, $_POST['term'],$offset) + 1;
				
					}
					
				}
				
				return $output . "<p><a href='?tool=basic_concordance'>" . $this->language->translate("tools/basic_concordance", "Return to Basic Concordance") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/plain_text/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/basic_concordance", "Basic Concordance") . "</h2>
							   <p>" . $this->language->translate("tools/basic_concordance", "Choose a plain text file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='concordancefile'>
										<option>" . $this->language->translate("tools/basic_concordance", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<p>" . $this->language->translate("tools/basic_concordance", "Term to search for") . "</p>
									<input type='text' name='term' value='" . $this->language->translate("tools/basic_concordance", "Enter term here") . "' />
									<p>" . $this->language->translate("tools/basic_concordance", "Length of text around match") . "</p>
									<input type='text' name='length' size=50 value='" . $this->language->translate("tools/basic_concordance", "Enter length of surrounding text here") . "' />
									<p>" . $this->language->translate("tools/basic_concordance", "Case Sensitive - check for on") . "
									<input type='checkbox' name='case' value='on' /></p>
									<input type='submit' value='" . $this->language->translate("tools/basic_concordance", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/basic_concordance", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}