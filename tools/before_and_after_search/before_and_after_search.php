<?PHP

	class before_and_after_search extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Text Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=before_and_after_search";
			$classification->name = "Before and After search";
			
			return $classification;
		
		}
		
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/before_and_after_search/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/before_and_after_search/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/before_and_after_search", "Before and After search") . "</h2>
						   <ul>
								<li>
									<a href='?tool=before_and_after_search&action=instructions'>" . $this->language->translate("tools/before_and_after_search", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=before_and_after_search&action=search'>" . $this->language->translate("tools/before_and_after_search", "Search") . "</a>
								</li>
								<li>
									<a href='?tool=before_and_after_search&action=compare'>" . $this->language->translate("tools/before_and_after_search", "Compare") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/before_and_after_search", "help");
			
			return $output . "<p><a href='?tool=before_and_after_search'>" . $this->language->translate("tools/before_and_after_search", "Return to Before and After search") . "</a></p>";
				
		}
		
		private function search(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/before_and_after/files/" . $_POST['bandafile']));
			
				if(isset($data->{$_POST['searchword']})){
				
					$before = $data->{$_POST['searchword']}->before;
					$after = $data->{$_POST['searchword']}->after;
				
				}else{
				
					$output = "<p>" . $this->language->translate("tools/before_and_after_search", "No results found for this word") . "</p>";
					return $output . "<p><a href='?tool=before_and_after_search'>" . $this->language->translate("tools/before_and_after_search", "Return to Before and After search") . "</a></p>";
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/before_and_after_search", "Search results for ") . " " . $_POST['searchword'] . "</h2>";
				$output .= "<p><strong>" . $this->language->translate("tools/before_and_after_search", "Before") . "</strong> : </p>";

				foreach($before as $word => $occurrence){
				
					$output .= "<p><span>" . $word . " : " . $occurrence . "</span></p>";
				
				}

				$output .= "<p><strong>" . $this->language->translate("tools/before_and_after_search", "After") . "</strong> : </p>";

				foreach($after as $word => $occurrence){
				
					$output .= "<p><span>" . $word . " : " . $occurrence . "</span></p>";
				
				}
				
				return $output . "<p><a href='?tool=before_and_after_search'>" . $this->language->translate("tools/before_and_after_search", "Return to Before and After search") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/before_and_after/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/before_and_after_search", "Search a Before and After file") . "</h2>
							   <p>" . $this->language->translate("tools/before_and_after_search", "Choose a Before and After file to search") . "</p>
							   <form action='' method='POST'>
									<select name='bandafile'>
										<option>" . $this->language->translate("tools/before_and_after_search", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<label>
										" . $this->language->translate("tools/before_and_after_search", "Choose a word") . "
									</label>									
									<input type='text' name='searchword' />
									<input type='submit' value='" . $this->language->translate("tools/before_and_after_search", "Search") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/before_and_after_search", "No before and after search files exist. Please create one before using this tool") . "</p>";				
				
				}
			
			}
		
		}
		
		private function compare(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/before_and_after/files/" . $_POST['bandafileone']));
			
				if(isset($data->{$_POST['searchword']})){
				
					$before = $data->{$_POST['searchword']}->before;
					$after = $data->{$_POST['searchword']}->after;
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/before_and_after_search", "Search results for") . " " . $_POST['searchword'] . "</h2>";
				$output .= "<div class='baa_compare'><p>" . $this->language->translate("tools/before_and_after_search", "Results for") . " " . $_POST['bandafileone'] . "</p>";
				$output .= "<p><strong>" . $this->language->translate("tools/before_and_after_search", "Before") . "</strong> : </p>";

				foreach($before as $word => $occurrence){
				
					$output .= "<p><span>" . $word . " : " . $occurrence . "</span></p>";
				
				}

				$output .= "<p><strong>" . $this->language->translate("tools/before_and_after_search", "After") . "</strong> : </p>";

				foreach($after as $word => $occurrence){
				
					$output .= "<p><span>" . $word . " : " . $occurrence . "</span></p>";
				
				}
				
				$output .="</div>";
				
				$data = unserialize($file_process->file_get_all("data/before_and_after/files/" . $_POST['bandafiletwo']));
			
				if(isset($data->{$_POST['searchword']})){
				
					$before = $data->{$_POST['searchword']}->before;
					$after = $data->{$_POST['searchword']}->after;
				
				}
				
				$output .= "<div class='baa_compare'><p>" . $this->language->translate("tools/before_and_after_search", "Results for") . " " . $_POST['bandafiletwo'] . "</p>";
				$output .= "<p><strong>" . $this->language->translate("tools/before_and_after_search", "Before") . "</strong> : </p>";

				foreach($before as $word => $occurrence){
				
					$output .= "<p><span>" . $word . " : " . $occurrence . "</span></p>";
				
				}

				$output .= "<p><strong>" . $this->language->translate("tools/before_and_after_search", "After") . "</strong> : </p>";

				foreach($after as $word => $occurrence){
				
					$output .= "<p><span>" . $word . " : " . $occurrence . "</span></p>";
				
				}
				
				$output .="</div>";
				
				return $output . "<p><a href='?tool=before_and_after_search'>" . $this->language->translate("tools/before_and_after_search", "Return to before and after search") . "</a></p>";
			
			}else{
		
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/before_and_after/files/");
				$second_plain_text = $plain_text;
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/before_and_after_search", "Compare two before and after files") . "</h2>
							   <form action='' method='POST'>
									<label>
										" . $this->language->translate("tools/before_and_after_search", "File one") . "
									</label>
									<select name='bandafileone'>
										<option>" . $this->language->translate("tools/before_and_after_search", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
					
					$output .= "<label>
									" . $this->language->translate("tools/before_and_after_search", "File two") . "
								</label>
								<select name='bandafiletwo'>
										<option>" . $this->language->translate("tools/before_and_after_search", "Select a file") . "</output>";
								
					while($plain = array_pop($second_plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<label>
										" . $this->language->translate("tools/before_and_after_search", "Choose a word") . "
									</label>									
									<input type='text' name='searchword' />
									<input type='submit' value='" . $this->language->translate("tools/before_and_after_search", "Search") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/before_and_after_search", "No before and after search files exist. Please create one before using this tool") . "</p>";				
				
				}
							
			}
		
		}
		
	}