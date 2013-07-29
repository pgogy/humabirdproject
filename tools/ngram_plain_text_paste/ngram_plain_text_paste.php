<?PHP

	class ngram_plain_text_paste extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Intertextual";
			$classification->column = "Tools";
			$classification->link = "?tool=ngram_plain_text_paste";
			$classification->name = "Ngram and Pasted text";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/ngram_plain_text_paste/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/ngram_plain_text_paste/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>Ngram and Pasted text</h2>
						   <ul>
								<li>
									<a href='?tool=ngram_plain_text_paste&action=instructions'>" . $this->language->translate("tools/ngram_plain_text_paste", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=ngram_plain_text_paste&action=search'>" . $this->language->translate("tools/ngram_plain_text_paste", "Search") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/ngram_plain_text_paste", "help");
			
			return $output . "<p><a href='?tool=ngram_plain_text_paste'>" . $this->language->translate("tools/ngram_plain_text_paste", "Return to Ngram Plain Text Paste") . "</a></p>";
				
		}
		
		private function search(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/ngram/files/" . $_POST['ngramfile']));
				
				$words = explode(" ", str_replace("\n"," ",$_POST['pastedtext']));
				foreach($words as $key => $value){
					$words[$key] = trim($value);
				}			
			
				$keys = array_keys($data);
				
				$key = explode("-", $keys[1]);
				
				$length = count($key);
				
				$words = array_filter($words);
				
				$last_word = array();
				$words_list = array();
				
				foreach($words as $word){
					
					$word = str_replace("&mdash;","",$word);
				
					if(trim($word)!=""){
					
						if(strlen($word)!=1){
						
							$last = substr($word,strlen($word)-1,1);
						
							while($last=="!"||$last=="."||$last==","||$last=="?"||$last==":"||$last==";"||$last==")"){
							
								$word = substr($word,0,strlen($word)-1);
								$last = substr($word,strlen($word)-1,1);
								
							}
							
							$first = substr($word,0,1);
						
							while($first=="!"||$first=="."||$first==","||$first=="?"||$first==":"||$first==";"||$first==")"||$first=="("){
							
								$word = substr($word,1,strlen($word)-1);
								$first = substr($word,strlen($word)-1,1);
								
							}
							
							if(count($last_word)==$length){
							
								if(!isset($words_list[implode("-",$last_word)])){
										
									$words_list[implode("-",$last_word)] = 1;
										
								}else{
									
									$words_list[implode("-",$last_word)]++;
									
								}
								
								array_shift($last_word);
								
							}
							
							array_push($last_word,$word);
								
						}
						
					}
						
				}
				
				$output = "<h2>Ngram results</h2>";
				
				foreach($data as $ngram => $occurrences){
				
					if(isset($words_list[$ngram])){
					
						$output .= "<p><strong>" . str_replace("-"," ",$ngram) . "</strong></p><p>" . $this->language->translate("tools/ngram_plain_text_paste", "Occurrences in") . " " . $_POST['ngramfile'] . " " . $occurrences . " | " . $this->language->translate("tools/ngram_plain_text_paste", "Occurrences in pasted text") . " " . $words_list[$ngram] . "</p>";
					
					}
				
				}
				
				return $output . "<p><a href='?tool=ngram_plain_text_paste'>" . $this->language->translate("tools/ngram_plain_text_paste", "Return to Ngram plain text paste") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/ngram/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/ngram_plain_text_paste", "Search an Ngram file") . "</h2>
							   <p>" . $this->language->translate("tools/ngram_plain_text_paste", "Paste in some test and choose a Ngram file") . "</p>
							   <form action='' method='POST'>
									<select name='ngramfile'>
										<option>" . $this->language->translate("tools/ngram_plain_text_paste", "Select an Ngram file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<textarea style='width:100%; height:300px;' name='pastedtext'>" . $this->language->translate("tools/ngram_plain_text_paste", "Paste text here") . "</textarea>
									<input type='submit' value='" . $this->language->translate("tools/ngram_plain_text_paste", "Search") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/ngram_plain_text_paste", "No Ngram files can be found, please create one before using this tool") . "</p>";				
				
				}
			
			}
		
		}
		
	}