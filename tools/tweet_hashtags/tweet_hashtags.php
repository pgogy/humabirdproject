<?PHP

	class tweet_hashtags extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_hashtags";
			$classification->name = "Tweet Hashtag Display";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_hashtags/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_hashtags/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_hashtags", "Tweet hashtag display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_hashtags&action=instructions'>" . $this->language->translate("tools/tweet_hashtags", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_hashtags&action=hashtags'>" . $this->language->translate("tools/tweet_hashtags", "Display hashtags") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_hashtags", "help");
			
			return $output . "<p><a href='?tool=tweet_hashtags'>" . $this->language->translate("tools/tweet_hashtags", "Return to Tweet Hashtags display") . "</a></p>";
				
		}
		

		private function hashtags(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_hashtags", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$hashtags = array();
				
				foreach($data as $tweet){
				
					if(isset($tweet->entities->hashtags)){
				
						foreach($tweet->entities->hashtags as $key => $data){
						
							if(isset($hashtag[$data->text])){
							
								$hashtag[$data->text]++;
								
							}else{
							
								$hashtag[$data->text]=1;
								
							}
						
						}
						
					}
					
				}
				
				arsort($hashtag);
				
				
				$output = "<h3>" . $this->language->translate("tools/tweet_hashtags", "Hashtags") . "</h3>";
				
				foreach($hashtag as $hash => $count){
				
					$output .= "<p>" . $hash . " : " . $count . "</p>";
				
				}
				
				return $output . "<p><a href='?tool=tweet_hashtags'>" . $this->language->translate("tools/tweet_hashtags", "Return to Tweet Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_hashtags", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_hashtags", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_hashtags", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_hashtags", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_hashtags", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}