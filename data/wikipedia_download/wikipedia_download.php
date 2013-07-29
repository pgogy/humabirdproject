<?PHP

	class wikipedia_download extends data{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
			
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Wikipedia Analysis";
			$classification->column = "Wikipedia";
			$classification->link = "?data=wikipedia_download";
			$classification->name = "Wikipedia Download";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='look/" . $theme . "/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='look/" . $theme . "/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("data/wikipedia_download", "Wikipedia Download") . "</h2>
						   <ul>
								<li>
									<a href='?data=wikipedia_download&action=instructions'>" . $this->language->translate("data/wikipedia_download", "Instructions") . "</a>
								</li>
								<li>
									<a href='?data=wikipedia_download&action=download'>" . $this->language->translate("data/wikipedia_download", "Download Wikipedia Edits list") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("data/wikipedia_download", "help");
			
			return $output . "<p><a href='?data=wikipedia_download'>" . $this->language->translate("data/wikipedia_download", "Return to Wikipedia Download") . "</a></p>";
				
		}
		
		private function get_wikipedia_edits($url, $stem, $counter, $time){
		
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, $url . $stem);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
			curl_setopt($ch, CURLOPT_USERAGENT, 'HumaBird Project www.humabirdproject.org');
			$data = curl_exec($ch); 
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
			curl_close($ch);
			
			$xmlDoc = new DOMDocument();
			$xmlDoc->loadXML($data);
			$x = $xmlDoc->documentElement;
			
			$start = "";
			
			if($x->firstChild->firstChild->getAttribute("rvcontinue")){
			
				$start = $x->firstChild->firstChild->getAttribute("rvcontinue"); 
			
			}
			
			$xml = simplexml_load_string($data);
			
			$revisions = array();
			
			foreach($xml->query->pages->page->revisions->rev as $key){
			
				$rev = array();
			
				foreach($key->attributes() as $inner_key => $data){
				
					$rev[$inner_key] = (string)$data;
				
				}
			
				$revisions[] = $rev;
			
			}
			
			require_once("core/file_handling/file_handling.php");
			$file_process = new file_handling();
			$file_process->create_file("data/wikipedia_download/files/" . urlencode($_POST['title']) . "_" . $time . "_" . $counter, serialize($revisions));
			
			if($start!=""){
			
				$this->get_wikipedia_edits($url, "&rvstartid=" . $start, $counter+1, $time);
				
			}
		
		}
		
		private function aggregate($time){
		
			require_once("core/file_handling/file_handling.php");
			$file_process = new file_handling();
			$harvest_files = $file_process->read_folder("data/wikipedia_download/files/");
			
			$aggregate = array();
			
			while($file = array_shift($harvest_files)){
			
				if(count(explode($time, $file))!==1){
				
					array_push($aggregate, $file);
				
				}
			
			}
			
			$revisions = array();
			
			$counter = 0;
			
			while($file = array_shift($aggregate)){
			
				$counter++;
			
				$content = unserialize($file_process->file_get_all("data/wikipedia_download/files/" . $file));
				
				$revisions = array_merge($revisions, $content);
			
			}
			
			$file_process->create_file("data/wikipedia_download/files/aggregate/" . urlencode($_POST['title']) . "_" . $time, serialize($revisions));
			
			return array($counter,count($revisions));
		
		}
		
		private function download(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				$url = "http://" . $_POST['language'] . ".wikipedia.org/w/api.php?action=query&format=xml&prop=revisions&titles=" . urlencode($_POST['title']) . "&rvprop=timestamp%7Cuser%7Cflags%7Csize&rvlimit=500";
				
				$time = time();
				
				$this->get_wikipedia_edits($url, "", 1, $time); 				
				$response = $this->aggregate($time); 				
				
				$output = "<p><strong>" . $this->language->translate("data/wikipedia_download", "Download successful") . "</strong></p>";
				
				return "<h2>" . $response[1] . " " . $this->language->translate("data/wikipedia_download", "revisions downloaded") . "</h2><a href='?data=wikipedia_download'>" . $this->language->translate("data/wikipedia_download", "Return to Wikiepedia Download") . "</a>";
			
			}else{
			
				$output = "<h2>" . $this->language->translate("data/wikipedia_download", "Download Wikipedia edits") . "</h2>
							<form action='' method='POST'>";
			
				$output .= "<p>" . $this->language->translate("data/wikipedia_download", "Enter a language code - en for English Wikipedia") . "</p>
						<input type='text' size=100 value='" . $this->language->translate("data/wikipedia_download", "Enter the language code for the wikipedia you wish to search") . "' name='language' />
						<p>" . $this->language->translate("data/wikipedia_download", "Enter a Wikipedia Page title") . "</p>
						<input type='text' size=100 value='" . $this->language->translate("data/wikipedia_download", "Enter the page title") . "' name='title' />
						<input type='submit' value='" . $this->language->translate("data/wikipedia_download", "Download Wikipedia") . "' />
					</form>";
					
				return $output;
				
			}
		
		}
		
	}