<?PHP

	class harpax extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Text Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=harpax";
			$classification->name = "Harpax";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/harpax/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/harpax/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/harpax", "List Harpax") . "</h2>
						   <ul>
								<li>
									<a href='?tool=harpax&action=instructions'>" . $this->language->translate("tools/harpax", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=harpax&action=display'>" . $this->language->translate("tools/harpax", "Display") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/harpax", "help");
			
			return $output . "<p><a href='?tool=harpax'>" . $this->language->translate("tools/harpax", "Return to Harpax") . "</a></p>";
				
		}
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = $file_process->file_get_all("data/plain_text/files/" . $_POST['harpaxfile']);	
				$words = explode(" ", strip_tags(str_replace("\n"," ",strtolower($data))));
			
				$element_data = new StdClass();
			
				foreach($words as $word){
				
					$temp = explode("-", $word);
					
					$word = array_shift($temp);
					
					while($hyphen = array_shift($temp)){
					
						array_push($words,$hyphen);
					
					}

					$word = trim($word);
					
					$last = substr($word,strlen($word)-1,1);
					
					while($last=="!"||$last=="."||$last==","||$last=="?"||$last==":"||$last==";"||$last==")"||$last=="'"||$last=="\""||$last=="`"){
						
						$word = substr($word,0,strlen($word)-1);
						$last = substr($word,strlen($word)-1,1);
						
					}
						
					$first = substr($word,0,1);
					
					while($first=="!"||$first=="."||$first==","||$first=="?"||$first==":"||$first==";"||$first==")"||$first=="("||$first=="'"||$first=="\""||$first=="`"){
					
						$word = substr($word,1,strlen($word)-1);
						$first = substr($word,strlen($word)-1,1);
						
					}
				
					if($word!=""){
						
						if(isset($element_data->{$word})){
							
							$element_data->{$word}++;
							
						}else{
							
							$element_data->{$word} = 1;
							
						}
					
					}
				
				}
				
				$harpaxwords = array();
				
				foreach($element_data as $key => $value){
				
					if($value==1){

						array_push($harpaxwords, $key);
						
					}
				
				}
				
				sort($harpaxwords);
				
				$output = "<h2>" . $this->language->translate("tools/harpax", "Zipf Display for file ") . " : " . $_POST['harpaxfile'] . "</h2>";
				
				$output .= "<p>" . $this->language->translate("tools/harpax", "Number of Harpax ") . count($harpaxwords) . "</p>";
				
				foreach($harpaxwords as $word){
				
					$output .= "<p>" . $word . "</p>";
				
				}
				
				return $output . "<p><a href='?tool=harpax'>" . $this->language->translate("tools/harpax", "Return to Harpax") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/plain_text/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/harpax", "Find Harpax or Unique words") . "</h2>
							   <p>" . $this->language->translate("tools/harpax", "Choose a plain text file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='harpaxfile'>
										<option>" . $this->language->translate("tools/harpax", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/harpax", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/harpax", "No Plain text files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}