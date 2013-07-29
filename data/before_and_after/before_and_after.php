<?PHP

	class before_and_after extends data{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Intertextual";
			$classification->column = "Formatted text";
			$classification->link = "?data=before_and_after";
			$classification->name = "Before and After";
			
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

				$output = "<h2>" . $this->language->translate("data/before_and_after", "Before and After file management") . "</h2>
						   <ul>
								<li>
									<a href='?data=before_and_after&action=instructions'>" . $this->language->translate("data/before_and_after", "Instructions") . "</a>
								</li>
								<li>
									<a href='?data=before_and_after&action=create'>" . $this->language->translate("data/before_and_after", "Create New File") . "</a>
								</li>
								<li>
									<a href='?data=before_and_after&action=upload'>" . $this->language->translate("data/before_and_after", "Upload New File") . "</a>
								</li>
								<li>
									<a href='?data=before_and_after&action=manage'>" . $this->language->translate("data/before_and_after", "Manage Files") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("data/before_and_after", "help");
			
			return $output . "<p><a href='?data=before_and_after'>" . $this->language->translate("data/before_and_after", "Return to Before and After") . "</a></p>";
				
		}
		
		private function create_before_and_after($post_data){
			
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
			
			$before_and_after = new StdClass();
			$words_data = array();
					
			foreach($words as $position => $word){
			
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
					
					array_push($words_data, $word);		
				
				}
			
			}
			
			for($x=0;$x<count($words_data);$x++){
			
				if(trim($words_data[$x])!==""){
			
					if(!isset($before_and_after->{$words_data[$x]})){
					
						$before_and_after->{$words_data[$x]} = new StdClass();
						$before_and_after->{$words_data[$x]}->before = new StdClass();
						$before_and_after->{$words_data[$x]}->after = new StdClass();
					
					}
				
					if(isset($words_data[$x+1])){
					
						if(trim($words_data[$x+1])!==""){
						
							if(isset($before_and_after->{$words_data[$x]}->after->{$words_data[$x+1]})){
						
								$before_and_after->{$words_data[$x]}->after->{$words_data[$x+1]}++;
								
							}else{
							
								$before_and_after->{$words_data[$x]}->after->{$words_data[$x+1]}=1;
								
							}
							
						}
					
					}
					
					if(isset($words_data[$x-1])){
					
						if(trim($words_data[$x-1])!==""){
						
							if(isset($before_and_after->{$words_data[$x]}->after->{$words_data[$x-1]})){
						
								$before_and_after->{$words_data[$x]}->before->{$words_data[$x-1]}++;
								
							}else{
							
								$before_and_after->{$words_data[$x]}->before->{$words_data[$x-1]}=1;
								
							}
						
						}
					
					}
					
				}
			
			}
			
			$response = $file_process->create_file("data/before_and_after/files/" . str_replace(" ", "_", $post_data['filename']), serialize($before_and_after));
			
			return $response;
		
		}
		
		private function create(){
		
			if(count($_POST)!==0){
			
				$response = $this->create_before_and_after($_POST);
				
				return "<h2>" . $response[1] . "</h2><p><a href='?data=before_and_after'>" . $this->language->translate("data/before_and_after", "Return to before and after") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/plain_text/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("data/before_and_after", "Creating a before and after file") . "</h2>
							   <p>" . $this->language->translate("data/before_and_after", "Choose a plain text file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='plaintextfile'>
										<option>" . $this->language->translate("data/before_and_after", "Select a plain text file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<label>
										" . $this->language->translate("data/before_and_after", "Enter a file name") . "
									</label>
									<input type='text' name='filename' />
									<input type='submit' value='" . $this->language->translate("data/before_and_after", "Create") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("data/before_and_after", "No plain texts files exist, please add one before using this tool") . "</p>";
				
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
				
					$response = $file_process->upload_file("data/before_and_after/files/", $_POST['filename'], $_FILES);
				
				}
				
				return "<h2>" . $response[1] . "</h2><a href='?data=before_and_after'>" . $this->language->translate("data/before_and_after", "Return to before and after") . "</a>";
			
			}else{
		
				return "<h2>" . $this->language->translate("data/before_and_after", "Upload a before and after file") . "</h2>
							<form enctype='multipart/form-data' action='' method='POST'>
								<input type='file' name='uploadfile' />
								<label>
									" . $this->language->translate("data/before_and_after", "Name for new file") . "
								</label>
								<input type='text' value='" . $this->language->translate("data/before_and_after", "Enter the file name") . "' name='filename' /><br />
								<input type='submit' value='" . $this->language->translate("data/before_and_after", "Upload") . "' />
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
				
						$response[$key] = $file_process->delete_file("data/before_and_after/files/" . $key);
						
					}
					
				}
				
				$output = "";
				
				foreach($response as $file => $values){
				
					$output .= "<p>" . $file . " : " . $values[1] . "</p>";
				
				}
				
				$output .= "<p><a href='?data=before_and_after'>" . $this->language->translate("data/before_and_after", "Return to before and after") . "</a></p>";
				
				return $output;
			
			}else{
		
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();		
				
				$files = $file_process->read_folder("data/before_and_after/files/");
				
				if(count($files)!==0){
		
					$output = "<h2>" . $this->language->translate("data/before_and_after", "Manage before and after files") . "</h2>
								<form enctype='multipart/form-data' action='' method='POST'>";
					
					while($file = array_pop($files)){
						
						$output .= "<label>" . $this->language->translate("data/before_and_after", "File") . " : " . $file . "</label> - " . $this->language->translate("data/before_and_after", "tick to delete") . " <input name='" . $file . "' type='checkbox' /><br />"; 
					
					}
										
					$output .=	"<input type='submit' value='Delete' />
								</form>";
				}else{
				
					$output = "<p>" . $this->language->translate("data/before_and_after", "No files have been uploaded yet") . ". <a href='?data=before_and_after'>" . $this->language->translate("data/before_and_after", "Return to before and after") . "</a></p>";
				
				}
				
				return $output;
			
			}
		
		}
		
	}