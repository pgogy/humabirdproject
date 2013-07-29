<?PHP

	class play_pie_chart extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Play Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=play_pie_chart";
			$classification->name = "Play pie chart";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/play_pie_chart/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/play_pie_chart/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/play_pie_chart", "Play pie chart") . "</h2>
						   <ul>
								<li>
									<a href='?tool=play_pie_chart&action=instructions'>" . $this->language->translate("tools/play_pie_chart", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=play_pie_chart&action=display'>" . $this->language->translate("tools/play_pie_chart", "Generate pie chart") . "</a>
								</li>
								<li>
									<a href='?tool=play_pie_chart&action=view_footprint'>" . $this->language->translate("tools/play_pie_chart", "View pie chart") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/play_pie_chart", "help");
			
			return $output . "<p><a href='?tool=play_pie_chart'>" . $this->language->translate("tools/play_pie_chart", "Return to Play Pie Chart") . "</a></p>";
				
		}
		
		private function view_footprint(){
		
			if(count($_POST)!==0){
			
				$output = "<img src='data/play/files/play_pie_chart/" . $_POST['playfile'] . "' />";		
						
				return $output . "<p><a href='?tool=play_pie_chart'>" . $this->language->translate("tools/play_pie_chart", "Return to Play pie chart") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/play/files/play_pie_chart");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/play_pie_chart", "Play pie chart") . "</h2>
							   <p>" . $this->language->translate("tools/play_pie_chart", "Select an existing footprint") . "</p>
							   <form action='' method='POST'>
									<select name='playfile'>
										<option>" . $this->language->translate("tools/play_pie_chart", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . str_replace(".csv","",$plain) . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/play_pie_chart", "view") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/play_pie_chart", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
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
				
				while($row = array_shift($rows)){
				
					$inner_data = explode("	", $row);
					
					$speaker = substr($inner_data[3],0,strlen($inner_data[3]));
				
					$text = str_replace("'", "", str_replace("  ", " ", str_replace("!", "", str_replace(".", "", str_replace(",", "", str_replace("?", "", str_replace(":", "", str_replace(";", "", str_replace(")", "", $inner_data[4])))))))));
			
					if(trim($speaker)!=""&&trim($text)!=""){
					
						$no_words = explode(" ",$text);
						$total_words += count($no_words);
						
						if(isset($play_data->{$speaker})){			
							
							$play_data->{$speaker} += count($no_words);
						
						}else{
						
							$play_data->{$speaker} = count($no_words);
							$no_speakers++;
						
						}
				
					}
					
				}
				
				$im = imagecreatetruecolor(800,625 + ($no_speakers * 18));
				$white = imagecolorallocate($im, 255,255,255);
				$last_angle = 90.0;
				$speakers_y = 650;
				$degree_ratio = 360 / $total_words;
				
				$other_colour = imagecolorallocate($im, 255,255,255);
				$other_angle = 0;
				
				$significant = 0;
				
				foreach($play_data as $key => $value){
				
					if(($value*$degree_ratio)>1){
					
						$colour = imagecolorallocate($im, rand(10,240),rand(10,240),rand(10,240));

						imagefilledarc ($im , 400 , 400 , 450 , 450 , $last_angle , $last_angle+($value*$degree_ratio) , $colour, IMG_ARC_PIE );
						imagefilledrectangle($im, 170, $speakers_y-15 , 190, $speakers_y , $colour );
						imagettftext ( $im , 15.0 , 0 , 200 , $speakers_y , $white , "core/misc/fonts/arial.ttf" , $key);
						$speakers_y += 18;
						$significant++;
						
					}else{
					
						$other_angle += ($value*$degree_ratio);
					
					}

					$last_angle += (integer)$value*$degree_ratio;
				
				}	
				
				imagefilledarc ($im , 400 , 400 , 450 , 450 , $last_angle , $last_angle+($other_angle) , $other_colour, IMG_ARC_PIE );
				imagefilledrectangle($im, 170, $speakers_y-15 , 190, $speakers_y , $other_colour );
				imagettftext ( $im , 15.0 , 0 , 200 , $speakers_y , $white , "core/misc/fonts/arial.ttf" , "Others");
				
				$new_im = imagecreatetruecolor(800,625 + ($significant * 21));
				imagecopy($new_im,$im,0,0,0,0,800,625 + ($significant * 21));
						
				$file_process->file_image_create("data/play/files/play_pie_chart/" . str_replace(".","",$_POST['playfile']) . ".jpg", "jpeg", $im);
						
				$output = "<img src='data/play/files/play_pie_chart/" . str_replace(".","",$_POST['playfile']) . ".jpg' />";		
						
				return $output . "<p><a href='?tool=play_pie_chart'>" . $this->language->translate("tools/play_pie_chart", "Return to Play pie chart") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder_files_only("data/play/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/play_pie_chart", "Play pie chart") . "</h2>
							   <p>" . $this->language->translate("tools/play_pie_chart", "Select a play") . "</p>
							   <form action='' method='POST'>
									<select name='playfile'>
										<option>" . $this->language->translate("tools/play_pie_chart", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . str_replace(".csv","",$plain) . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/play_pie_chart", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/play_pie_chart", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}