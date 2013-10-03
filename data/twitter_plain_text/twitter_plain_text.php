<?PHP

	class twitter_plain_text extends data{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
			
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Twitter setup and harvest";
			$classification->link = "?data=twitter_plain_text";
			$classification->name = "Twitter archive to plain text";
			
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

				$output = "<h2>" . $this->language->translate("data/twitter_setup", "Twitter archive to plain text") . "</h2>
						   <ul>
								<li>
									<a href='?data=twitter_plain_text&action=instructions'>" . $this->language->translate("data/twitter_plain_text", "Instructions") . "</a>
								</li>
								<li>
									<a href='?data=twitter_plain_text&action=tweet_extract'>" . $this->language->translate("data/twitter_plain_text", "Extract tweets") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("data/twitter_plain_text", "help");
			
			return $output . "<p><a href='?data=twitter_plain_text'>" . $this->language->translate("data/twitter_plain_text", "Return to Twitter Harvest") . "</a></p>";
				
		}
		
		private function tweet_extract(){
				
			if(count($_POST)!==0){
				
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				$data_store = array();
				$tweets = array();
				
				$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['plaintextfile']));
					
				$output = "";	
					
				foreach($data as $tweet){
					
					$output .= $tweet->text . "\n";
					
				}
				
				$response = $file_process->update_file("data/plain_text/files/" . $_POST['new_file_name'] . ".txt", $output);
					
				$output = "<h2>" . $this->language->translate("data/twitter_plain_text", "Twitter archive to plain text") . "</h2>";
				
				$output .= "<p>" . $this->language->translate("data/twitter_plain_text", "File Created") . "</p>";
					
				$output .= "<p><a href='?data=twitter_plain_text'>" . $this->language->translate("data/twitter_plain_text", "Return to Twitter archive to plain text") . "</a></p>";
					
				return $output;
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();		
					
				$files = $file_process->read_folder_files_only("data/twitter_harvest/files/aggregate/");
					
				if(count($files)!==0){
			
					$output = "<h2>" . $this->language->translate("data/twitter_plain_text", "Choose a twitter file") . "</h2>
									<p>" . $this->language->translate("data/twitter_plain_text", "Choose files to merge") . "</p>
									<form enctype='multipart/form-data' action='' method='POST'>";
						
					$output .= "<select name='plaintextfile'>
										<option>" . $this->language->translate("data/twitter_plain_text", "Select a file") . "</output>";
								
					while($plain = array_pop($files)){
						
							$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
						
					}
		
					$output .=	"</select><br />";
											
					$output .= "<label>" . $this->language->translate("data/twitter_plain_text", "Enter a new file name") . "</label>";						
											
					$output .=	"<input type='text' name='new_file_name' /></br>";
					
					$output .=	"<input type='submit' value='" . $this->language->translate("data/twitter_plain_text", "Extract") . "' />
									</form>";
				}else{
					
					$output = "<p>" . $this->language->translate("data/twitter_plain_text", "No files have been uploaded yet") . " - <a href='?data=twitter_plain_text'>" . $this->language->translate("data/twitter_plain_text", "Return to plain text") . "</a></p>";
					
				}
					
				return $output;
				
			}
			
		}		
				
	}