<?PHP

	class ngram_compare extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Intertextual";
			$classification->column = "Tools";
			$classification->link = "?tool=ngram_compare";
			$classification->name = "Ngram compare";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/ngram_compare/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/ngram_compare/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/ngram_compare", "Ngram Comparison") . "</h2>
						   <ul>
								<li>
									<a href='?tool=ngram_compare&action=instructions'>" . $this->language->translate("tools/ngram_compare", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=ngram_compare&action=search'>" . $this->language->translate("tools/ngram_compare", "Search") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/ngram_compare", "help");
			
			return $output . "<p><a href='?tool=ngram_compare'>" . $this->language->translate("tools/ngram_compare", "Return to Ngram Compare") . "</a></p>";
				
		}
		
		private function search(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/ngram/files/" . $_POST['ngramfileone']));				
				$data_two = unserialize($file_process->file_get_all("data/ngram/files/" . $_POST['ngramfiletwo']));
				
				$output = "<h2>" . $this->language->translate("tools/ngram_compare", "Ngram results") . "</h2>";
				
				foreach($data as $ngram => $occurrences){
				
					if(isset($data_two[$ngram])){
					
						$output .= "<p><strong>" . str_replace("-"," ",$ngram) . "</strong></p><p>" . $this->language->translate("tools/ngram_compare", "Occurrences in") . " " . $_POST['ngramfileone'] . " " . $occurrences . " | " . $this->language->translate("tools/ngram_compare", "Occurrences in") . " in " . $_POST['ngramfiletwo'] . " " . $data_two[$ngram] . "</p>";
					
					}
				
				}
				
				return $output . "<p><a href='?tool=ngram_compare'>" . $this->language->translate("tools/ngram_compare", "Return to Ngram Compare") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/ngram/files/");
				arsort($plain_text);
				$second_plain_text = $plain_text;
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/ngram_compare", "Compare Ngram files") . "</h2>
							   <p>" . $this->language->translate("tools/ngram_compare", "Choose two ngram files") . "</p>
							   <form action='' method='POST'>
									<select name='ngramfileone'>
										<option>" . $this->language->translate("tools/ngram_compare", "Select an Ngram File") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
						
					$output .= "<select name='ngramfiletwo'>
										<option>" . $this->language->translate("tools/ngram_compare", "Select a second Ngram File") . "</output>";
								
					while($plain = array_pop($second_plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
				
					$output .= "<input type='submit' value='" . $this->language->translate("tools/ngram_compare", "Search") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/ngram_compare", "No Ngram files exist. Please create one before using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}