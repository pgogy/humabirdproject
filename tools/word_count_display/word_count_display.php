<?PHP

	class word_count_display extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Text Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=word_count_display";
			$classification->name = "Word Count Display";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/word_count_display/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/word_count_display/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/word_count_display", "Word Count display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=word_count_display&action=instructions'>" . $this->language->translate("tools/word_count_display", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=word_count_display&action=display'>" . $this->language->translate("tools/word_count_display", "Display all words") . "</a>
								</li>
								<li>
									<a href='?tool=word_count_display&action=display_total'>" . $this->language->translate("tools/word_count_display", "Display total") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/sentence_length_display", "help");
			
			return $output . "<p><a href='?tool=word_count_display'>" . $this->language->translate("tools/word_count_display", "Return to Word Count display") . "</a></p>";
				
		}
		
		private function display_total(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/word_count/files/" . $_POST['wcfile']));
			
				$output = "<h2>" . $this->language->translate("tools/word_count_display", "Total word count for ") . " " . $_POST['wcfile'] . "</h2>";
				
				$output .= "<p>" . $this->language->translate("tools/word_count_display", "Number of words in this file ") . " : " . count($data) . "</p>";
				
				return $output . "<p><a href='?tool=word_count_display'>" . $this->language->translate("tools/word_count_display", "Return to Word Count display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/word_count/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/word_count_display", "Select a Word Count file") . "</h2>
							   <p>" . $this->language->translate("tools/word_count_display", "Choose a Word Count file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='wcfile'>
										<option>" . $this->language->translate("tools/word_count_display", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/word_count_display", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/word_count_display", "No word count files exist, please create one before using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/word_count/files/" . $_POST['wcfile']));
			
				$output = "<h2>" . $this->language->translate("tools/word_count_display", "Word count for ") . " " . $_POST['wcfile'] . "</h2>";
				
				$output .= "<p>" . $this->language->translate("tools/word_count_display", "Number of words in this file ") . " : " . count($data) . "</p>";
				
				foreach($data as $place => $word){
				
					$output .= "<p>" . $this->language->translate("tools/word_count_display", "Place") . " : " . ($place + 1) . " | " . $this->language->translate("tools/word_count_display", "Word") . " : " . $word . "</p>"; 
				
				}
				
				return $output . "<p><a href='?tool=word_count_display'>" . $this->language->translate("tools/word_count_display", "Return to Word Count display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/word_count/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/word_count_display", "Select a Word Count file") . "</h2>
							   <p>" . $this->language->translate("tools/word_count_display", "Choose a Word Count file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='wcfile'>
										<option>" . $this->language->translate("tools/word_count_display", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/word_count_display", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/word_count_display", "No word count files exist, please create one before using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}