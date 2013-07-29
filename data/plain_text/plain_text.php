<?PHP

	class plain_text extends data{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
			
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Generic Data Formats";
			$classification->column = "Plain text";
			$classification->link = "?data=plain_text";
			$classification->name = "Plain text";
			
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

				$output = "<h2>Plain Text File Management</h2>
						   <ul>
								<li>
									<a href='?data=plain_text&action=instructions'>" . $this->language->translate("data/plain_text", "Instructions") . "</a>
								</li>
								<li>
									<a href='?data=plain_text&action=create'>" . $this->language->translate("data/plain_text", "Create new file") . "</a>
								</li>
								<li>
									<a href='?data=plain_text&action=edit'>" . $this->language->translate("data/plain_text", "Edit file") . "</a>
								</li>
								<li>
									<a href='?data=plain_text&action=upload'>" . $this->language->translate("data/plain_text", "Upload file") . "</a>
								</li>
								<li>
									<a href='?data=plain_text&action=manage'>" . $this->language->translate("data/plain_text", "Manage file") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("data/plain_text", "help");
			
			return $output . "<p><a href='?data=plain_text'>" . $this->language->translate("data/plain_text", "Return to plain text") . "</a></p>";
				
		}
		
		private function create(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$response = $file_process->create_file("data/plain_text/files/" . str_replace(" ", "_", $_POST['filename']), $_POST['data']);
				return "<h2>" . $response[1] . "</h2><a href='?data=plain_text'>" . $this->language->translate("data/plain_text", "Return to plain text") . "</a>";
			
			}else{
		
				return "<h2>Create a plain text file</h2>
							<form action='' method='POST'>
								<textarea name='data' style='width:100%; height:400px'>
									" . $this->language->translate("data/plain_text", "Add Plain text here") . "
								</textarea>
								<label>
									Name for new file
								</label>
								<input type='text' value='" . $this->language->translate("data/plain_text", "Enter a filename here") . "' name='filename' /><br />
								<input type='submit' value='" . $this->language->translate("data/plain_text", "Create") . "' />
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
				
					$response = $file_process->upload_file("data/plain_text/files/", $_POST['filename'], $_FILES);
				
				}
				
				return "<h2>" . $response[1] . "</h2><a href='?data=plain_text'>" . $this->language->translate("data/plain_text", "Return to plain text") . "</a>";
			
			}else{
		
				return "<h2>" . $this->language->translate("data/plain_text", "Upload a plain text file") . "</h2>
							<form enctype='multipart/form-data' action='' method='POST'>
								<input type='file' name='uploadfile' />
								<label>
									" . $this->language->translate("data/plain_text", "Name for new file") . "
								</label>
								<input type='text' value='" . $this->language->translate("data/plain_text", "Enter a file name here") . "' name='filename' /><br />
								<input type='submit' value='" . $this->language->translate("data/plain_text", "Upload") . "' />
							</form>";
							
			}
		
		}
		
		private function edit(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
			
				if(isset($_POST['edit'])){
				
					$output = "<h2>" . $this->language->translate("data/plain_text", "Edit text file") . "</h2>
								<form enctype='multipart/form-data' action='' method='POST'>";
								
					$output .= "<textarea name='file' style='width:100%; height:500px'>";
					
					$output .= $file_process->file_get_all("data/plain_text/files/" . $_POST['plaintextfile']);
				
					$output .= "</textarea>";
					
					$output .= "<input type='hidden' name='editor' />";
					$output .= "<input type='hidden' name='plaintextfile' value='" . $_POST['plaintextfile'] . "' />";
										
					$output .=	"<input type='submit' value='" . $this->language->translate("data/plain_text", "Save") . "' />
								</form>";
				
				}else if(isset($_POST['editor'])){
				
					$response = $file_process->update_file("data/plain_text/files/" . str_replace(" ", "_", $_POST['plaintextfile']), $_POST['file']);
					$output = $this->language->translate("data/plain_text", "File updated");
				
				}
				
				$output .= "<p><a href='?data=plain_text'>" . $this->language->translate("data/plain_text", "Return to plain text") . "</a></p>";
				
				return $output;
			
			}else{
		
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();		
				
				$files = $file_process->read_folder("data/plain_text/files/");
				
				if(count($files)!==0){
		
					$output = "<h2>" . $this->language->translate("data/plain_text", "Manage plain text files") . "</h2>
								<form enctype='multipart/form-data' action='' method='POST'>";
					
					$output .= "<select name='plaintextfile'>
										<option>" . $this->language->translate("data/plain_text", "Select a file") . "</output>";
								
					while($plain = array_pop($files)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
					
					$output .= "<input type='hidden' name='edit' />";
										
					$output .=	"<input type='submit' value='" . $this->language->translate("data/plain_text", "Edit") . "' />
								</form>";
				}else{
				
					$output = "<p>" . $this->language->translate("data/plain_text", "No files have been uploaded yet") . " - <a href='?data=plain_text'>" . $this->language->translate("data/plain_text", "Return to plain text") . "</a></p>";
				
				}
				
				return $output;
			
			}
		
		}
		
		private function manage(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				$response = array();
				
				foreach($_POST as $key => $value){
				
					if($value=="on"){
				
						$response[$key] = $file_process->delete_file("data/plain_text/files/" . $key);
						
					}
					
				}
				
				$output = "";
				
				foreach($response as $file => $values){
				
					$output .= "<p>" . $file . " : " . $values[1] . "</p>";
				
				}
				
				$output .= "<p><a href='?data=plain_text'>" . $this->language->translate("data/plain_text", "Return to plain text") . "</a></p>";
				
				return $output;
			
			}else{
		
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();		
				
				$files = $file_process->read_folder("data/plain_text/files/");
				
				if(count($files)!==0){
		
					$output = "<h2>" . $this->language->translate("data/plain_text", "Manage plain text files") . "</h2>
								<form enctype='multipart/form-data' action='' method='POST'>";
					
					while($file = array_pop($files)){
						
						$output .= "<label>" . $this->language->translate("data/plain_text", "File") . ": " . $file . "</label> - " . $this->language->translate("data/plain_text", " tick box to delete") . " <input name='" . $file . "' type='checkbox' /><br />"; 
					
					}
										
					$output .=	"<input type='submit' value='Delete' />
								</form>";
				}else{
				
					$output = "<p>" . $this->language->translate("data/plain_text", "No files have been uploaded yet") . " - <a href='?data=plain_text'>" . $this->language->translate("data/plain_text", "Return to plain text") . "</a></p>";
				
				}
				
				return $output;
			
			}
		
		}
		
	}