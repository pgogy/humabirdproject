<?PHP

	class tweet_time_contributions extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_time_contributions";
			$classification->name = "Tweet Time Contributions";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_time_contributions/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_time_contributions/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_time_contributions", "Tweet Time Contributions") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_time_contributions&action=instructions'>" . $this->language->translate("tools/tweet_time_contributions", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_time_contributions&action=tweet_time_contributions'>" . $this->language->translate("tools/tweet_time_contributions", "Display Tweet Time Contributions") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_time_contributions", "help");
			
			return $output . "<p><a href='?tool=tweet_time_contributions'>" . $this->language->translate("tools/tweet_time_contributions", "Return to Tweet Time Map") . "</a></p>";
				
		}
		
		private function map_tweets_time($data){
		
			$tweets = array();
				
			$first = "";
			
			$times = array();
			
			$users = array();
			$screen_names = array();
	
			foreach($data as $tweet){
			
				if(!in_array($tweet->user->id_str, $users)){
				
					$users[$tweet->user->id_str] = $tweet->user->name;
					$screen_names[$tweet->user->id_str] = $tweet->user->screen_name;
				
				}
			
				$time = explode(" ", $tweet->created_at);
				
				$clock = explode(":", $time[3]);
				
				switch($time[1]){
				
					case "Jan" : $month = 1; break;
					case "Feb" : $month = 2; break;
					case "Mar" : $month = 3; break;
					case "Apr" : $month = 4; break;
					case "May" : $month = 5; break;
					case "Jun" : $month = 6; break;
					case "Jul" : $month = 7; break;
					case "Aug" : $month = 8; break;
					case "Sep" : $month = 9; break;
					case "Oct" : $month = 10; break;
					case "Nov" : $month = 11; break;
					case "Dec" : $month = 12; break;
				
				}
				
				$seconds = mktime($clock[0],$clock[1],$clock[2],$month,$time[2],array_pop($time));
				
				if(!isset($tweets[$seconds])){
				
					$tweets[$seconds] = array();
				
				}
				
				array_push($tweets[$seconds],$tweet);					
				
			}
			
			$index = array_keys($tweets);
			
			arsort($index);
			
			$last = array_shift($index);
			$first = array_pop($index);
			
			arsort($tweets);
			
			$width = ($last - $first);
			
			$max = 0;
			
			$windows = array();
			
			foreach($tweets as $time => $data){
		
				$window = ($time-$first)%$_POST['time_period'];
				$window = ($time-$first) - $window;
				
				if(!isset($windows[$window])){
				
					$windows[$window] = array();
				
				}
				
				array_push($windows[$window], $data);
			
			}
			
			return array($windows,array($users, $screen_names),$width,$first,$last);
		
		}
	
		private function tweet_time_contributions(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$response = $this->map_tweets_time($data);
				
				$windows = $response[0];
				$users = $response[1][0];
				$screen_name = $response[1][1];
				$width = $response[2];
				$first = $response[3];
				$last = $response[4];
				
				$width_val = round($width/$_POST['time_period']);
				
				$im = imagecreatetruecolor(($width_val*30) + 300, (count($users)*25)+200);
				$white = imagecolorallocate($im, 255,255,255);
				$black = imagecolorallocate($im, 0,0,0);
				
				imageline($im, 200, (count($users)*25), 200, 25, $white);
				imageline($im, 200, (count($users)*25), ($width_val*30) + 200, (count($users)*25), $white);
				
				$names = array_flip(array_keys($users));
				
				$y_pos = 0;
				$orig_y_pos = (count($users)*25)-5;
				
				$style = array($white, $black, $white, $black);
				imagesetstyle($im, $style);
				
				array_unshift($screen_name, $this->language->translate("tools/tweet_time_contributions", "Total Tweets"));
				array_unshift($users, " ");
								
				foreach($screen_name as $name){
				
					imagettftext( $im , 8.0 , 0, 5, $orig_y_pos - $y_pos, $white , "core/misc/fonts/arial.ttf" , $name . " / " . array_shift($users));
					imageline($im, 200, ($orig_y_pos - $y_pos)+2, ($width_val*30) + 200, ($orig_y_pos - $y_pos)+2, IMG_COLOR_STYLED);
					$y_pos+=24;
				
				}
				
				$x_pos = 210;
				$y_pos = 620;
				
				for($x=0; $x<=($last-$first); $x+=$_POST['time_period']){
				
					$user_list = array();
				
					if(isset($windows[$x])){
				
						foreach($windows[$x] as $tweets){
						
							foreach($tweets as $tweet){
								
								if(!isset($user_list[$tweet->user->id_str])){
								
									$user_list[$tweet->user->id_str]=1;
								
								}else{
								
									$user_list[$tweet->user->id_str]++;
									
								}
								
							}
							
						}
						
					}	
					
					foreach($user_list as $user => $total){
					
						imagettftext( $im , 8.0 , 0, $x_pos, ($orig_y_pos - (($names[$user]+1)*24))-5, $white , "core/misc/fonts/arial.ttf" , $total);
					
					}
					
					imagettftext( $im , 8.0 , 0, $x_pos, ($orig_y_pos -5), $white , "core/misc/fonts/arial.ttf" , array_sum($user_list));
					
					imagettftext( $im , 8.0 , 270, $x_pos, (count($names)*25)+10, $white , "core/misc/fonts/arial.ttf" , date("H:i:s jS M Y", $first-($x)));
					
					$x_pos+=30;
					
				}
				
				imagettftext( $im , 8.0 , 0, 5, (count($names)*25)+180, $white , "core/misc/fonts/arial.ttf" , date("H:i:s jS M Y", $first));
				
				imagettftext( $im , 8.0 , 0, 5, (count($names)*25)+190, $white , "core/misc/fonts/arial.ttf" , date("H:i:s jS M Y", $last));
				
				$file_process->file_image_create("data/twitter_harvest/files/time_plot/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_time_plot.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_time_contributions", "Tweet Time Contributions") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/time_plot/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_time_plot.jpg' />";

				return $output . "<p><a href='?tool=tweet_time_contributions'>" . $this->language->translate("tools/tweet_time_contributions", "Return to Tweet Time Map") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_time_contributions", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_time_contributions", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_time_contributions", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
					
					$output .= "<label>" . $this->language->translate("data/twitter_time_plot", "Time period") . "</label>";
	
					$output .= "<input type='text' name='time_period' />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_time_contributions", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_time_contributions", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}		
		
	}