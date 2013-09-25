<?PHP

	class tweet_fingerprint extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_fingerprint";
			$classification->name = "Tweet Fingerprint";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_fingerprint/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_fingerprint/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_fingerprint", "Tweet Fingerprint Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_fingerprint&action=instructions'>" . $this->language->translate("tools/tweet_fingerprint", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_fingerprint&action=alltweets'>" . $this->language->translate("tools/tweet_fingerprint", "Display Tweet Fingerprint (all)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_fingerprint&action=tweet_contrib'>" . $this->language->translate("tools/tweet_fingerprint", "Display Tweet Fingerprint (users with over X tweets)") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_fingerprint", "help");
			
			return $output . "<p><a href='?tool=tweet_fingerprint'>" . $this->language->translate("tools/tweet_fingerprint", "Return to Tweet Fingerprint") . "</a></p>";
				
		}
		
		private function alltweets(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_fingerprint", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
				$twitter_users = array();
				
				foreach($data as $tweet){
				
					$tweets[] = $tweet->user->screen_name;
					
					if(!in_array($tweet->user->screen_name, array_keys($twitter_users))){
								
						$twitter_users[$tweet->user->screen_name] = count($twitter_users)+1; 
					
					}
					
				}
				
				$im = imagecreatetruecolor(count($tweets)+100, (count($twitter_users)*12) + 50);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				$x_pos = 100;
				$y_pos = 5;
				
				foreach($tweets as $tweet){
							
					imageline($im, $x_pos, $y_pos + (($twitter_users[$tweet]-1)*12)+2, $x_pos, $y_pos + ((($twitter_users[$tweet]-1)*12) + 12), imagecolorallocate($im, (integer)(255),0,0));
					imagettftext ( $im , 10.0 , 0, 5 , $y_pos + ($twitter_users[$tweet]*12) , $white , "core/misc/fonts/arial.ttf" , $tweet);
					
					$x_pos += 1;
				
				}	
				
				$file_process->file_image_create("data/twitter_harvest/files/fingerprint/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_all.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_fingerprint", "Tweet Fingerprint Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/fingerprint/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_all.jpg' />";

				return $output . "<p><a href='?tool=tweet_fingerprint'>" . $this->language->translate("tools/tweet_fingerprint", "Return to Tweet Fingerprint Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_fingerprint", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_fingerprint", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_fingerprint", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									  <input type='submit' value='" . $this->language->translate("tools/tweet_fingerprint", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_fingerprint", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		
		private function tweet_contrib(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_fingerprint", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
				
				$twitter_users = array();
				
				foreach($data as $tweet){
				
					$tweets[] = $tweet->user->screen_name;
					
					if(!in_array($tweet->user->screen_name, array_keys($twitter_users))){
								
						$twitter_users[$tweet->user->screen_name] = count($twitter_users)+1; 
					
					}
					
				}	
				
				$no_tweets = array_count_values($tweets);
				
				$twitter_accs = array();
				$other = 0;
				$counter = 1;
				
				foreach($no_tweets as $name => $count){
				
					if($count > $_POST['tweet_cutoff']){
					
						$other += $count;
						$twitter_accs[$name] = $counter++;
					
					}
				
				}
				
				$twitter_accs["Other"] = $counter;
				
				$names = array_keys($twitter_accs);
				
				$im = imagecreatetruecolor(count($tweets)+120, (count($twitter_accs)*12) + 50);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				$x_pos = 150;
				$y_pos = 5;
				
				foreach($tweets as $tweet){
				
					if(!in_array($tweet,$names)){
					
						$tweet = "Other";
					
					}
					
					imageline($im, $x_pos, $y_pos + (($twitter_accs[$tweet]-1)*12), $x_pos, $y_pos + ((($twitter_accs[$tweet]-1)*12) + 10), imagecolorallocate($im, (integer)(255),0,0));
					imagettftext ( $im , 10.0 , 0, 5 , $y_pos + ($twitter_accs[$tweet]*12) , $white , "core/misc/fonts/arial.ttf" , $tweet);
					
					$x_pos += 1;
				
				}	
				
				$file_process->file_image_create("data/twitter_harvest/files/fingerprint/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_fingerprint_limit.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_fingerprint", "Tweet Fingerprint Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/fingerprint/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_fingerprint_limit.jpg' />";

				return $output . "<p><a href='?tool=tweet_fingerprint'>" . $this->language->translate("tools/tweet_fingerprint", "Return to Tweet Fingerprint Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_fingerprint", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_fingerprint", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_fingerprint", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<label>" . $this->language->translate("tools/tweet_fingerprint", "Number of tweets a user must have tweeted to appear on the fingerprint") . "</label>
									<input type='text' size=100 name='tweet_cutoff' value='" . $this->language->translate("tools/tweet_fingerprint", "Number of tweets a user must have tweeted to appear") . "' />
									<input type='submit' value='" . $this->language->translate("tools/tweet_fingerprint", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_fingerprint", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}