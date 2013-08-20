<?PHP

	class tweet_time_density extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_time_density";
			$classification->name = "Tweet Time Watch";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_time_density/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_time_density/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_time_density", "Tweet Display for Time between tweets") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_time_density&action=instructions'>" . $this->language->translate("tools/tweet_time_density", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_time_density&action=alltweets'>" . $this->language->translate("tools/tweet_time_density", "Display Tweet time map") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_time_density&action=replytweets'>" . $this->language->translate("tools/tweet_time_density", "Display Tweet time map (replies only)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_time_density&action=averagetweets'>" . $this->language->translate("tools/tweet_time_density", "Display Tweet time map (distance from average)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_time_density&action=replyaveragetweets'>" . $this->language->translate("tools/tweet_time_density", "Display Tweet time map (replies only) (distance from average)") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_time_density", "help");
			
			return $output . "<p><a href='?tool=tweet_time_density'>" . $this->language->translate("tools/tweet_time_density", "Return to Tweet Time Map") . "</a></p>";
				
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
				
				$tweets = array();
				$user_mentions = array();
				
				$lasttime = "";
		
				foreach($data as $tweet){
				
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
				
					if($lasttime==""){
					
						$lasttime = $seconds;
					
					}else{
					
						$tweets[] = $seconds - $lasttime;
						$lasttime = $seconds;
					
					}
					
				}
				
				$tweets = array_reverse($tweets);
				
				$first = "";
				$last = "";
				
				$first_tweet = array_shift($data);
				$last_tweet = array_pop($data);
				
				$first = $first_tweet->created_at;
				$last = $last_tweet->created_at;
				
				$max = max(max($tweets), abs(min($tweets)));
				$min = min($tweets);
				
				$im = imagecreatetruecolor(count($tweets) + 200, 1000);
				$white = imagecolorallocate($im, 255,255,255);
				
				imageline($im, 100, 980, 100, 25, $white);
				imageline($im, 100, 980, count($tweets)+100, 980, $white);
				
				$scale = $max / 200;
				
				$x_pos = 105;
				$y_pos = 980;
				
				$height = ($max - $min)/5;
				
				foreach($tweets as $value){
				
					imageline($im, $x_pos, $y_pos, $x_pos, $y_pos + (($value/$max)*950), $white);
					$x_pos+=1;
					
				}	
				
				$y_pos = 980;	
				
				$height = 980 / 5;
				
				$y_pos_orig = 980 - $height;
				
				imagettftext ( $im , 10.0 , 0, 10 , 980, $white , "core/misc/fonts/arial.ttf" , 0);
				
				for($x=1;$x<=5;$x++){
				
					imagettftext ( $im , 10.0 , 0, 10 , $y_pos_orig, $white , "core/misc/fonts/arial.ttf" ,round(($max/6)*$x,0));
					
					$y_pos_orig -= $height;
				
				}
				
				imagettftext ( $im , 10.0 , 0, 150 , 20, $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_time_density", "From") . " " . $last);
				imagettftext ( $im , 10.0 , 0, 150 , 40, $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_time_density", "Till") . " " . $first);
				
				imagettftext ( $im , 10.0 , 0, 10 , 40, $white , "core/misc/fonts/arial.ttf" , $max);
				
				$file_process->file_image_create("data/twitter_harvest/files/time_map/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_timemap.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_time_density", "Tweet Time Map") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/time_map/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_timemap.jpg' />";

				return $output . "<p><a href='?tool=tweet_time_density'>" . $this->language->translate("tools/tweet_time_density", "Return to Tweet Time Map") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_time_density", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_time_density", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_time_density", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_time_density", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_time_density", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function replytweets(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$tweets = array();
				$user_mentions = array();
				
				$lasttime = "";
		
				foreach($data as $tweet){
				
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
				
					if($lasttime==""){
					
						$lasttime = $seconds;
					
					}else{
					
						if($tweet->in_reply_to_status_id_str!=""){
					
							$tweets[] = abs($seconds - $lasttime);
							$lasttime = $seconds;
						
						}
					
					}
					
				}
				
				$tweets = array_reverse($tweets);
				
				$max = max(max($tweets), abs(min($tweets)));
				
				$min = min($tweets);
				
				$im = imagecreatetruecolor(count($tweets) + 200, 1000);
				$white = imagecolorallocate($im, 255,255,255);
				
				imageline($im, 100, 980, 100, 25, $white);
				imageline($im, 100, 980, count($tweets)+100, 980, $white);
				
				$scale = $max / 200;
				
				$x_pos = 105;
				$y_pos = 980;
				
				$height = ($max - $min)/5;
				
				foreach($tweets as $value){
				
					imageline($im, $x_pos, $y_pos, $x_pos, $y_pos - (($value/$max)*950), $white);
					$x_pos+=1;
					
				}	
				
				$y_pos = 980;	
				
				$height = 980 / 5;
				
				$y_pos_orig = 980 - $height;
				
				imagettftext ( $im , 10.0 , 0, 10 , 980, $white , "core/misc/fonts/arial.ttf" , 0);
				
				for($x=1;$x<=5;$x++){
				
					imagettftext ( $im , 10.0 , 0, 10 , $y_pos_orig, $white , "core/misc/fonts/arial.ttf" ,round(($max/6)*$x,0));
					
					$y_pos_orig -= $height;
				
				}
				
				$first_tweet = array_shift($data);
				$last_tweet = array_pop($data);
				
				$first = $first_tweet->created_at;
				$last = $last_tweet->created_at;

				imagettftext ( $im , 8.0 , 0, 110 , 20, $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_time_density", "From") . " " . $last);
				imagettftext ( $im , 8.0 , 0, 110 , 40, $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_time_density", "Till") . " " . $first);
				
				imagettftext ( $im , 10.0 , 0, 10 , 40, $white , "core/misc/fonts/arial.ttf" , $max);
				
				$file_process->file_image_create("data/twitter_harvest/files/time_map/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_replytimemap.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_time_density", "Tweet Time Map") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/time_map/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_replytimemap.jpg' />";

				return $output . "<p><a href='?tool=tweet_time_density'>" . $this->language->translate("tools/tweet_time_density", "Return to Tweet Time Map") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_time_density", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_time_density", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_time_density", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_time_density", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_time_density", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function averagetweets(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_time_density", "Tweet Watch Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
				$user_mentions = array();
				
				$lasttime = "";
		
				foreach($data as $tweet){
				
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
				
					if($lasttime==""){
					
						$lasttime = $seconds;
					
					}else{
					
						$tweets[] = $seconds - $lasttime;
						
						$lasttime = $seconds;
					
					}
					
				}
				
				$tweets = array_reverse($tweets);
				
				$avg = array_sum($tweets) / count($tweets);
				$max = max(max($tweets), abs(min($tweets)));
				$min = min($tweets);
				
				echo $avg . "<br />";
				
				$im = imagecreatetruecolor(count($tweets) + 150, 1000);
				$white = imagecolorallocate($im, 255,255,255);
				
				imageline($im, 50, 980, 50, 25, $white);
				imageline($im, 50, 440, count($tweets)+100, 440, $white);
				
				$scale = $max / 200;
				
				$x_pos = 55;
				$y_pos = 980;
				
				$height = ($max - $min)/5;
				
				foreach($tweets as $value){
				
					imageline($im, $x_pos, 440, $x_pos, 440 + (($value/$max)*400), $white);
					$x_pos+=1;
					
				}	
				
				imagettftext ( $im , 10.0 , 0, 10 , 440, $white , "core/misc/fonts/arial.ttf" , 0);
				
				$height = 440 / 5;
				
				$y_pos_orig = 440 - $height;
				
				for($x=1;$x<=5;$x++){
				
					imagettftext ( $im , 10.0 , 0, 10 , $y_pos_orig, $white , "core/misc/fonts/arial.ttf" ,round(($max/6)*$x,0));
					
					$y_pos_orig -= $height;
				
				}
				
				$y_pos_orig = 440 + $height;
				
				for($x=1;$x<=5;$x++){
				
					imagettftext ( $im , 10.0 , 0, 10 , $y_pos_orig, $white , "core/misc/fonts/arial.ttf" ,round(($max/6)*$x,0));
					
					$y_pos_orig += $height;
				
				}

				
				$first_tweet = array_shift($data);
				$last_tweet = array_pop($data);
				
				$first = $first_tweet->created_at;
				$last = $last_tweet->created_at;

				imagettftext ( $im , 10.0 , 0, 150 , 20, $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_time_density", "From") . " " . $last);
				imagettftext ( $im , 10.0 , 0, 150 , 40, $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_time_density", "Till") . " " . $first);
				
				$file_process->file_image_create("data/twitter_harvest/files/time_map/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_timemapavg.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_time_density", "Tweet Watch for Links Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/time_map/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_timemapavg.jpg' />";

				return $output . "<p><a href='?tool=tweet_time_density'>" . $this->language->translate("tools/tweet_time_density", "Return to Tweet Time Map") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_time_density", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_time_density", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_time_density", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_time_density", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_time_density", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function replyaveragetweets(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_time_density", "Tweet Watch Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
				$user_mentions = array();
				
				$lasttime = "";
		
				foreach($data as $tweet){
				
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
				
					if($lasttime==""){
					
						$lasttime = $seconds;
					
					}else{
										
						if($tweet->in_reply_to_status_id_str!=""){	
					
							$tweets[] = $seconds - $lasttime;
							
						
						}
						
						$lasttime = $seconds;
					
					}
					
				}
				
				$tweets = array_reverse($tweets);
				
				$avg = array_sum($tweets) / count($tweets);
				
				$max = max(max($tweets), abs(min($tweets)));
				$min = min($tweets);
				
				$im = imagecreatetruecolor(count($tweets) + 150, 1000);
				$white = imagecolorallocate($im, 255,255,255);
				
				imageline($im, 50, 980, 50, 25, $white);
				imageline($im, 50, 440, count($tweets)+100, 440, $white);
				
				$scale = $max / 200;
				
				$x_pos = 55;
				$y_pos = 980;
				
				$height = 440/5;
				
				foreach($tweets as $value){
				
					imageline($im, $x_pos, 440, $x_pos, 440 + (($value/$max)*400), $white);
					$x_pos+=1;
					
				}	
				
				$y_pos_orig = 440 + $height;
				
				for($x=1;$x<=5;$x++){
				
					imagettftext ( $im , 10.0 , 0, 10 , $y_pos_orig, $white , "core/misc/fonts/arial.ttf" ,round(($max/6)*$x,0));
					
					$y_pos_orig += $height;
				
				}

				$y_pos_orig = 440 - $height;
				
				for($x=1;$x<=5;$x++){
				
					imagettftext ( $im , 10.0 , 0, 10 , $y_pos_orig, $white , "core/misc/fonts/arial.ttf" ,round(($max/6)*$x,0));
					
					$y_pos_orig -= $height;
				
				}
				
				$first_tweet = array_shift($data);
				$last_tweet = array_pop($data);
				
				$first = $first_tweet->created_at;
				$last = $last_tweet->created_at;

				imagettftext ( $im , 8.0 , 0, 60 , 10, $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_time_density", "From") . " " . $last);
				imagettftext ( $im , 8.0 , 0, 60 , 30, $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_time_density", "Till") . " " . $first);
				
				
				$file_process->file_image_create("data/twitter_harvest/files/time_map/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_replytimemapavg.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_time_density", "Tweet Watch for Links Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/time_map/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_replytimemapavg.jpg' />";

				return $output . "<p><a href='?tool=tweet_time_density'>" . $this->language->translate("tools/tweet_time_density", "Return to Tweet Time Map") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_time_density", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_time_density", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_time_density", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_time_density", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_time_density", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}