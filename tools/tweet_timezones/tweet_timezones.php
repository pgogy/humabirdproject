<?PHP

	class tweet_timezones extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_timezones";
			$classification->name = "Tweet Timezones";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_timezones/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_timezones/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_timezones", "Tweet Timezones") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_timezones&action=instructions'>" . $this->language->translate("tools/tweet_timezones", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_timezones&action=hashtags'>" . $this->language->translate("tools/tweet_timezones", "Display time zones") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_timezones", "help");
			
			return $output . "<p><a href='?tool=tweet_timezones'>" . $this->language->translate("tools/tweet_timezones", "Return to Tweet Hashtags display") . "</a></p>";
				
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
				
				$output = "<h2>" . $this->language->translate("tools/tweet_timezones", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$timezones = array();
				
				foreach($data as $tweet){
				
					if(isset($tweet->user->time_zone)){
				
						$timezone = $tweet->user->time_zone; 
						
						if(isset($timezones[$timezone])){
						
							$timezones[$timezone]++;
							
						}else{
						
							$timezones[$timezone]=1;
							
						}
						
					}
					
				}
				
				arsort($timezones);
				
				$output = "<h3>" . $this->language->translate("tools/tweet_timezones", "Time zones") . "</h3>";
				
				foreach($timezones as $zone => $count){
				
					$output .= "<p>" . $zone . " : " . $count . "</p>";
				
				}
				
				return $output . "<p><a href='?tool=tweet_timezones'>" . $this->language->translate("tools/tweet_timezones", "Return to Tweet Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_timezones", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_timezones", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_timezones", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_timezones", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_timezones", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}