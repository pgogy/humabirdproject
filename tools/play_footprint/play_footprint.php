<?PHP

	class play_footprint extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Play Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=play_footprint";
			$classification->name = "Play Footprint";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/play_footprint/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/play_footprint/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/play_footprint", "Play footprint") . "</h2>
						   <ul>
								<li>
									<a href='?tool=play_footprint&action=instructions'>" . $this->language->translate("tools/play_footprint", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=play_footprint&action=display'>" . $this->language->translate("tools/play_footprint", "Generate Play Footprint") . "</a>
								</li>
								<li>
									<a href='?tool=play_footprint&action=view_footprint'>" . $this->language->translate("tools/play_footprint", "View Footprint") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/play_footprint", "help");
			
			return $output . "<p><a href='?tool=play_footprint'>" . $this->language->translate("tools/play_footprint", "Return to Play footprint") . "</a></p>";
				
		}
		
		private function view_footprint(){
		
			if(count($_POST)!==0){
			
				$output = "<img src='data/play/files/play_footprint/" . $_POST['playfile'] . "' />";		
						
				return $output . "<p><a href='?tool=play_footprint'>" . $this->language->translate("tools/play_footprint", "Return to play footprint") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/play/files/play_footprint");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/play_footprint", "Play footprint") . "</h2>
							   <p>" . $this->language->translate("tools/play_footprint", "Select an existing footprint") . "</p>
							   <form action='' method='POST'>
									<select name='playfile'>
										<option>" . $this->language->translate("tools/play_footprint", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . str_replace(".csv","",$plain) . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/play_footprint", "view") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/play_footprint", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = $file_process->file_get_all("data/play/files/" . $_POST['playfile']);	
				
				$play_data = new StdClass;
				$play_fingerprint = array();
				$curr_speaker = "";
				$curr_speaker_words = 0;
				$total_words = 0;
				$no_speakers = 0;

				$rows = explode("\n", $data);				
				array_shift($rows);
				
				while($row = array_shift($rows)){
				
					$inner_data = explode("	", $row);
					
					$speaker = substr($inner_data[3],0,strlen($inner_data[3]));
				
					$text = str_replace("'", "", str_replace("  ", " ", str_replace("!", "", str_replace(".", "", str_replace(",", "", str_replace("?", "", str_replace(":", "", str_replace(";", "", str_replace(")", "", $inner_data[4])))))))));
				
					if(trim($text)!=""&&trim($speaker)!=""){
		
						$no_words = explode(" ", strtolower(substr($text,0,strlen($text))));
					
						$total_words += count($no_words);
					
						if(isset($play_data->{$speaker})){			
							
							$play_data->{$speaker} += count($no_words);
						
						}else{
						
							$play_data->{$speaker} = count($no_words);
							$no_speakers++;
						
						}
						
						if($curr_speaker==""){
						
							$curr_speaker=$speaker;
						
						}
						
						if($curr_speaker==$speaker){
						
							$curr_speaker_words += count($no_words);
						
						}else{
						
							array_push($play_fingerprint, array($curr_speaker, $curr_speaker_words));
							$curr_speaker_words = count($no_words);
							$curr_speaker=$speaker;
						
						}
					
					}
			
				}
				
				$im = imagecreatetruecolor(round($total_words/3),($no_speakers * 25)+110);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$x_pos = 250;
				$speakers = array();
				
				while($data_set = array_shift($play_fingerprint)){
				
					if(!isset($speakers[$data_set[0]])){
					
						$speakers[$data_set[0]] = count($speakers)*25;
						imageline($im, 250, ($speakers[$data_set[0]]+22)+110, round($total_words/3), ($speakers[$data_set[0]]+22)+110, $white);
						imagettftext ( $im , 15.0 , 0 , 10 , ($speakers[$data_set[0]]+20)+110 , $white , "core/misc/fonts/arial.ttf" , $data_set[0]);
					
					}
					
					$add_x = 0;
					
					if($speakers[$data_set[0]]<3){
					
						$add_x = 1;
					
					}else{
					
						$add_x = round($data_set[1]/3);
					
					}
					
					imagefilledrectangle($im, $x_pos, ($speakers[$data_set[0]])+110 , $x_pos + $add_x, ($speakers[$data_set[0]]+20)+110 , $red );
					
					$x_pos += $add_x;
				
				}

				$data = $file_process->file_image_create("data/play/files/play_footprint/" . str_replace(".","",$_POST['playfile']) . ".jpg", "jpeg", $im);
						
				$output = "<img src='data/play/files/play_footprint/" . str_replace(".","",$_POST['playfile']) . ".jpg' />";		
						
				return $output . "<p><a href='?tool=play_footprint'>" . $this->language->translate("tools/play_footprint", "Return to play footprint") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder_files_only("data/play/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/play_footprint", "Play footprint") . "</h2>
							   <p>" . $this->language->translate("tools/play_footprint", "Select a play") . "</p>
							   <form action='' method='POST'>
									<select name='playfile'>
										<option>" . $this->language->translate("tools/play_footprint", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . str_replace(".csv","",$plain) . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/play_footprint", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/play_footprint", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}