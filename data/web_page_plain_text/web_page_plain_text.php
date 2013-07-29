<?PHP

	class web_page_plain_text extends data{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
			
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Generic Data Formats";
			$classification->column = "Web Page Download";
			$classification->link = "?data=web_page_plain_text";
			$classification->name = "Web Page Conversion";
			
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

				$output = "<h2>" . $this->language->translate("data/web_page_plain_text", "Web Page Conversion") . "</h2>
						   <ul>
								<li>
									<a href='?data=web_page_plain_text&action=instructions'>" . $this->language->translate("data/web_page_plain_text", "Instructions") . "</a>
								</li>
								<li>
									<a href='?data=web_page_plain_text&action=convert'>" . $this->language->translate("data/web_page_plain_text", "Convert web page to plain text") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("data/web_page_plain_text", "help");
			
			return $output . "<p><a href='?data=web_page_plain_text'>" . $this->language->translate("data/web_page_plain_text", "Return to Web Page Conversion") . "</a></p>";
				
		}
		
		private function convert(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				$data = $file_process->file_get_all("data/get_web_page/files/" . $_POST['htmlfile']);	
				
				$response = $file_process->create_file("data/plain_text/files/" . $_POST['filename'], strip_tags($data));
				
				return "<h2>" . $this->language->translate("data/web_page_plain_text", "File converted") . "</h2><a href='?data=web_page_plain_text'>" . $this->language->translate("data/web_page_plain_text", "Return to Web Page Conversion") . "</a>";
			
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$web_page = $file_process->read_folder("data/get_web_page/files/");
				
				arsort($web_page);
				
				if(count($web_page)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/web_page_plain_text", "Web Page Conversion") . "</h2>
							   <p>" . $this->language->translate("tools/web_page_plain_text", "Select a play") . "</p>
							   <form action='' method='POST'>
									<select name='htmlfile'>
										<option>" . $this->language->translate("tools/web_page_plain_text", "Select a file") . "</output>";
								
					while($page = array_pop($web_page)){
					
						$output .= "<option value='" . $page . "'>" . $page . "</output>";
					
					}
	
					$output .=	"</select><br />
								<label>
									" . $this->language->translate("data/web_page_plain_text", "Name for a new file") . "
								</label>
								<input type='text' value='" . $this->language->translate("data/web_page_plain_text", "Enter a filename here") . "' name='filename' /><br />
									<input type='submit' value='" . $this->language->translate("tools/web_page_plain_text", "Convert") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/web_page_plain_text", "No Web pages exist, please create one for using this tool.") . "</p>";				
				
				}
		
				return $output;
							
			}
		
		}
		
	}