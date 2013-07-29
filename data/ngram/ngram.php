<?PHP

	class ngram extends data{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Intertextual";
			$classification->column = "Formatted text";
			$classification->link = "?data=ngram";
			$classification->name = "Ngram";
			
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

				$output = "<h2>" . $this->language->translate("data/ngram", "Ngram file management") . "</h2>
						   <ul>
								<li>
									<a href='?data=ngram&action=instructions'>" . $this->language->translate("data/ngram", "Instructions") . "</a>
								</li>
								<li>
									<a href='?data=ngram&action=create'>" . $this->language->translate("data/ngram", "Create new file") . "</a>
								</li>
								<li>
									<a href='?data=ngram&action=upload'>" . $this->language->translate("data/ngram", "Upload file") . "</a>
								</li>
								<li>
									<a href='?data=ngram&action=manage'>" . $this->language->translate("data/ngram", "Manage files") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("data/ngram", "help");
			
			return $output . "<p><a href='?data=ngram'>" . $this->language->translate("data/ngram", "Return to Ngram") . "</a></p>";
				
		}
		
		private function create_ngram($post_data){
			
			if(trim($post_data['filename'])===""){
			
				return array(false, $this->language->translate("data/ngram", "Please choose a filename"));
			
			}
			
			if(trim($post_data['ngramlength'])===""){
			
				return array(false, $this->language->translate("data/ngram", "Please choose an Ngram length"));
			
			}
			
			require_once("core/file_handling/file_handling.php");
			$file_process = new file_handling();
			$data = $file_process->file_get_all("data/plain_text/files/" . $post_data['plaintextfile']);
			$words = explode(" ", str_replace("\n"," ",$data));
			foreach($words as $key => $value){
				$words[$key] = trim($value);
			}			
			$words = array_filter($words);
			
			$last_word = array();
			$words_list = array();
			
			foreach($words as $word){
				
				$word = str_replace("&mdash;","",$word);
			
				if(trim($word)!=""){
				
					if(strlen($word)!=1){
					
						$last = substr($word,strlen($word)-1,1);
					
						while($last=="!"||$last=="."||$last==","||$last=="?"||$last==":"||$last==";"||$last==")"){
						
							$word = substr($word,0,strlen($word)-1);
							$last = substr($word,strlen($word)-1,1);
							
						}
						
						$first = substr($word,0,1);
					
						while($first=="!"||$first=="."||$first==","||$first=="?"||$first==":"||$first==";"||$first==")"||$first=="("){
						
							$word = substr($word,1,strlen($word)-1);
							$first = substr($word,strlen($word)-1,1);
							
						}
						
						if(count($last_word)==$post_data['ngramlength']){
						
							if(!isset($words_list[implode("-",$last_word)])){
									
								$words_list[implode("-",$last_word)] = 1;
									
							}else{
								
								$words_list[implode("-",$last_word)]++;
								
							}
							
							array_shift($last_word);
							
						}
						
						array_push($last_word,$word);
							
					}
					
				}
					
			}
			
			$response = $file_process->create_file("data/ngram/files/" . str_replace(" ", "_", $post_data['filename']) . "_ngram_" . $post_data['ngramlength'], serialize($words_list));
			
			return $response;
		
		}
		
		private function create(){
		
			if(count($_POST)!==0){
			
				$response = $this->create_ngram($_POST);
				
				return "<h2>" . $response[1] . "</h2><p><a href='?data=ngram'>" . $this->language->translate("data/ngram", "Return to Ngram") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/plain_text/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("data/ngram", "Creating an Ngram file") . "</h2>
							   <p>" . $this->language->translate("data/ngram", "Choose a plain text file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='plaintextfile'>
										<option>" . $this->language->translate("data/ngram", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<label>
										" . $this->language->translate("data/ngram", "Enter a file name") . "
									</label>									
									<input type='text' name='filename' /><br />
									<label>
										" . $this->language->translate("data/ngram", "Enter an Ngram Length") . "
									</label>									
									<input type='text' name='ngramlength' />
									<input type='submit' value='" . $this->language->translate("data/ngram", "Create") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("data/ngram", "No plain texts files exist, please add one before using this tool") . "</p>";				
				
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
				
					$response = $file_process->upload_file("data/ngram/files/", $_POST['filename'], $_FILES);
				
				}
				
				return "<h2>" . $response[1] . "</h2><a href='?data=ngram'>" . $this->language->translate("data/ngram", "Return to Ngram") . "</a>";
			
			}else{
		
				return "<h2>" . $this->language->translate("data/ngram", "Upload an Ngram File") . "</h2>
							<form enctype='multipart/form-data' action='' method='POST'>
								<input type='file' name='uploadfile' />
								<label>
									" . $this->language->translate("data/ngram", "Name for new file") . "
								</label>
								<input type='text' value='" . $this->language->translate("data/ngram", "Enter a file name here") . "' name='filename' /><br />
								<input type='submit' value='" . $this->language->translate("data/ngram", "Upload") . "' />
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
				
						$response[$key] = $file_process->delete_file("data/ngram/files/" . $key);
						
					}
					
				}
				
				$output = "";
				
				foreach($response as $file => $values){
				
					$output .= "<p>" . $file . " : " . $values[1] . "</p>";
				
				}
				
				$output .= "<p><a href='?data=ngram'>" . $this->language->translate("data/ngram", "Return to Ngram") . "</a></p>";
				
				return $output;
			
			}else{
		
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();		
				
				$files = $file_process->read_folder("data/ngram/files/");
				
				if(count($files)!==0){
		
					$output = "<h2>" . $this->language->translate("data/ngram", "Manage Ngram Files") . "</h2>
								<form enctype='multipart/form-data' action='' method='POST'>";
					
					while($file = array_pop($files)){
						
						$output .= "<label>" . $this->language->translate("data/ngram", "File") . " : " . $file . "</label> - " . $this->language->translate("data/ngram", "Tick box to delete") . " <input name='" . $file . "' type='checkbox' /><br />"; 
					
					}
										
					$output .=	"<input type='submit' value='Delete' />
								</form>";
				}else{
				
					$output = "<p>" . $this->language->translate("data/ngram", "No files have been uploaded yet") . ". <a href='?data=ngram'>" . $this->language->translate("data/ngram", "Return to Ngram") . "</a></p>";
				
				}
				
				return $output;
			
			}
		
		}
		
	}