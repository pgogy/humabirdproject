<?PHP

	class play_network extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Play Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=play_network";
			$classification->name = "Play Network Diagram";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/play_network/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/play_network/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/play_network", "Play network") . "</h2>
						   <ul>
								<li>
									<a href='?tool=play_network&action=instructions'>" . $this->language->translate("tools/play_network", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=play_network&action=display'>" . $this->language->translate("tools/play_network", "Generate Play network") . "</a>
								</li>
								<li>
									<a href='?tool=play_network&action=view_footprint'>" . $this->language->translate("tools/play_network", "View Network") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/play_network", "help");
			
			return $output . "<p><a href='?tool=play_network'>" . $this->language->translate("tools/play_network", "Return to Play Network") . "</a></p>";
				
		}
		
		private function view_footprint(){
		
			if(count($_POST)!==0){
			
				$output = "<img src='data/play/files/play_network/" . $_POST['playfile'] . "' />";		
						
				return $output . "<p><a href='?tool=play_network'>" . $this->language->translate("tools/play_network", "Return to Play network") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/play/files/play_network");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/play_network", "Play network") . "</h2>
							   <p>" . $this->language->translate("tools/play_network", "Select an existing footprint") . "</p>
							   <form action='' method='POST'>
									<select name='playfile'>
										<option>" . $this->language->translate("tools/play_network", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . str_replace(".csv","",$plain) . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/play_network", "view") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/play_network", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = $file_process->file_get_all("data/play/files/" . $_POST['playfile']);	
				
				$play_data = array();
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
			
					if(trim($speaker)!=""&&trim($text)!=""){
					
						$no_words = explode(" ", strtolower(substr($text,0,strlen($text))));
					
						if(isset($play_fingerprint[trim($speaker)])){			
								
								$play_fingerprint[trim($speaker)]++;
							
							}else{
							
								$play_fingerprint[trim($speaker)] = 1;
								$no_speakers++;
							
							}
							
							$curr_speaker = trim($speaker);
						
							if($play_data[count($play_data)-1]!=trim($speaker)){
						
								array_push($play_data,trim($speaker));
							
							}
						
						}else{
						
							if(count($play_data)!=0){
						
								if($play_data[count($play_data)-1]!="---BREAK---"){
							
									array_push($play_data,"---BREAK---");
								
								}
							
							}else{
							
								array_push($play_data,"---BREAK---");
								
							}
						
						}
					
				}
				
				foreach($play_data as $value){
				
					if(isset($curr_speaker)){
				
						if($value!="---BREAK---"&&$curr_speaker!="---BREAK---"){
						
							if(isset($play_interactions[$curr_speaker . "->" . $value])){
							
								$play_interactions[$curr_speaker . "->" . $value]++;
						
							}else{
							
								$play_interactions[$curr_speaker . "->" . $value] = 1;
							
							}
						
						}
					
					}
					
					$curr_speaker = $value;
				
				}
				
				$ratio = 360 / (count($play_fingerprint)-1);
				
				arsort($play_fingerprint);
				
				$im = imagecreatetruecolor(1500,1500);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$clock_point = 0;
				
				$keys = array_keys($play_fingerprint);
				
				$core = array_shift($keys);		
				
				$interactions = array_keys($play_fingerprint);
				
				for($x=0;$x<count($interactions);$x++){
					if(isset($play_interactions[$core . "->" . $interactions[$x]])){
						
						$y_pos = 750 + (integer)round( 375 * sin( deg2rad($ratio * $x) ) );
						$x_pos = 750 + (integer)round( 375 * cos( deg2rad($ratio * $x) ) );
						imageline($im, 750,750, $x_pos, $y_pos, $red);
						
					}
				}
				
				
				array_shift($play_fingerprint);
				
				foreach($play_fingerprint as $key => $value){
					
					$y_pos = 750 + (integer)round( 375 * sin( deg2rad($clock_point) ) );
					$x_pos = 750 + (integer)round( 375 * cos( deg2rad($clock_point) ) );
					
					for($x=0;$x<count($interactions);$x++){
					
						if(isset($play_interactions[$key . "->" . $interactions[$x]])){
						
							$line_y_pos = 750 + (integer)round( 375 * sin( deg2rad($ratio * $x) ) );
							$line_x_pos = 750 + (integer)round( 375 * cos( deg2rad($ratio * $x) ) );
							imageline($im, $x_pos, $y_pos, $line_x_pos, $line_y_pos, imagecolorallocate($im, (integer)(255),0,0));
							
						}
					}
				
					imagettftext ( $im , 12.0 , 0-$clock_point, $x_pos , $y_pos , $white , "core/misc/fonts/arial.ttf" , $key);
					$clock_point+=$ratio;
				
				}	
				
				imagettftext ( $im , 15.0 , $clock_point, 750 , 750 , $white , "core/misc/fonts/arial.ttf" , $core);
		
				$file_process->file_image_create("data/play/files/play_network/" . str_replace(".","",$_POST['playfile']) . ".jpg", "jpeg", $im);
						
				$output = "<img src='data/play/files/play_network/" . str_replace(".","",$_POST['playfile']) . ".jpg' />";		
						
				return $output . "<p><a href='?tool=play_network'>" . $this->language->translate("tools/play_network", "Return to Play network") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder_files_only("data/play/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/play_network", "Play network") . "</h2>
							   <p>" . $this->language->translate("tools/play_network", "Select a play") . "</p>
							   <form action='' method='POST'>
									<select name='playfile'>
										<option>" . $this->language->translate("tools/play_network", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . str_replace(".csv","",$plain) . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/play_network", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/play_network", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}