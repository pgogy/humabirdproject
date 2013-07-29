<?PHP

	class word_distance_search extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Intertextual";
			$classification->column = "Tools";
			$classification->link = "?tool=word_distance_search";
			$classification->name = "Word Distance Search";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/word_distance_search/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/word_distance_search/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/word_distance_search", "Word distance search") . "</h2>
						   <ul>
								<li>
									<a href='?tool=word_distance_search&action=instructions'>" . $this->language->translate("tools/word_distance_search", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=word_distance_search&action=search'>" . $this->language->translate("tools/word_distance_search", "Search") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/word_distance_search", "help");
			
			return $output . "<p><a href='?tool=word_distance_search'>" . $this->language->translate("tools/word_distance_search", "Return to Word distance search") . "</a></p>";
				
		}
		
		private function search(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/word_distance/files/" . $_POST['wdfile']));
			
				if(isset($data[$_POST['searchwordone']])){
				
					$first = $data[$_POST['searchwordone']];
				
				}
				
				if(isset($data[$_POST['searchwordtwo']])){
				
					$second = $data[$_POST['searchwordtwo']];
				
				}
				
				if(!isset($first)||!isset($second)){
				
					$output = "";
				
					if(!isset($first)){
				
						$output .= "<p>" . $_POST['searchwordone'] . " " . $this->language->translate("tools/word_distance_search", " does not exist in the data") . "</p>";
						
					}
					
					if(!isset($second)){
				
						$output .= "<p>" . $_POST['searchwordtwo'] . " " . $this->language->translate("tools/word_distance_search", " does not exist in the data") . "</p>";
						
					}
					
					return $output . "<p><a href='?tool=word_distance_search'>" . $this->language->translate("tools/word_distance_search", "Return to Word Distance search") . "</a></p>";
				
				}
				
				$min = 1000000;
				
				$output = "";
				
				foreach($first as $location){
				
					$output .= "<p>" . $_POST['searchwordone'] . " " . $this->language->translate("tools/word_distance_search", "at") . " " . $location . "<ul>";
					
					foreach($second as $second_location){
					
						$distance = (max($location,$second_location) - min($location,$second_location));
						
						if($distance<$min){
						
							$min = $distance;
						
						}
					
						$output .= "<li>
								" . $_POST['searchwordtwo'] . " " . $this->language->translate("tools/word_distance_search", "at") . " " . $second_location . " : " . $this->language->translate("tools/word_distance_search", "Distance") . " " . $distance . "
							  </li>";
					
					}
					
					$output .= "</ul></p>";
				
				}
				
				$output = "<p> " . $this->language->translate("tools/word_distance_search", "The minimum distance between the words is") . " " . $min . "</p>" . $output;
				
				return $output . "<p><a href='?tool=word_distance_search'>" . $this->language->translate("tools/word_distance_search", "Return to Word Distance search") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/word_distance/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/word_distance_search", "Search a Word Distance file") . "</h2>
							   <p>" . $this->language->translate("tools/word_distance_search", "Choose a Word Distance file") . "</p>
							   <form action='' method='POST'>
									<select name='wdfile'>
										<option>" . $this->language->translate("tools/word_distance_search", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<label>
										" . $this->language->translate("tools/word_distance_search", "Choose the first word") . "
									</label>									
									<input type='text' name='searchwordone' /><br />
									<label>
										" . $this->language->translate("tools/word_distance_search", "Choose the second word") . "
									</label>									
									<input type='text' name='searchwordtwo' />
									<input type='submit' value='" . $this->language->translate("tools/word_distance_search", "Search") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/word_distance_search", "No Word Distance files exist, please add one before using this tool") . "</p>";				
				
				}
			
			}
		
		}
		
	}