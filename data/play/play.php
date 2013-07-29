<?PHP

	class play extends data{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
			
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Play Analysis";
			$classification->column = "Data";
			$classification->link = "?data=play";
			$classification->name = "Play Management";
			
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

				$output = "<h2>" . $this->language->translate("data/play", "Play management") . "</h2>
						   <ul>
								<li>
									<a href='?data=play&action=instructions'>" . $this->language->translate("data/play", "Instructions") . "</a>
								</li>
								<li>
									<a href='?data=play&action=upload'>" . $this->language->translate("data/play", "Upload file") . "</a>
								</li>
								<li>
									<a href='?data=play&action=manage'>" . $this->language->translate("data/play", "Manage file") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("data/play", "help");
			
			return $output . "<p><a href='?data=play'>" . $this->language->translate("data/play", "Return to Play Management") . "</a></p>";
				
		}
		
		private function create(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$response = $file_process->create_file("data/play/files/" . str_replace(" ", "_", $_POST['filename']), $_POST['data']);
				return "<h2>" . $response[1] . "</h2><a href='?data=play'>" . $this->language->translate("data/play", "Return to plain text") . "</a>";
			
			}else{
		
				return "<h2>Create a plain text file</h2>
							<form action='' method='POST'>
								<textarea name='data' style='width:100%; height:400px'>
									" . $this->language->translate("data/play", "Add Plain text here") . "
								</textarea>
								<label>
									Name for new file
								</label>
								<input type='text' value='" . $this->language->translate("data/play", "Enter a filename here") . "' name='filename' /><br />
								<input type='submit' value='" . $this->language->translate("data/play", "Create") . "' />
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
				
					$response = $file_process->upload_file("data/play/files/", $_POST['filename'], $_FILES);
				
				}
				
				return "<h2>" . $response[1] . "</h2><a href='?data=play'>Return to Plain text</a>";
			
			}else{
		
				return "<h2>Upload a plain text file</h2>
							<form enctype='multipart/form-data' action='' method='POST'>
								<input type='file' name='uploadfile' />
								<label>
									" . $this->language->translate("data/play", "Name for new file") . "
								</label>
								<input type='text' value='" . $this->language->translate("data/play", "Enter a file name here") . "' name='filename' /><br />
								<input type='submit' value='" . $this->language->translate("data/play", "Upload") . "' />
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
				
						$response[$key] = $file_process->delete_file("data/play/files/" . $key);
						
					}
					
				}
				
				$output = "";
				
				foreach($response as $file => $values){
				
					$output .= "<p>" . $file . " : " . $values[1] . "</p>";
				
				}
				
				$output .= "<p><a href='?data=play'>" . $this->language->translate("data/play", "Return to plain text") . "</a></p>";
				
				return $output;
			
			}else{
		
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();		
				
				$files = $file_process->read_folder("data/play/files/");
				
				if(count($files)!==0){
		
					$output = "<h2>" . $this->language->translate("data/play", "Manage plain text files") . "</h2>
								<form enctype='multipart/form-data' action='' method='POST'>";
					
					while($file = array_pop($files)){
						
						$output .= "<label>" . $this->language->translate("data/play", "File") . ": " . $file . "</label> - " . $this->language->translate("data/play", " tick box to delete") . " <input name='" . $file . "' type='checkbox' /><br />"; 
					
					}
										
					$output .=	"<input type='submit' value='Delete' />
								</form>";
				}else{
				
					$output = "<p>" . $this->language->translate("data/play", "No files have been uploaded yet") . " - <a href='?data=play'>" . $this->language->translate("data/play", "Return to plain text") . "</a></p>";
				
				}
				
				return $output;
			
			}
		
		}
		
	}