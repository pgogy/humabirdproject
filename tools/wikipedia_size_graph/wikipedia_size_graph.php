<?PHP

	class wikipedia_size_graph extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Wikipedia Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=wikipedia_size_graph";
			$classification->name = "Wikipedia Page Size Graph";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/wikipedia_size_graph/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/wikipedia_size_graph/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/wikipedia_size_graph", "Wikipedia Size Graph") . "</h2>
						   <ul>
								<li>
									<a href='?tool=wikipedia_size_graph&action=instructions'>" . $this->language->translate("tools/wikipedia_size_graph", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=wikipedia_size_graph&action=display'>" . $this->language->translate("tools/wikipedia_size_graph", "Display size graph") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/wikipedia_size_graph", "help");
			
			return $output . "<p><a href='?tool=wikipedia_size_graph'>" . $this->language->translate("tools/wikipedia_size_graph", "Return to Wikipedia Size Graph") . "</a></p>";
				
		}
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/wikipedia_download/files/aggregate/" . $_POST['wikifile'])){
				
					$data = unserialize($file_process->file_get_all("data/wikipedia_download/files/aggregate/" . $_POST['wikifile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/wikipedia_size_graph", "Wikipedia edit Display for file ") . " : " . $_POST['wikifile'] . "</h2>";
				
				$size = array();
				
				$max = 0;
				$min = 1000000;
				
				$data = array_reverse($data);
				
				for($x=0;$x<count($data);$x++){
					
					if(isset($data[$x+1])){
				
						$size[$x] = $data[$x]['size'];
					
						if($size[$x] > $max){
						
							$max = $size[$x];
						
						}
						
						if($size[$x] < $min){
						
							$min = $size[$x];
						
						}
					
					}
				
				}
				
				$scale = ($max - $min);
				
				$im = imagecreatetruecolor(count($size)+10, 1010);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$x_pos = 5;
				
				for($x=0;$x<count($size);$x++){
				
					$percent = (($size[$x] - $min)/$scale) * 100;
				
					imageline($im, $x_pos, 1005 , $x_pos, 1005 - round($percent*10), $white);
					
					$x_pos += 1;
				
				}

				$data = $file_process->file_image_create("data/wikipedia_download/files/images/" . str_replace(".","",$_POST['wikifile']) . "_size.jpg", "jpeg", $im);
						
				$output .= "<img src='data/wikipedia_download/files/images/" . str_replace(".","",$_POST['wikifile']) . "_size.jpg' />";		
				
				return $output . "<p><a href='?tool=wikipedia_size_graph'>" . $this->language->translate("tools/wikipedia_size_graph", "Return to Wikipedia Size Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$edits = $file_process->read_folder("data/wikipedia_download/files/aggregate/");
				
				arsort($edits);
				
				if(count($edits)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/wikipedia_size_graph", "Display a Wikipedia page size graph") . "</h2>
							   <p>" . $this->language->translate("tools/wikipedia_size_graph", "Choose a Wikipedia Edit file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='wikifile'>
										<option>" . $this->language->translate("tools/wikipedia_size_graph", "Select a file") . "</output>";
								
					while($plain = array_pop($edits)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/wikipedia_size_graph", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/wikipedia_size_graph", "No Wikipedia download files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}