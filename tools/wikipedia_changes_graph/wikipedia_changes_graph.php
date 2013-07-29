<?PHP

	class wikipedia_changes_graph extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Wikipedia Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=wikipedia_changes_graph";
			$classification->name = "Wikipedia Edit changes Graph";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/wikipedia_changes_graph/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/wikipedia_changes_graph/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/wikipedia_changes_graph", "Wikipedia Edit changes Graph") . "</h2>
						   <ul>
								<li>
									<a href='?tool=wikipedia_changes_graph&action=instructions'>" . $this->language->translate("tools/wikipedia_changes_graph", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=wikipedia_changes_graph&action=display'>" . $this->language->translate("tools/wikipedia_changes_graph", "Display Changes graph") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/wikipedia_changes_graph", "help");
			
			return $output . "<p><a href='?tool=wikipedia_changes_graph'>" . $this->language->translate("tools/wikipedia_changes_graph", "Return to Wikipedia Changes graph") . "</a></p>";
				
		}
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/wikipedia_download/files/aggregate/" . $_POST['wikifile'])){
				
					$data = unserialize($file_process->file_get_all("data/wikipedia_download/files/aggregate/" . $_POST['wikifile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/wikipedia_changes_graph", "Wikipedia edit changes for file ") . " : " . $_POST['wikifile'] . "</h2>";
				$output .= "<p>" . $this->language->translate("tools/wikipedia_changes_graph", "Oldest change first") . "</p>";
				
				$changes = array();
				
				$max_change = 0;
				$max = 0;
				$min = 0;
				
				$data = array_reverse($data);
			
				for($x=0;$x<count($data);$x++){
				
					
					if(isset($data[$x+1])){
				
						$changes[$x] = $data[$x+1]['size'] - $data[$x]['size'];
					
						if($changes[$x] > $max){
						
							$max = $changes[$x];
						
						}
						
						if($changes[$x] < $min){
						
							$min = $changes[$x];
						
						}
					
					}
				
				}
				
				$height = $max - $min;
				
				$im = imagecreatetruecolor(count($changes)+10, 1010);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$x_pos = 5;
				
				for($x=0;$x<count($changes);$x++){
				
					$percent = ($changes[$x] / $height) * 100;
				
					imageline($im, $x_pos, 502 , $x_pos, 502 - round($percent*10), $white);
					
					$x_pos += 1;
				
				}

				$data = $file_process->file_image_create("data/wikipedia_download/files/images/" . str_replace(".","",$_POST['wikifile']) . "_changes.jpg", "jpeg", $im);
						
				$output .= "<img src='data/wikipedia_download/files/images/" . str_replace(".","",$_POST['wikifile']) . "_changes.jpg' />";		
				
				return $output . "<p><a href='?tool=wikipedia_changes_graph'>" . $this->language->translate("tools/wikipedia_changes_graph", "Return to Wikipedia Editors Display Graph") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$edits = $file_process->read_folder("data/wikipedia_download/files/aggregate/");
				
				arsort($edits);
				
				if(count($edits)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/wikipedia_changes_graph", "Display a Wikipedia Edits file") . "</h2>
							   <p>" . $this->language->translate("tools/wikipedia_changes_graph", "Choose a Wikipedia Edit file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='wikifile'>
										<option>" . $this->language->translate("tools/wikipedia_changes_graph", "Select a file") . "</output>";
								
					while($plain = array_pop($edits)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/wikipedia_changes_graph", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/wikipedia_changes_graph", "No Wikipedia download files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}