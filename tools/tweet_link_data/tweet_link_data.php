<?PHP

	class tweet_link_data extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_link_data";
			$classification->name = "Tweet Link Data";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_link_data/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_link_data/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_link_data", "Tweet Network Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_link_data&action=instructions'>" . $this->language->translate("tools/tweet_link_data", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_link_data&action=links_tweeted'>" . $this->language->translate("tools/tweet_link_data", "Display screen names and links shared") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_link_data&action=links_shared'>" . $this->language->translate("tools/tweet_link_data", "Display how often links are shared how many times") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_link_data", "help");
			
			return $output . "<p><a href='?tool=tweet_link_data'>" . $this->language->translate("tools/tweet_link_data", "Return to Tweet Network display") . "</a></p>";
				
		}
		
		private function links_tweeted(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_link_data", "Tweet Link Data for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
				$urls = array();
		
				foreach($data as $tweet){
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->urls)!=0){
						
							if(isset($tweet->retweeted_status)){
					
								$extra = " / " . $tweet->retweeted_status->user->screen_name;
					
							}else{
							
								$extra = "";	
						
							}
						
							$tweets[] = 1;
							
							foreach($tweet->entities->urls as $url){
							
								if(isset($urls[$url->expanded_url])){
								
									array_push($urls[$url->expanded_url], $tweet->user->screen_name . $extra);
								
								}else{
								
									$urls[$url->expanded_url]= array($tweet->user->screen_name . $extra);
									
								}
								
							}
						
						}
				
					}
					
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_link_data", "Tweet Link Data Display") . "</h2>";
				
				foreach($urls as $link => $data){
				
					$output .= "<p>" . $link . " : " . implode(",",$data) . " " . $this->language->translate("tools/tweet_link_data", " : Times Tweeted") . " " . count($data) . "</p>";
				
				}
				
				return $output . "<p><a href='?tool=tweet_link_data'>" . $this->language->translate("tools/tweet_link_data", "Return to Tweet Link Data Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_link_data", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_link_data", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_link_data", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_link_data", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_link_data", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function links_shared(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_link_data", "Tweet Link Data for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
				$urls = array();
		
				foreach($data as $tweet){
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->urls)!=0){
						
							if(isset($tweet->retweeted_status)){
					
								$extra = " / " . $tweet->retweeted_status->user->screen_name;
					
							}else{
							
								$extra = "";	
						
							}
						
							$tweets[] = 1;
							
							foreach($tweet->entities->urls as $url){
							
								if(isset($urls[$url->expanded_url])){
								
									array_push($urls[$url->expanded_url], $tweet->user->screen_name . $extra);
								
								}else{
								
									$urls[$url->expanded_url]= array($tweet->user->screen_name . $extra);
									
								}
								
							}
						
						}
				
					}
					
				}
				
				$links_shared = array();
				
				foreach($urls as $link => $data){
				
					if(isset($links_shared[count($data)])){
					
						$links_shared[count($data)]++;
					
					}else{
						
						$links_shared[count($data)]=1;
					
					}
					
				}
				
				ksort($links_shared);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_link_data", "Tweet Link Data Display") . "</h2>";
				
				foreach($links_shared as $link => $data){
				
					$output .= "<p>" . $this->language->translate("tools/tweet_link_data", " Link shared ") . $link . " : " . $data . " " . $this->language->translate("tools/tweet_link_data", " times") . "</p>";
				
				}
				
				return $output . "<p><a href='?tool=tweet_link_data'>" . $this->language->translate("tools/tweet_link_data", "Return to Tweet Link Data Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_link_data", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_link_data", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_link_data", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_link_data", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_link_data", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		
	}