<?PHP

	class get_web_page extends data{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
			
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Generic Data Formats";
			$classification->column = "Web Page Download";
			$classification->link = "?data=get_web_page";
			$classification->name = "Web Page Management and Download";
			
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

				$output = "<h2>" . $this->language->translate("data/get_web_page", "Web Page Management") . "</h2>
						   <ul>
								<li>
									<a href='?data=get_web_page&action=instructions'>" . $this->language->translate("data/get_web_page", "Instructions") . "</a>
								</li>
								<li>
									<a href='?data=get_web_page&action=create'>" . $this->language->translate("data/get_web_page", "Download web page") . "</a>
								</li>
								<li>
									<a href='?data=get_web_page&action=upload'>" . $this->language->translate("data/get_web_page", "Upload web page") . "</a>
								</li>
								<li>
									<a href='?data=get_web_page&action=manage'>" . $this->language->translate("data/get_web_page", "Manage web page") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("data/get_web_page", "help");
			
			return $output . "<p><a href='?data=get_web_page'>" . $this->language->translate("data/get_web_page", "Return to Web Page Management") . "</a></p>";
				
		}
		
		private function create(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				$ch = curl_init(); 
				curl_setopt($ch, CURLOPT_URL, $_POST['url']);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
				$data = curl_exec($ch); 
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
				curl_close($ch); 
				
				if(!$data){ 
					$output = "<p><strong>" . $this->language->translate("data/get_web_page", "Download failed") . "</strong></p>";
				} 
				
				if($httpCode > 400){ 
						
						$output .= "<p><strong>" . $this->language->translate("data/get_web_page", "Error code") . " " . $httpCode . "</strong></p>";
					
				}
				
				$output = "<p><strong>" . $this->language->translate("data/get_web_page", "Download successful") . "</strong></p>";
				
				$response = $file_process->create_file("data/get_web_page/files/" . str_replace(" ", "_", $_POST['filename']), $data);
				return "<h2>" . $response[1] . "</h2><a href='?data=get_web_page'>" . $this->language->translate("data/get_web_page", "Return to Web Page Management") . "</a>";
			
			}else{
		
				return "<h2>" . $this->language->translate("data/get_web_page", "Download a web page") . "</h2>
							<form action='' method='POST'>
								<input type='text' size=100 value='" . $this->language->translate("data/get_web_page", "Enter a URL here") . "' name='url' /><br />
								<label>
									Name for new file
								</label>
								<input type='text' value='" . $this->language->translate("data/get_web_page", "Enter a filename here") . "' name='filename' /><br />
								<input type='submit' value='" . $this->language->translate("data/get_web_page", "Create") . "' />
							</form>";
							
			}
		
		}
		
		private function upload(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($_FILES['uploadfile']['error']!=0){
				
					$response = $file_process->file_upload_error($_FILES['uploadfile']['error']);
				
				}else{
				
					$response = $file_process->upload_file("data/get_web_page/files/", $_POST['filename'], $_FILES);
				
				}
				
				return "<h2>" . $response[1] . "</h2><a href='?data=get_web_page'>" . $this->language->translate("data/get_web_page", "Return to Web Page Management") . "</a>";
			
			}else{
		
				return "<h2>" . $this->language->translate("data/get_web_page", "Upload a Web page") . "</h2>
							<form enctype='multipart/form-data' action='' method='POST'>
								<input type='file' name='uploadfile' />
								<label>
									" . $this->language->translate("data/get_web_page", "Name for new file") . "
								</label>
								<input type='text' value='" . $this->language->translate("data/get_web_page", "Enter a file name here") . "' name='filename' /><br />
								<input type='submit' value='" . $this->language->translate("data/get_web_page", "Upload") . "' />
							</form>";
							
			}
		
		}
		
		private function manage(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				$response = array();
				
				foreach($_POST as $key => $value){
				
					if($value=="on"){
				
						$response[$key] = $file_process->delete_file("data/get_web_page/files/" . $key);
						
					}
					
				}
				
				$output = "";
				
				foreach($response as $file => $values){
				
					$output .= "<p>" . $file . " : " . $values[1] . "</p>";
				
				}
				
				$output .= "<p><a href='?data=get_web_page'>" . $this->language->translate("data/get_web_page", "Return to Web Page Management") . "</a></p>";
				
				return $output;
			
			}else{
		
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();		
				
				$files = $file_process->read_folder("data/get_web_page/files/");
				
				if(count($files)!==0){
		
					$output = "<h2>Manage Plain text files</h2>
								<form enctype='multipart/form-data' action='' method='POST'>";
					
					while($file = array_pop($files)){
						
						$output .= "<label>" . $this->language->translate("data/get_web_page", "File") . ": " . $file . "</label> - " . $this->language->translate("data/get_web_page", " tick box to delete") . " <input name='" . $file . "' type='checkbox' /><br />"; 
					
					}
										
					$output .=	"<input type='submit' value='Delete' />
								</form>";
				}else{
				
					$output = "<p>" . $this->language->translate("data/get_web_page", "No files have been uploaded yet") . " - <a href='?data=get_web_page'>" . $this->language->translate("data/get_web_page", "Return to plain text") . "</a></p>";
				
				}
				
				return $output;
			
			}
		
		}
		
	}