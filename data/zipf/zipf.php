<?PHP

	class zipf extends data{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Text Analysis";
			$classification->column = "Formatted text";
			$classification->link = "?data=zipf";
			$classification->name = "Zipf";
			
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

				$output = "<h2>" . $this->language->translate("data/zipf", "Zipf File Management") . "</h2>
						   <ul>
								<li>
									<a href='?data=zipf&action=instructions'>" . $this->language->translate("data/zipf", "Instructions") . "</a>
								</li>
								<li>
									<a href='?data=zipf&action=create'>" . $this->language->translate("data/zipf", "Create new file") . "</a>
								</li>
								<li>
									<a href='?data=zipf&action=upload'>" . $this->language->translate("data/zipf", "Upload file") . "</a>
								</li>
								<li>
									<a href='?data=zipf&action=manage'>" . $this->language->translate("data/zipf", "Manage files") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("data/zipf", "help");
			
			return $output . "<p><a href='?data=zipf'>" . $this->language->translate("data/zipf", "Return to Zipf management") . "</a></p>";
				
		}
		
		private function create_zipf($post_data){
			
			if(trim($post_data['filename'])===""){
			
				return array(false, "Please set a filename");
			
			}
			
			require_once("core/file_handling/file_handling.php");
			$file_process = new file_handling();
			$data = $file_process->file_get_all("data/plain_text/files/" . $post_data['plaintextfile']);
			$words = explode(" ", str_replace("\n"," ",$data));
			foreach($words as $key => $value){
				$words[$key] = trim($value);
			}			
			$words = array_filter($words);
					
			$words_data = array();
			
			foreach($words as $word){
			
				$last_letter = substr($word,strlen($word)-1,1);
					
				while(!preg_match("/[A-Za-z0-9]/",$last_letter)){
					
					$word = substr($word,0,strlen($word)-1);
						
					if(strlen($word)==0){
						
						break;
							
					}
						
					$last_letter = substr($word,strlen($word)-1,1);
					
				}
					
				if($word!=""){
					
					$first_letter = substr($word,0,1);
						
					while(!preg_match("/[A-Za-z0-9]/",$first_letter)){
						
						$word = substr($word,1,strlen($word)-1);
						$first_letter = substr($word,0,1);
						
					}
						
					if(!isset($words_data[$word])){
						
						$words_data[$word]=1;
						
					}else{
							
						$words_data[$word]++;
						
					}			
					
				}
				
			}
			
			arsort($words_data);
			
			$response = $file_process->create_file("data/zipf/files/" . str_replace(" ", "_", $post_data['filename']), serialize($words_data));
			
			return $response;
		
		}
		
		private function create(){
		
			if(count($_POST)!==0){
			
				$response = $this->create_zipf($_POST);
				
				return "<h2>" . $response[1] . "</h2><p><a href='?data=zipf'>" . $this->language->translate("data/zipf", "Return to Zipf") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/plain_text/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("data/zipf", "Creating a Zipf file") . "</h2>
							   <p>" . $this->language->translate("data/zipf", "Choose a plain text file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='plaintextfile'>
										<option>" . $this->language->translate("data/zipf", "Select a plain text file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<label>
										" . $this->language->translate("data/zipf", "Enter a file name") . "
									</label>
									<input type='text' name='filename' />
									<input type='submit' value='" . $this->language->translate("data/zipf", "Create") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("data/zipf", "No plain texts files exist, please add one before using this tool") . "</p>";	
				
				}
			
			}
		
		}
		
		private function upload(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($_FILES['uploadfile']['error']!=0){
				
					$response = $file_process->file_upload_error($_FILES['uploadfile']['error']);
				
				}else{
				
					$response = $file_process->upload_file("data/zipf/files/", $_POST['filename'], $_FILES);
				
				}
				
				return "<h2>" . $response[1] . "</h2><a href='?data=zipf'>" . $this->language->translate("data/zipf", "Return to Zipf") . "</a>";
			
			}else{
		
				return "<h2>" . $this->language->translate("data/zipf", "Upload a Zipf file") . "</h2>
							<form enctype='multipart/form-data' action='' method='POST'>
								<input type='file' name='uploadfile' />
								<label>
									" . $this->language->translate("data/zipf", "Name for new file") . "
								</label>
								<input type='text' value='" . $this->language->translate("data/zipf", "Enter the file name") . "' name='filename' /><br />
								<input type='submit' value='" . $this->language->translate("data/zipf", "Upload") . "' />
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
				
						$response[$key] = $file_process->delete_file("data/zipf/files/" . $key);
						
					}
					
				}
				
				$output = "";
				
				foreach($response as $file => $values){
				
					$output .= "<p>" . $file . " : " . $values[1] . "</p>";
				
				}
				
				$output .= "<p><a href='?data=zipf'>" . $this->language->translate("data/zipf", "Return to Zipf") . "</a></p>";
				
				return $output;
			
			}else{
		
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();		
				
				$files = $file_process->read_folder("data/zipf/files/");
				
				if(count($files)!==0){
		
					$output = "<h2>" . $this->language->translate("data/zipf", "Manage Zipf files") . "</h2>
								<form enctype='multipart/form-data' action='' method='POST'>";
					
					while($file = array_pop($files)){
						
						$output .= "<label>" . $this->language->translate("data/zipf", "File") . " : " . $file . "</label> - " . $this->language->translate("data/zipf", "tick to delete") . " <input name='" . $file . "' type='checkbox' /><br />"; 
					
					}
										
					$output .=	"<input type='submit' value='Delete' />
								</form>";
				}else{
				
					$output = "<p>" . $this->language->translate("data/zipf", "No files have been uploaded yet") . ". <a href='?data=zipf'>" . $this->language->translate("data/zipf", "Return to Zipf") . "</a></p>";
				
				}
				
				return $output;
			
			}
		
		}
		
	}