<?PHP

	class rare_phrase extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Text Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=rare_phrase";
			$classification->name = "Rare Phrase";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/rare_phrase/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/rare_phrase/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/rare_phrase", "Rare Phrase Detection") . "</h2>
						   <ul>
								<li>
									<a href='?tool=rare_phrase&action=instructions'>" . $this->language->translate("tools/rare_phrase", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=rare_phrase&action=detectdisplay'>" . $this->language->translate("tools/rare_phrase", "Detect and display") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/rare_phrase", "help");
			
			return $output . "<p><a href='?tool=rare_phrase'>" . $this->language->translate("tools/rare_phrase", "Return to Rare Phrase") . "</a></p>";
				
		}
		
		private function detectdisplay(){
		
			if(count($_POST)!==0){

				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = $file_process->file_get_all("data/plain_text/files/" . $_POST['textfile']);
				$words = explode(" ", str_replace("\n"," ", strtolower($data)));
				foreach($words as $key => $value){
					$words[$key] = trim($value);
				}			
				$words = array_filter($words);
				
				$last_word = array();
				$words_list = array();
				
				$words_count = array();
				
				foreach($words as $word){
					
					$word = str_replace("&mdash;","",$word);
				
					if(trim($word)!=""){
					
						if(strlen($word)!=1){
						
							$last = substr($word,strlen($word)-1,1);
						
							while($last=="_"||$last=="!"||$last=="."||$last=="-"||$last==","||$last=="?"||$last==":"||$last==";"||$last==")"){
							
								$word = substr($word,0,strlen($word)-1);
								$last = substr($word,strlen($word)-1,1);
								
							}
							
							$first = substr($word,0,1);
						
							while($first=="_"||$first=="!"||$first=="."||$first==","||$first=="-"||$first=="?"||$first==":"||$first==";"||$first==")"||$first=="("){
							
								$word = substr($word,1,strlen($word)-1);
								$first = substr($word,0,1);
								
							}
							
							if(count($last_word)==$_POST['phraselength']){
							
								if(!isset($words_list[implode("*",$last_word)])){
										
									$words_list[implode("*",$last_word)] = 1;
										
								}else{
									
									$words_list[implode("*",$last_word)]++;
									
								}
								
								array_shift($last_word);
								
							}
							
							array_push($last_word,$word);
								
						}
						
						if(!isset($words_count[$word])){
									
							$words_count[$word] = 1;
								
						}else{
							
							$words_count[$word]++;
							
						}
						
					}
						
				}
				
				$phrase_score = array();
				
				foreach($words_list as $word => $occurrence){
				
					$parts = explode("*", $word);
					
					$phrase_score[$word] = $words_count[$parts[0]] + $words_count[$parts[1]]; 
				
				}
				
				asort($words_list);
				asort($phrase_score);
				
				$output = "<h2>" . $this->language->translate("tools/rare_phrase", "Rare Phrase display") . "</h2><div class='rare_phrase'>";
				$output .= "<h3>" . $this->language->translate("tools/rare_phrase", "Rare Phrase occurrences") . "</h3>";
				
				foreach($words_list as $word => $occurrence){
				
					$output .= "<p>" . str_replace("*", " ", $word) . " " . $occurrence . "</p>";
					
				}
				
				$output .= "</div>";
				
				$output .= "<div class='rare_phrase'><h3>" . $this->language->translate("tools/rare_phrase", "Rare Phrases ordered by word occurrence") . "</h3>";
				
				foreach($phrase_score as $word => $occurrence){
				
					$output .= "<p>" . str_replace("*", " ", $word) . " " . $occurrence . " " . $this->language->translate("tools/rare_phrase", "total word occurrences in text") . "</p>";
					
				}
				
				$output .= "</div>";
				
				return $output;
				

			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/plain_text/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/rare_phrase", "Detect Rare Phrase") . "</h2>
							   <p>" . $this->language->translate("tools/rare_phrase", "Choose a text file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='textfile'>
										<option>" . $this->language->translate("tools/rare_phrase", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<label>
										" . $this->language->translate("tools/rare_phrase", "Number of words in the phrase") . "
									</label>									
									<input type='text' name='phraselength' />
									<input type='submit' value='" . $this->language->translate("tools/rare_phrase", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/rare_phrase", "No Zipf files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}