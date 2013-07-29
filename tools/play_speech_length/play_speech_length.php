<?PHP

	class play_speech_length extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Play Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=play_speech_length";
			$classification->name = "Play speech length";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/play_speech_length/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/play_speech_length/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/play_speech_length", "Play speech length") . "</h2>
						   <ul>
								<li>
									<a href='?tool=play_speech_length&action=instructions'>" . $this->language->translate("tools/play_speech_length", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=play_speech_length&action=display'>" . $this->language->translate("tools/play_speech_length", "Generate speech length profile") . "</a>
								</li>
								<li>
									<a href='?tool=play_speech_length&action=view_footprint'>" . $this->language->translate("tools/play_speech_length", "View speech length profile") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/play_speech_length", "help");
			
			return $output . "<p><a href='?tool=play_speech_length'>" . $this->language->translate("tools/play_speech_length", "Return to Play speech length") . "</a></p>";
				
		}
		
		private function view_footprint(){
		
			if(count($_POST)!==0){
			
				$output = "<img src='data/play/files/play_speech_length/" . $_POST['playfile'] . "' />";		
						
				return $output . "<p><a href='?tool=play_speech_length'>" . $this->language->translate("tools/play_speech_length", "Return to Play speech length") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/play/files/play_speech_length");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/play_speech_length", "Play speech length") . "</h2>
							   <p>" . $this->language->translate("tools/play_speech_length", "Select an existing footprint") . "</p>
							   <form action='' method='POST'>
									<select name='playfile'>
										<option>" . $this->language->translate("tools/play_speech_length", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . str_replace(".csv","",$plain) . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/play_speech_length", "view") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/play_speech_length", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = $file_process->file_get_all("data/play/files/" . $_POST['playfile']);	
				
				$play_data = new Stdclass();
				$play_fingerprint = array();
				$curr_speaker = "";
				$curr_speaker_words = 0;
				$total_words = 0;
				$no_speakers = 0;

				$rows = explode("\n", $data);				
				array_shift($rows);
				
				$play_fingerprint = array();
				$play_speakers = array();
				$words_count = 0;
				$max_words = 0;

				while($row = array_shift($rows)){
				
					$inner_data = explode("	", $row);
					
					$speaker = substr($inner_data[3],0,strlen($inner_data[3]));
				
					$text = str_replace("'", "", str_replace("  ", " ", str_replace("!", "", str_replace(".", "", str_replace(",", "", str_replace("?", "", str_replace(":", "", str_replace(";", "", str_replace(")", "", $inner_data[4])))))))));
					
					if(trim($text)!=""&&trim($speaker)!=""){
						
						$words_count += count($text);
					
						if(isset($play_fingerprint[count($play_fingerprint)-1])){
					
						if($play_fingerprint[count($play_fingerprint)-1][0]!=$speaker){
							
							array_push($play_fingerprint,array($speaker, $words_count));
								
							if($words_count>=$max_words){
								
								$max_words = $words_count;
								
							}
								
							$words_count = 0;
							
						}
						
						}else{
						
							array_push($play_fingerprint,array($speaker, $words_count));
								
							if($words_count>=$max_words){
								
								$max_words = $words_count;
								
							}
						
						}
						
					}
			
				}
				
				$im = imagecreatetruecolor($max_words+100,count($play_fingerprint)+100);
				$white = imagecolorallocate($im, 255,255,255);
								
				$x_pos = 10;
				$y_pos = 10;
				
				for($x=0;$x<count($play_fingerprint);$x++){
				
					imageline($im, $x_pos, $y_pos+$x, $x_pos+$play_fingerprint[$x][1], $y_pos+$x, $white);
				
				}	
				
				$file_process->file_image_create("data/play/files/play_speech_length/" . str_replace(".","",$_POST['playfile']) . ".jpg", "jpeg", $im);
						
				$output = "<img src='data/play/files/play_speech_length/" . str_replace(".","",$_POST['playfile']) . ".jpg' />";		
						
				return $output . "<p><a href='?tool=play_speech_length'>" . $this->language->translate("tools/play_speech_length", "Return to Play speech length") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder_files_only("data/play/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/play_speech_length", "Play speech length") . "</h2>
							   <p>" . $this->language->translate("tools/play_speech_length", "Select a play") . "</p>
							   <form action='' method='POST'>
									<select name='playfile'>
										<option>" . $this->language->translate("tools/play_speech_length", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . str_replace(".csv","",$plain) . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/play_speech_length", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/play_speech_length", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}