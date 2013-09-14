<?PHP

	class tweet_matrix extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_matrix";
			$classification->name = "Tweet Matrix";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_matrix/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_matrix/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_matrix", "Tweet Matrix Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_matrix&action=instructions'>" . $this->language->translate("tools/tweet_matrix", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_matrix&action=alltweets'>" . $this->language->translate("tools/tweet_matrix", "Display Tweet Matrix") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_matrix", "help");
			
			return $output . "<p><a href='?tool=tweet_matrix'>" . $this->language->translate("tools/tweet_matrix", "Return to Tweet Matrix") . "</a></p>";
				
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
				
				$output = "<h2>" . $this->language->translate("tools/tweet_matrix", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$matrix = new StdClass();
				$twitter_users = array();
				$user_index = array();
				
				foreach($data as $tweet){
				
					if(!in_array($tweet->user->screen_name, $twitter_users)){
					
						array_push($twitter_users,$tweet->user->screen_name);
						$user_index[$tweet->user->screen_name] = count($twitter_users);
						
					}
					
					if(!isset($matrix->{$tweet->user->screen_name})){
					
						$matrix->{$tweet->user->screen_name} = new StdClass();
					
					}
					
					if(isset($tweet->entities->user_mentions)){
					
						foreach($tweet->entities->user_mentions as $user){
						
							if(!in_array($user->screen_name, $twitter_users)){
					
								array_push($twitter_users,$user->screen_name);
								$user_index[$user->screen_name] = count($twitter_users);
								
							}
						
							if(!isset($matrix->{$tweet->user->screen_name}->{$user->screen_name})){
						
								$matrix->{$tweet->user->screen_name}->{$user->screen_name} = 1;
						
							}else{
							
								$matrix->{$tweet->user->screen_name}->{$user->screen_name}++;
							
							}
						
						}
						
					}
					
				}
				
				$im = imagecreatetruecolor((count($twitter_users)*18)+150, (count($twitter_users)*18)+150);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				$x_pos = 120;
				$y_pos = 120;
				
				foreach($twitter_users as $user){
				
					imagettftext ( $im , 8.0 , 90, $x_pos , 90, $white , "core/misc/fonts/arial.ttf" , $user);
					$x_pos+=18;
					imagettftext ( $im , 8.0 , 0, 10 , $y_pos, $white , "core/misc/fonts/arial.ttf" , $user);	
					$y_pos+=18;	
				
				}
				
				$max = 0;
				$users;
				
				foreach($matrix as $user => $data){
				
					foreach($data as $user2 => $total){
					
						if($total > $max){
						
							$max = $total;
							
							$users = array($user, $user2, $total);
						
						}
							
						imagettftext ( $im , 8 , 0, (($user_index[$user])*18)+95 , ($user_index[$user2]*18)+103 , $white , "core/misc/fonts/arial.ttf" , $total);

					}
					
				}
				
				$file_process->file_image_create("data/twitter_harvest/files/matrix/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_matrix.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_matrix", "Tweet Matrix Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/matrix/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_matrix.jpg' />";
				
				$output .= "<h2>" . $this->language->translate("tools/tweet_matrix", "Most Connected") . "</h2>";
				
				$output .= "<p>" . $users[0] . " -> " . $users[2] . " -> " . $users[1] . "</p>";

				return $output . "<p><a href='?tool=tweet_matrix'>" . $this->language->translate("tools/tweet_matrix", "Return to Tweet Matrix Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_matrix", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_matrix", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_matrix", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									  <input type='submit' value='" . $this->language->translate("tools/tweet_matrix", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_matrix", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
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
				
				$output = "<h2>" . $this->language->translate("tools/tweet_matrix", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
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
				
				$output = "<h2>" . $this->language->translate("tools/tweet_matrix", "Tweet Matrix Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/fingerprint/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_fingerprint_limit.jpg' />";

				return $output . "<p><a href='?tool=tweet_matrix'>" . $this->language->translate("tools/tweet_matrix", "Return to Tweet Matrix Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_matrix", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_matrix", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_matrix", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<label>" . $this->language->translate("tools/tweet_matrix", "Number of tweets a user must have tweeted to appear on the fingerprint") . "</label>
									<input type='text' size=100 name='tweet_cutoff' value='" . $this->language->translate("tools/tweet_matrix", "Number of tweets a user must have tweeted to appear") . "' />
									<input type='submit' value='" . $this->language->translate("tools/tweet_matrix", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_matrix", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}