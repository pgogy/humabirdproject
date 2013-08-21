<?PHP

	class tweet_watch_people extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_watch_people";
			$classification->name = "Tweet Watch for People mentioned";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_watch_people/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_watch_people/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_watch_people", "Tweet Display for People mentioned in tweets") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_watch_people&action=instructions'>" . $this->language->translate("tools/tweet_watch_people", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_watch_people&action=alltweets'>" . $this->language->translate("tools/tweet_watch_people", "Display Tweet Watch for people") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_watch_people", "help");
			
			return $output . "<p><a href='?tool=tweet_watch_people'>" . $this->language->translate("tools/tweet_watch_people", "Return to Tweet watch display") . "</a></p>";
				
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
				
				$output = "<h2>" . $this->language->translate("tools/tweet_watch_people", "Tweet Watch Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
				$user_mentions = array();
		
				foreach($data as $tweet){
				
					if(isset($tweet->entities)){
				
						if(isset($tweet->entities->user_mentions)){
						
							$tweets[] = count($tweet->entities->user_mentions);
						
						}else{
						
							$tweets[] = 0;
						
						}
						
						if(isset($user_mentions[count($tweet->entities->user_mentions)])){
						
							$user_mentions[count($tweet->entities->user_mentions)]++;
						
						}else{
						
							$user_mentions[count($tweet->entities->user_mentions)]=1;
							
						}
				
					}else{
					
						$tweets[] = 0;
						
						if(isset($user_mentions[0])){
						
							$user_mentions[0]++;
						
						}else{
						
							$user_mentions[0]=1;
							
						}
						
					}
					
				}
				
				arsort($user_mentions);
				
				$ratio = 360 / (count($tweets));				
				
				$size = max(count($tweets), 1300);
				$width = max(count($tweets)*1.1, 1400);
				
				$width = min(11000, $width);
				$size = min(10000, $size);
				
				$im = imagecreatetruecolor($width, $width + 50 + ((count($user_mentions) * 20)));
				$white = imagecolorallocate($im, 255,255,255);
				$grey = imagecolorallocate($im, 200,200,200);
				$red = imagecolorallocate($im, 255,0,0);
				
				$clock_point = 270;	
				
				$colours = array();
				
				foreach($tweets as $key => $value){
				
					if($value!=0){
				
						$y_pos = ($width / 2) + (integer)round( (($size-($value*100)) / 2) * sin( deg2rad($clock_point) ) );
						$x_pos = ($width / 2) + (integer)round( (($size-($value*100)) / 2) * cos( deg2rad($clock_point) ) );
						
						if(!isset($colours[$value])){
						
							$colours[$value] = imagecolorallocate($im, rand(0,255), rand(0,255), rand(0,255)); 
						
						}
						
						imageline($im, $width/2, $width/2, $x_pos, $y_pos, $colours[$value]);
						
					}else{
					
						$y_pos = ($width / 2) + (integer)round( (($size-($value*100)) / 2) * sin( deg2rad($clock_point) ) );
						$x_pos = ($width / 2) + (integer)round( (($size-($value*100)) / 2) * cos( deg2rad($clock_point) ) );
						
						imageline($im, $width/2, $width/2, $x_pos, $y_pos, $red);						
					
					}
				
					$clock_point+=$ratio;
				
				}	
				
				$y_pos = $size+50;
				
				$colours[0] = $red;
				
				foreach($user_mentions as $key => $value){
				
					imagefilledrectangle($im, 20, $y_pos, 40, $y_pos+20, $colours[$key]);
					imagettftext ( $im , 15.0 , 0, 50 , $y_pos+18 , $white , "core/misc/fonts/arial.ttf" , $key . " : " . $this->language->translate("tools/tweet_watch_mentions", " people mentioned in tweet") . " " . $value . $this->language->translate("tools/tweet_watch_mentions", " times") );
					
					$y_pos +=25;
				
				}
				
				imagettftext ( $im , 15.0 , 0, $width/2 , 30 , $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_watch_links", "Archive start"));
				
				$file_process->file_image_create("data/twitter_harvest/files/mentions_watch/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_mentionswatch.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_watch_people", "Tweet Watch for Links Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/mentions_watch/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_mentionswatch.jpg' />";

				return $output . "<p><a href='?tool=tweet_watch_people'>" . $this->language->translate("tools/tweet_watch_people", "Return to Tweet Watch for Mentions Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_watch_people", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_watch_people", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_watch_people", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_watch_people", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_watch_people", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}