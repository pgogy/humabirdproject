<?PHP

	class twitter_retweet_remove extends data{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
			
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Twitter setup and harvest";
			$classification->link = "?data=twitter_retweet_remove";
			$classification->name = "Twitter Retweet Remove";
			
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

				$output = "<h2>" . $this->language->translate("data/twitter_setup", "Twitter Retweet Remove") . "</h2>
						   <ul>
								<li>
									<a href='?data=twitter_retweet_remove&action=instructions'>" . $this->language->translate("data/twitter_retweet_remove", "Instructions") . "</a>
								</li>
								<li>
									<a href='?data=twitter_retweet_remove&action=retweet_remove'>" . $this->language->translate("data/twitter_retweet_remove", "Remove Retweets") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("data/twitter_retweet_remove", "help");
			
			return $output . "<p><a href='?data=twitter_retweet_remove'>" . $this->language->translate("data/twitter_retweet_remove", "Return to Twitter Retweet Remove") . "</a></p>";
				
		}
		
		private function retweet_remove(){
				
			if(count($_POST)!==0){
				
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['plaintextfile']));
				
				$kept_tweets = array();
				
				foreach($data as $tweet){
					
					if(!isset($tweet->retweeted_status)){
					
						array_push($kept_tweets, $tweet);
					
					}					
					
				}
				
				$response = $file_process->update_file("data/twitter_harvest/files/aggregate/" . $_POST['new_file_name'] . ".json", serialize($kept_tweets));
					
				$output = $this->language->translate("data/twitter_retweet_remove", "File Created");
					
				$output .= "<p><a href='?data=twitter_retweet_remove'>" . $this->language->translate("data/twitter_retweet_remove", "Return to Twitter Retweet Remove") . "</a></p>";
					
				return $output;
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();		
					
				$files = $file_process->read_folder_files_only("data/twitter_harvest/files/aggregate/");
					
				if(count($files)!==0){
			
					$output = "<h2>" . $this->language->translate("data/twitter_retweet_remove", "Choose a twitter file") . "</h2>
									<form enctype='multipart/form-data' action='' method='POST'>";
						
					$output .= "<select name='plaintextfile'>
										<option>" . $this->language->translate("data/twitter_retweet_remove", "Select a file") . "</output>";
								
					while($plain = array_pop($files)){
						
							$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
						
					}
		
					$output .=	"</select><br />";
					
					$output .=	"<label>";
					
					$output .= $this->language->translate("data/twitter_retweet_remove", "New Filename");
					
					$output .=	"</label>";
					
					$output .=	"<input type='text' name='new_file_name' />";
											
					$output .=	"<input type='submit' value='" . $this->language->translate("data/twitter_retweet_remove", "Remove") . "' />
									</form>";
				}else{
					
					$output = "<p>" . $this->language->translate("data/twitter_retweet_remove", "No files have been uploaded yet") . " - <a href='?data=twitter_retweet_remove'>" . $this->language->translate("data/twitter_retweet_remove", "Return to plain text") . "</a></p>";
					
				}
					
				return $output;
				
			}
			
		}		
				
	}