<?PHP

	class tweet_retweet extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_retweet";
			$classification->name = "Tweet Retweet";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_retweet/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_retweet/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_retweet", "Tweet Retweet Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_retweet&action=instructions'>" . $this->language->translate("tools/tweet_retweet", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_retweet&action=tweet_depth'>" . $this->language->translate("tools/tweet_retweet", "Display Retweet contributions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_retweet&action=tweet_contrib'>" . $this->language->translate("tools/tweet_retweet", "User contributions Vs Retweets") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_retweet", "help");
			
			return $output . "<p><a href='?tool=tweet_retweet'>" . $this->language->translate("tools/tweet_retweet", "Return to Tweet Retweet") . "</a></p>";
				
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
				
				$output = "<h2>" . $this->language->translate("tools/tweet_retweet", "Tweet Reply Pie Chart for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$retweets = array();
				$normals = array();
				$both = array();
				
				$count = 0;	
			
				foreach($data as $tweet){
				
					if(isset($tweet->retweeted_status)){
		
						if(in_array($tweet->user->name,$normals)){
						
							$normals[$tweet->user->name] = "";
							unset($normals[$tweet->user->name]);
							$both[$tweet->user->name] = $tweet->user->name;
						
						}else if(!in_array($tweet->user->name,$both)){

							$retweets[$tweet->user->name] = $tweet->user->name;

						}
					
					}else{
					
						$normals[$tweet->user->name] = $tweet->user->name;
					
					}
					
				}
				
				$degree_ratio = 360 / (count($normals) + count($both) + count($retweets));
				
				$im = imagecreatetruecolor(800,700);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 0,0,255);
				$blue = imagecolorallocate($im, 255,0,0);
				$last_angle = 0;
				$speakers_y = 650;
			
				imagefilledarc ($im , 400 , 325 , 600 , 600 , $last_angle , $last_angle+($degree_ratio*count($normals)) , $white, IMG_ARC_PIE );
				imagefilledrectangle($im, 10, $speakers_y-15 , 30, $speakers_y , $white );
				imagettftext ( $im , 12.0 , 0 , 35 , $speakers_y , $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_retweet", "Just tweets ") . count($normals));
				$speakers_y += 18;
				$last_angle += ($degree_ratio*count($normals));			
						
				imagefilledarc ($im , 400, 325 , 600 , 600 , $last_angle , $last_angle+($degree_ratio*count($both)) , $red, IMG_ARC_PIE );
				imagefilledrectangle($im, 10, $speakers_y-15 , 30, $speakers_y , $red );
				imagettftext ( $im , 12.0 , 0 , 35 , $speakers_y , $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_retweet", "Tweets and Retweets ") . count($both));
				$speakers_y += 18;		
				$last_angle += ($degree_ratio*count($both));

				imagefilledarc ($im , 400 , 325 , 600 , 600 , $last_angle , $last_angle+($degree_ratio*count($retweets)) , $blue, IMG_ARC_PIE );
				imagefilledrectangle($im, 10, $speakers_y-15 , 30, $speakers_y , $blue );
				imagettftext ( $im , 12.0 , 0 , 35 , $speakers_y , $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_retweet", "Retweets only ") . count($retweets));
				$speakers_y += 18;	
				
				$file_process->file_image_create("data/twitter_harvest/files/piechart/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_rt_contrib.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_retweet", "Tweet Retweet Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/piechart/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_rt_contrib.jpg' />";

				return $output . "<p><a href='?tool=tweet_retweet'>" . $this->language->translate("tools/tweet_retweet", "Return to Tweet Retweet") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_retweet", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_retweet", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_retweet", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_retweet", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_retweet", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}