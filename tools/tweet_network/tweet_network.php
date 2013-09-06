<?PHP

	class tweet_network extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_network";
			$classification->name = "Tweet Network";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_network/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_network/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_network", "Tweet Network Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_network&action=instructions'>" . $this->language->translate("tools/tweet_network", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_network&action=network'>" . $this->language->translate("tools/tweet_network", "Display Tweet Network (all)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_network&action=binetwork'>" . $this->language->translate("tools/tweet_network", "Display Tweet Network (bidirectional)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_network&action=onlybinetwork'>" . $this->language->translate("tools/tweet_network", "Display Tweet Network (only bidirectional)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_network&action=tweet_contrib'>" . $this->language->translate("tools/tweet_network", "Display Tweet Network (users with over X tweets)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_network&action=tweet_social'>" . $this->language->translate("tools/tweet_network", "Display Tweet Network (users tweeting over X people)") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_network", "help");
			
			return $output . "<p><a href='?tool=tweet_network'>" . $this->language->translate("tools/tweet_network", "Return to Tweet Network display") . "</a></p>";
				
		}
		
		private function tweet_social(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$twitter_users = array();
				
				foreach($data as $tweet){
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->user_mentions)!=1){
					
							foreach($tweet->entities->user_mentions as $key => $inner_data){
							
								if(!isset($twitter_users[$tweet->user->screen_name])){
								
									$twitter_users[$tweet->user->screen_name] = array();
								
								}
								
								if(!isset($twitter_users[$inner_data->screen_name])){
								
									$twitter_users[$inner_data->screen_name] = array();
								
								}
								
								if(isset($twitter_users[$tweet->user->screen_name][$inner_data->screen_name])){
								
									$twitter_users[$tweet->user->screen_name][$inner_data->screen_name]++;
									
								}else{
								
									$twitter_users[$tweet->user->screen_name][$inner_data->screen_name]=1;
									
								}
							
							}
							
						}
					
					}
					
				}
				
				foreach($twitter_users as $key => $data){
				
					if(count($data) < $_POST['tweet_cutoff']){
					
						unset($twitter_users[$key]);
					
					}
					
				}
				
				$ratio = 360 / (count($twitter_users));
				
				$users = max(count($twitter_users), 200);
				
				$width = max(count($twitter_users) * 8, 1200);
				
				$im = imagecreatetruecolor($width,$width);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$clock_point = 0;	
				
				$interactions = array_keys($twitter_users);
				
				foreach($twitter_users as $key => $value){
				
					$y_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * sin( deg2rad($clock_point) ) );
					$x_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * cos( deg2rad($clock_point) ) );
					
					for($x=0;$x<count($interactions);$x++){
					
						if(isset($value[$interactions[$x]])){
						
							$line_y_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * sin( deg2rad($ratio * $x) ) );
							$line_x_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * cos( deg2rad($ratio * $x) ) );
							
							if(!isset($twitter_users[$key][$interactions[$x]])){
							
								imageline($im, $x_pos, $y_pos, $line_x_pos, $line_y_pos, imagecolorallocate($im, (integer)(255),0,0));
								
							}else{
							
								if(isset($twitter_users[$interactions[$x]][$key])){
								
									unset($twitter_users[$interactions[$x]][$key]);
								
								}
							
								imageline($im, $x_pos, $y_pos, $line_x_pos, $line_y_pos, $white);
							
							}
							
						}
						
					}
				
					imagettftext ( $im , 10.0 , 0-$clock_point, $x_pos , $y_pos , $white , "core/misc/fonts/arial.ttf" , $key);
					$clock_point+=$ratio;
				
				}	
				
				$file_process->file_image_create("data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_bi.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network", "Tweet Network Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_bi.jpg' />";

				return $output . "<p><a href='?tool=tweet_network'>" . $this->language->translate("tools/tweet_network", "Return to Tweet Network Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_network", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_network", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_network", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<label>" . $this->language->translate("tools/tweet_network", "Number of other users a user must tweet to appear on the network") . "</label>
									<input type='text' size=100 name='tweet_cutoff' value='" . $this->language->translate("tools/tweet_network", "Number of users a user must have tweeted to appear") . "' />
									<input type='submit' value='" . $this->language->translate("tools/tweet_network", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_network", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		
		private function network(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$twitter_users = array();
				
				foreach($data as $tweet){
					
					$name = strtolower($tweet->user->screen_name);
				
					if(!isset($twitter_users[$name])){
								
						$twitter_users[$name] = array();
					
					}
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->user_mentions)!=0){
					
							foreach($tweet->entities->user_mentions as $key => $inner_data){
							
								$inner_name = strtolower($inner_data->screen_name);
								
								if(!isset($twitter_users[$inner_name])){
								
									$twitter_users[$inner_name] = array();
								
								}
								
								if(isset($twitter_users[$name][$inner_name])){
								
									$twitter_users[$name][$inner_name]++;
									
								}else{
								
									$twitter_users[$name][$inner_name]=1;
									
								}
							
							}
							
						}
					
					}
					
				}
				
				$ratio = 360 / (count($twitter_users));
				
				$users = max(count($twitter_users), 200);
				
				$width = max(count($twitter_users) * 8, 1200);
				
				if($width > 10000){
				
					$width = min($width, 10000);
					
					$users = 19999;
					
					$font_size = 4.0;
				
				}else{
				
					$font_size = 10.0;
				
				}
				
				$im = imagecreatetruecolor($width,$width);
				
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$clock_point = 0;	
				
				$interactions = array_keys($twitter_users);
				
				foreach($twitter_users as $key => $value){
				
					$y_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * sin( deg2rad($clock_point) ) );
					$x_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * cos( deg2rad($clock_point) ) );
					
					for($x=0;$x<count($interactions);$x++){
					
						if(isset($value[$interactions[$x]])){
						
							$line_y_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * sin( deg2rad($ratio * $x) ) );
							$line_x_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * cos( deg2rad($ratio * $x) ) );
							
							imageline($im, $x_pos, $y_pos, $line_x_pos, $line_y_pos, imagecolorallocate($im, (integer)(255),0,0));
							
						}
						
					}
					
					$clock_point+=$ratio;
					
				}
				
				foreach($twitter_users as $key => $value){
				
					$y_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * sin( deg2rad($clock_point) ) );
					$x_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * cos( deg2rad($clock_point) ) );
					
				
					imagettftext ( $im , $font_size , 0-$clock_point, $x_pos , $y_pos , $white , "core/misc/fonts/arial.ttf" , $key);
					$clock_point+=$ratio;
				
				}	
				
				$file_process->file_image_create("data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . ".jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network", "Tweet Network Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . ".jpg' />";

				return $output . "<p><a href='?tool=tweet_network'>" . $this->language->translate("tools/tweet_network", "Return to Tweet Network Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_network", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_network", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_network", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_network", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_network", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
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
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$twitter_users = array();
				
				foreach($data as $tweet){
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->user_mentions)!=1){
					
							foreach($tweet->entities->user_mentions as $key => $inner_data){
							
								if(!isset($twitter_users[$tweet->user->screen_name])){
								
									$twitter_users[$tweet->user->screen_name] = array();
									$twitter_users[$tweet->user->screen_name]["number_of_tweets"] = 1;
								
								}
								
								if(!isset($twitter_users[$inner_data->screen_name])){
								
									$twitter_users[$inner_data->screen_name] = array();
									$twitter_users[$inner_data->screen_name]["number_of_tweets"] = 0;
								
								}
								
								if(isset($twitter_users[$tweet->user->screen_name][$inner_data->screen_name])){
								
									$twitter_users[$tweet->user->screen_name][$inner_data->screen_name]++;
									$twitter_users[$tweet->user->screen_name]["number_of_tweets"]++;
									
								}else{
								
									$twitter_users[$tweet->user->screen_name][$inner_data->screen_name]=1;
									
								}
							
							}
							
						}
					
					}
					
				}
				
				foreach($twitter_users as $key => $data){
				
					if($data['number_of_tweets'] < $_POST['tweet_cutoff']){
					
						unset($twitter_users[$key]);
					
					}
					
				}
				
				$ratio = 360 / (count($twitter_users));
				
				$users = max(count($twitter_users), 200);
				
				$width = max(count($twitter_users) * 8, 1200);
				
				$im = imagecreatetruecolor($width,$width);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$clock_point = 0;	
				
				$interactions = array_keys($twitter_users);
				
				foreach($twitter_users as $key => $value){
				
					$y_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * sin( deg2rad($clock_point) ) );
					$x_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * cos( deg2rad($clock_point) ) );
					
					for($x=0;$x<count($interactions);$x++){
					
						if(isset($value[$interactions[$x]])){
						
							$line_y_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * sin( deg2rad($ratio * $x) ) );
							$line_x_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * cos( deg2rad($ratio * $x) ) );
							
							if(!isset($twitter_users[$key][$interactions[$x]])){
							
								imageline($im, $x_pos, $y_pos, $line_x_pos, $line_y_pos, imagecolorallocate($im, (integer)(255),0,0));
								
							}else{
							
								if(isset($twitter_users[$interactions[$x]][$key])){
								
									unset($twitter_users[$interactions[$x]][$key]);
								
								}
							
								imageline($im, $x_pos, $y_pos, $line_x_pos, $line_y_pos, $white);
							
							}
							
						}
						
					}
				
					imagettftext ( $im , 10.0 , 0-$clock_point, $x_pos , $y_pos , $white , "core/misc/fonts/arial.ttf" , $key);
					$clock_point+=$ratio;
				
				}	
				
				$file_process->file_image_create("data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_bi.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network", "Tweet Network Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_bi.jpg' />";

				return $output . "<p><a href='?tool=tweet_network'>" . $this->language->translate("tools/tweet_network", "Return to Tweet Network Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_network", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_network", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_network", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<label>" . $this->language->translate("tools/tweet_network", "Number of tweets a user must have tweeted to appear on the network") . "</label>
									<input type='text' size=100 name='tweet_cutoff' value='" . $this->language->translate("tools/tweet_network", "Number of tweets a user must have tweeted to appear") . "' />
									<input type='submit' value='" . $this->language->translate("tools/tweet_network", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_network", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function binetwork(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$twitter_users = array();
				
				foreach($data as $tweet){
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->user_mentions)!=0){
					
							foreach($tweet->entities->user_mentions as $key => $inner_data){
							
								if(!isset($twitter_users[$tweet->user->screen_name])){
								
									$twitter_users[$tweet->user->screen_name] = array();
								
								}
								
								if(!isset($twitter_users[$inner_data->screen_name])){
								
									$twitter_users[$inner_data->screen_name] = array();
								
								}
								
								if(isset($twitter_users[$tweet->user->screen_name][$inner_data->screen_name])){
								
									$twitter_users[$tweet->user->screen_name][$inner_data->screen_name]++;
									
								}else{
								
									$twitter_users[$tweet->user->screen_name][$inner_data->screen_name]=1;
									
								}
							
							}
							
						}
					
					}
					
				}
				
				$ratio = 360 / (count($twitter_users));
				
				$users = max(count($twitter_users), 200);
				
				$width = max(count($twitter_users) * 8, 1200);
				
				if($width > 10000){
				
					$width = min($width, 10000);
					
					$users = 19999;
					
					$font_size = 4.0;
				
				}else{
				
					$font_size = 10.0;
				
				}
				
				$im = imagecreatetruecolor($width,$width);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$width = max(count($twitter_users) * 8, 1200);
				
				if($width > 10000){
				
					$width = min($width, 10000);
					
					$users = 19999;
					
					$font_size = 4.0;
				
				}else{
				
					$font_size = 10.0;
				
				}
				
				$clock_point = 0;	
				
				$interactions = array_keys($twitter_users);
				
				foreach($twitter_users as $key => $value){
				
					$y_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * sin( deg2rad($clock_point) ) );
					$x_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * cos( deg2rad($clock_point) ) );
					
					for($x=0;$x<count($interactions);$x++){
					
						if(isset($value[$interactions[$x]])){
						
							$line_y_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * sin( deg2rad($ratio * $x) ) );
							$line_x_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * cos( deg2rad($ratio * $x) ) );
							
							if(isset($twitter_users[$key][$interactions[$x]])&&isset($twitter_users[$interactions[$x]][$key])){
							
								imageline($im, $x_pos, $y_pos, $line_x_pos, $line_y_pos, $white);
								
							}else{
							
								imageline($im, $x_pos, $y_pos, $line_x_pos, $line_y_pos, imagecolorallocate($im, (integer)(255),0,0));
								
							}
							
						}
						
					}
					
					$clock_point+=$ratio;
					
				}
				
				foreach($twitter_users as $key => $value){
				
					$y_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * sin( deg2rad($clock_point) ) );
					$x_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * cos( deg2rad($clock_point) ) );
				
					imagettftext ( $im , $font_size , 0-$clock_point, $x_pos , $y_pos , $white , "core/misc/fonts/arial.ttf" , $key);
					$clock_point+=$ratio;
				
				}	
				
				$file_process->file_image_create("data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_bi.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network", "Tweet Network Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_bi.jpg' />";

				return $output . "<p><a href='?tool=tweet_network'>" . $this->language->translate("tools/tweet_network", "Return to Tweet Network Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_network", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_network", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_network", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_network", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_network", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		
		private function onlybinetwork(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$twitter_users = array();
				
				foreach($data as $tweet){
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->user_mentions)!=0){
					
							foreach($tweet->entities->user_mentions as $key => $inner_data){
							
								if(!isset($twitter_users[$tweet->user->screen_name])){
								
									$twitter_users[$tweet->user->screen_name] = array();
								
								}
								
								if(!isset($twitter_users[$inner_data->screen_name])){
								
									$twitter_users[$inner_data->screen_name] = array();
								
								}
								
								if(isset($twitter_users[$tweet->user->screen_name][$inner_data->screen_name])){
								
									$twitter_users[$tweet->user->screen_name][$inner_data->screen_name]++;
									
								}else{
								
									$twitter_users[$tweet->user->screen_name][$inner_data->screen_name]=1;
									
								}
							
							}
							
						}
					
					}
					
				}
				
				$ratio = 360 / (count($twitter_users));
				
				$users = max(count($twitter_users), 200);
				
				$width = max(count($twitter_users) * 8, 1200);
				
				if($width > 10000){
				
					$width = min($width, 10000);
					
					$users = 19999;
					
					$font_size = 4.0;
				
				}else{
				
					$font_size = 10.0;
				
				}
				
				$im = imagecreatetruecolor($width,$width);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$clock_point = 0;	
				
				$interactions = array_keys($twitter_users);
				
				foreach($twitter_users as $key => $value){
				
					$y_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * sin( deg2rad($clock_point) ) );
					$x_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * cos( deg2rad($clock_point) ) );
					
					for($x=0;$x<count($interactions);$x++){
					
						if(isset($value[$interactions[$x]])){
						
							$line_y_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * sin( deg2rad($ratio * $x) ) );
							$line_x_pos = ($width / 2) + (integer)round( (($width / 2) - ($users/2)) * cos( deg2rad($ratio * $x) ) );
							
							if(isset($twitter_users[$key][$interactions[$x]])){
							
								if(isset($twitter_users[$interactions[$x]][$key])){
								
									unset($twitter_users[$interactions[$x]][$key]);
								
								}
							
								imageline($im, $x_pos, $y_pos, $line_x_pos, $line_y_pos, $white);
							
							}
							
						}
						
					}
				
					imagettftext ( $im , $font_size , 0-$clock_point, $x_pos , $y_pos , $white , "core/misc/fonts/arial.ttf" , $key);
					$clock_point+=$ratio;
				
				}	
				
				$file_process->file_image_create("data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_onlybi.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network", "Tweet Network Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_onlybi.jpg' />";

				return $output . "<p><a href='?tool=tweet_network'>" . $this->language->translate("tools/tweet_network", "Return to Tweet Network Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_network", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_network", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_network", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_network", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_network", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}