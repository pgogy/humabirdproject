<?PHP

	class wikipedia_editors_display_graph_editors extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Wikipedia Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=wikipedia_editors_display_graph_editors";
			$classification->name = "Wikipedia Size Graph Editor Highlight";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/wikipedia_editors_display_graph_editors/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/wikipedia_editors_display_graph_editors/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/wikipedia_editors_display_graph_editors", "Wikipedia Editors Display Graph - Highlighted editor") . "</h2>
						   <ul>
								<li>
									<a href='?tool=wikipedia_editors_display_graph_editors&action=instructions'>" . $this->language->translate("tools/wikipedia_editors_display_graph_editors", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=wikipedia_editors_display_graph_editors&action=display'>" . $this->language->translate("tools/wikipedia_editors_display_graph_editors", "Display Editor graph") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/wikipedia_editors_display_graph_editors", "help");
			
			return $output . "<p><a href='?tool=wikipedia_editors_display_graph_editors'>" . $this->language->translate("tools/wikipedia_editors_display_graph_editors", "Return to Wikipedia Editors Display Graph") . "</a></p>";
				
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
				
				$users = array();
				
				for($x=0;$x<count($data);$x++){
				
					if(isset($users[$data[$x]['user']])){
					
						$users[$data[$x]['user']]++;
					
					}else{
					
						$users[$data[$x]['user']] = 1;
					
					}
					
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
				
				arsort($users);
				
				$usernames = array_keys($users);
				
				$top_user = array_shift($usernames);
				
				$scale = ($max - $min);
				
				$im = imagecreatetruecolor(count($size)+10, 1010);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$x_pos = 5;
				
				for($x=0;$x<count($size);$x++){
				
					$percent = (($size[$x] - $min)/$scale) * 100;
					
					if($data[$x]['user']==$top_user){
					
						imageline($im, $x_pos, 1005 , $x_pos, 1005 - round($percent*10), $red);
					
					}else{
				
						imageline($im, $x_pos, 1005 , $x_pos, 1005 - round($percent*10), $white);
						
					}
					
					$x_pos += 1;
				
				}

				$data = $file_process->file_image_create("data/wikipedia_download/files/images/" . str_replace(".","",$_POST['wikifile']) . "_editors_highlight.jpg", "jpeg", $im);
						
				$output = "<img src='data/wikipedia_download/files/images/" . str_replace(".","",$_POST['wikifile']) . "_editors_highlight.jpg' />";		
				
				return $output . "<p><a href='?tool=wikipedia_editors_display_graph_editors'>" . $this->language->translate("tools/wikipedia_editors_display_graph_editors", "Return to Wikipedia Editors Display Graph (highlighted)") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$edits = $file_process->read_folder("data/wikipedia_download/files/aggregate/");
				
				arsort($edits);
				
				if(count($edits)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/wikipedia_editors_display_graph_editors", "Display a Wikipedia Edits file") . "</h2>
							   <p>" . $this->language->translate("tools/wikipedia_editors_display_graph_editors", "Choose a Wikipedia Edit file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='wikifile'>
										<option>" . $this->language->translate("tools/wikipedia_editors_display_graph_editors", "Select a file") . "</output>";
								
					while($plain = array_pop($edits)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/wikipedia_editors_display_graph_editors", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/wikipedia_editors_display_graph_editors", "No Wikipedia download files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}