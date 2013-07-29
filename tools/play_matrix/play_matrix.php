<?PHP

	class play_matrix extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Play Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=play_matrix";
			$classification->name = "Play matrix Diagram";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/play_matrix/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/play_matrix/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/play_matrix", "Play matrix") . "</h2>
						   <ul>
								<li>
									<a href='?tool=play_matrix&action=instructions'>" . $this->language->translate("tools/play_matrix", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=play_matrix&action=display'>" . $this->language->translate("tools/play_matrix", "Generate Play matrix") . "</a>
								</li>
								<li>
									<a href='?tool=play_matrix&action=view_footprint'>" . $this->language->translate("tools/play_matrix", "View matrix") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/play_matrix", "help");
			
			return $output . "<p><a href='?tool=play_matrix'>" . $this->language->translate("tools/play_matrix", "Return to Play Matrix") . "</a></p>";
				
		}
		
		private function view_footprint(){
		
			if(count($_POST)!==0){
			
				$output = "<img src='data/play/files/play_matrix/" . $_POST['playfile'] . "' />";		
						
				return $output . "<p><a href='?tool=play_matrix'>" . $this->language->translate("tools/play_matrix", "Return to Play matrix") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/play/files/play_matrix");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/play_matrix", "Play matrix") . "</h2>
							   <p>" . $this->language->translate("tools/play_matrix", "Select an existing footprint") . "</p>
							   <form action='' method='POST'>
									<select name='playfile'>
										<option>" . $this->language->translate("tools/play_matrix", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . str_replace(".csv","",$plain) . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/play_matrix", "view") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/play_matrix", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
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
					
						if(isset($play_fingerprint[$speaker])){			
					
							$play_fingerprint[$speaker]++;
						
						}else{
						
							$play_fingerprint[$speaker] = 1;
							$no_speakers++;
						
						}
						
						$curr_speaker = $speaker;
					
						if($play_data[count($play_data)-1]!=$speaker){
					
							array_push($play_data,$speaker);
						
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
				
				arsort($play_interactions);
				
				$im = imagecreatetruecolor(360+(count($play_fingerprint)*35),400+(count($play_fingerprint)*35));
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);	
				
				$x_pos = 50;
				$y_pos = 400;
				$counter = 0;
				
				$interactions = array_keys($play_fingerprint);
				
				foreach($play_fingerprint as $key => $value){
				
					imagettftext ( $im , 15.0 , 0, $x_pos , $y_pos , $white , "core/misc/fonts/arial.ttf" , $key);
					imagefilledrectangle($im, ($y_pos-75), ($y_pos-35), ($y_pos-75)+35, $y_pos, $red);
					for($x=0;$x<count($interactions);$x++){
						if(isset($play_interactions[$key . "->" . $interactions[$x]])){
						
							imagettftext ( $im , 15.0 , 0, 332-(strlen($play_interactions[$key . "->" . $interactions[$x]])*2)+($counter*35) , 390 + ($x*35) , $white , "core/misc/fonts/arial.ttf" , $play_interactions[$key . "->" . $interactions[$x]]);
						
						}
					}
					$y_pos+=35;
					$counter++;
				
				}
				
				$y_pos=360;
				$x_pos=350;

				foreach($play_fingerprint as $key => $value){
				
					imagettftext ( $im , 15.0 , 90.0, $x_pos , $y_pos , $white , "core/misc/fonts/arial.ttf" , $key);
					$x_pos+=35;
				
				}	
						
				$file_process->file_image_create("data/play/files/play_matrix/" . str_replace(".","",$_POST['playfile']) . ".jpg", "jpeg", $im);
						
				$output = "<img src='data/play/files/play_matrix/" . str_replace(".","",$_POST['playfile']) . ".jpg' />";		
						
				return $output . "<p><a href='?tool=play_matrix'>" . $this->language->translate("tools/play_matrix", "Return to Play matrix") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder_files_only("data/play/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/play_matrix", "Play matrix") . "</h2>
							   <p>" . $this->language->translate("tools/play_matrix", "Select a play") . "</p>
							   <form action='' method='POST'>
									<select name='playfile'>
										<option>" . $this->language->translate("tools/play_matrix", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . str_replace(".csv","",$plain) . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/play_matrix", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/play_matrix", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}