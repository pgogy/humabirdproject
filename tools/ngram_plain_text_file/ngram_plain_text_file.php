<?PHP

	class ngram_plain_text_file extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Intertextual";
			$classification->column = "Tools";
			$classification->link = "?tool=ngram_plain_text_file";
			$classification->name = "Ngram and Plain text compare";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/ngram_plain_text_file/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/ngram_plain_text_file/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/ngram_plain_text_file", "Ngram and pasted text") . "</h2>
						   <ul>
								<li>
									<a href='?tool=ngram_plain_text_file&action=instructions'>" . $this->language->translate("tools/ngram_compare", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=ngram_plain_text_file&action=search_all'>" . $this->language->translate("tools/ngram_plain_text_file", "Ngram search - searches entire file, can be slow") . "</a>
								</li>
								<li>
									<a href='?tool=ngram_plain_text_file&action=search_part'>" . $this->language->translate("tools/ngram_plain_text_file", "Ngram search (part) - searches part of a file") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/ngram_plain_text_file", "help");
			
			return $output . "<p><a href='?tool=ngram_plain_text_file'>" . $this->language->translate("tools/ngram_plain_text_file", "Return to Ngram Plain Text Compare") . "</a></p>";
				
		}
		
		private function search_part(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/ngram/files/" . $_POST['ngramfile']));				
				$text = $file_process->file_get_all("data/plain_text/files/" . $_POST['plaintextfile']);
				
				$text = str_replace("  ", " ", str_replace("!", "", str_replace(".", "", str_replace(",", "", str_replace("?", "", str_replace(":", "", str_replace(";", "", str_replace(")", "", $text))))))));
				
				$words = explode(" ", str_replace("\n"," ",$text));
				foreach($words as $key => $value){
					$words[] = trim($value);
					
				}	

				$keys = array_keys($data);
				
				$key = explode("-", $keys[1]);
				
				$length = count($key);
				
				$words = array_filter($words);
				
				$last_word = array();
				$words_list = array();
				
				$max = min(count($words),$_POST['words_limit']);
				
				for($x=0;$x<=$max;$x++){
				
					$ngram = array_slice($words,$x,$length);
					
					if(!isset($words_list[implode("-",$ngram)])){
										
						$words_list[implode("-",$ngram)] = 1;
										
					}else{
									
						$words_list[implode("-",$ngram)]++;
									
					}
						
				}
				
				$output = "<h2>" . $this->language->translate("tools/ngram_plain_text_file", "Ngram Results") . "</h2>";
				
				$output .= "<p>" . $_POST['plaintextfile'] . " " . $this->language->translate("tools/ngram_plain_text_file", " - words - ") . count($words) . "</p>";
				
				$output .= "<p>" . $this->language->translate("tools/ngram_plain_text_file", "Search this file again") . "</p>";
				
				$output .= "<form action='' method='POST'>
								<input name='ngramfile' type='hidden' value='" . $_POST['ngramfile'] . "'>
								<input name='plaintextfile' type='hidden' value='" . $_POST['plaintextfile'] . "'>";
									
				$output .=	"<input name='words_start' size=50 type='text' value='" . $this->language->translate("tools/ngram_plain_text_file", "Enter start word number here") . "' /><br />";
				$output .=	"<input name='words_limit' size=50 type='text' value='" . $this->language->translate("tools/ngram_plain_text_file", "Enter word maximum here") . "' /><br />";
				
				$output .= "<input type='submit' value='" . $this->language->translate("tools/ngram_plain_text_file", "Search") . "' />";
				
				foreach($data as $ngram => $occurrences){
				
					if(isset($words_list[$ngram])){
					
						$output .= "<p><strong>" . str_replace("-"," ",$ngram) . "</strong></p><p>" . $this->language->translate("tools/ngram_plain_text_file", "Occurrences in") . " " . $_POST['ngramfile'] . " " . $occurrences . " | " . $this->language->translate("tools/ngram_plain_text_file", "Occurrences in") . " " . $_POST['plaintextfile'] . " " . $words_list[$ngram] . "</p>";
					
					}
				
				}
				
				return $output . "<p><a href='?tool=ngram_plain_text_file'>" . $this->language->translate("tools/ngram_plain_text_file", "Return to Ngram Comparison to plain text") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/ngram/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/ngram_plain_text_file", "Search an Ngram file") . "</h2>
							   <p>" . $this->language->translate("tools/ngram_plain_text_file", "Choose a Ngram and a plain text file") . "</p>
							   <form action='' method='POST'>
									<select name='ngramfile'>
										<option>" . $this->language->translate("tools/ngram_plain_text_file", "Select an Ngram file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
						
					$plain_text = $file_process->read_folder("data/plain_text/files/");

					$output .= "<select name='plaintextfile'>
										<option>" . $this->language->translate("tools/ngram_plain_text_file", "Select a Plain Text file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
					
					$output .=	"<input name='words_start' size=50 type='text' value='" . $this->language->translate("tools/ngram_plain_text_file", "Enter start word number here") . "' /><br />";
					$output .=	"<input name='words_limit' size=50 type='text' value='" . $this->language->translate("tools/ngram_plain_text_file", "Enter word maximum here") . "' />";
				
					$output .= "<input type='submit' value='" . $this->language->translate("tools/ngram_plain_text_file", "Search") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/ngram_plain_text_file", "No Ngram files exist, please create one before using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		
		private function search_all(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/ngram/files/" . $_POST['ngramfile']));				
				$text = $file_process->file_get_all("data/plain_text/files/" . $_POST['plaintextfile']);
				
				$text = str_replace("  ", " ", str_replace("!", "", str_replace(".", "", str_replace(",", "", str_replace("?", "", str_replace(":", "", str_replace(";", "", str_replace(")", "", $text))))))));
				
				$words = explode(" ", str_replace("\n"," ",$text));
				foreach($words as $key => $value){
					$words[] = trim($value);
					
				}	

				$keys = array_keys($data);
				
				$key = explode("-", $keys[1]);
				
				$length = count($key);
				
				$words = array_filter($words);
				
				$last_word = array();
				$words_list = array();
				
				for($x=0;$x<=count($words);$x++){
				
					$ngram = array_slice($words,$x,$length);
					
					if(!isset($words_list[implode("-",$ngram)])){
										
						$words_list[implode("-",$ngram)] = 1;
										
					}else{
									
						$words_list[implode("-",$ngram)]++;
									
					}
						
				}
				
				$output = "<h2>" . $this->language->translate("tools/ngram_plain_text_file", "Ngram Results") . "</h2>";
				
				foreach($data as $ngram => $occurrences){
				
					if(isset($words_list[$ngram])){
					
						$output .= "<p><strong>" . str_replace("-"," ",$ngram) . "</strong></p><p>" . $this->language->translate("tools/ngram_plain_text_file", "Occurrences in") . " " . $_POST['ngramfile'] . " " . $occurrences . " | " . $this->language->translate("tools/ngram_plain_text_file", "Occurrences in") . " " . $_POST['plaintextfile'] . " " . $words_list[$ngram] . "</p>";
					
					}
				
				}
				
				return $output . "<p><a href='?tool=ngram_plain_text_file'>" . $this->language->translate("tools/ngram_plain_text_file", "Return to Ngram Comparison to plain text") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/ngram/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/ngram_plain_text_file", "Search an Ngram file") . "</h2>
							   <p>" . $this->language->translate("tools/ngram_plain_text_file", "Choose a Ngram and a plain text file") . "</p>
							   <form action='' method='POST' target='_blank'>
									<select name='ngramfile'>
										<option>" . $this->language->translate("tools/ngram_plain_text_file", "Select an Ngram file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
						
					$plain_text = $file_process->read_folder("data/plain_text/files/");

					$output .= "<select name='plaintextfile'>
										<option>" . $this->language->translate("tools/ngram_plain_text_file", "Select a Plain Text file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
					$output .=	"<input name='newwindow' type='hidden' value='true' />";
				
					$output .= "<input type='submit' value='" . $this->language->translate("tools/ngram_plain_text_file", "Search") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/ngram_plain_text_file", "No Ngram files exist, please create one before using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}