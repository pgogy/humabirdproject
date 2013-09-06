<?PHP

	class tweet_time_plot extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_time_plot";
			$classification->name = "Tweet Time Plot";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_time_plot/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_time_plot/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_time_plot", "Tweet Time Plot") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_time_plot&action=instructions'>" . $this->language->translate("tools/tweet_time_plot", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_time_plot&action=tweet_time_plot'>" . $this->language->translate("tools/tweet_time_plot", "Display Tweet time plot") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_time_plot&action=tweet_time_plot_words'>" . $this->language->translate("tools/tweet_time_plot", "Display Tweet time plot (with words)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_time_plot&action=tweet_time_plot_words_exclude'>" . $this->language->translate("tools/tweet_time_plot", "Display Tweet time plot (with words + exclusions)") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_time_plot", "help");
			
			return $output . "<p><a href='?tool=tweet_time_plot'>" . $this->language->translate("tools/tweet_time_plot", "Return to Tweet Time Map") . "</a></p>";
				
		}
		
		private function tweet_time_plot(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$tweets = array();
				
				$first = "";
		
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
				
					if($first==""){
					
						$first = $seconds;
					
					}
					
					$window = round(($first-$seconds)/$_POST['time_period']);
					
					if(!isset($tweets[$window])){
					
						$tweets[$window] = 0;
					
					}
					
					$tweets[$window]++;
					
				}
				
				$first = "";
				$last = "";
				
				$first_tweet = array_shift($data);
				$last_tweet = array_pop($data);
				
				$first = $first_tweet->created_at;
				$last = $last_tweet->created_at;
				
				$time = explode(" ", $first);
				
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
				
				$max = max($tweets);
				$min = min($tweets);
				
				$im = imagecreatetruecolor((count($tweets)*15) + 200, 1100);
				$white = imagecolorallocate($im, 255,255,255);
				
				imageline($im, 100, 980, 100, 25, $white);
				imageline($im, 100, 980, (count($tweets)*15) + 200, 980, $white);
				
				$scale = $max / 200;
				
				$x_pos = 105;
				$y_pos = 980;
				
				$height = ($max - $min)/5;
				
				foreach($tweets as $value){
				
					imageline($im, $x_pos, $y_pos, $x_pos, $y_pos - (($value/$max)*950), $white);
					imagettftext ( $im , 10.0 , 270, $x_pos , 1000, $white , "core/misc/fonts/arial.ttf" , date ("G:i:s", $seconds));
					$x_pos+=15;
					
					$seconds -= $_POST['time_period'];
					
				}	
				
				$y_pos = 980;	
				
				$height = 980 / 5;
				
				$y_pos_orig = 980 - $height;
				
				imagettftext ( $im , 10.0 , 0, 10 , 980, $white , "core/misc/fonts/arial.ttf" , 0);
				
				for($x=1;$x<=5;$x++){
				
					imagettftext ( $im , 10.0 , 0, 10 , $y_pos_orig, $white , "core/misc/fonts/arial.ttf" ,round(($max/6)*$x,0));
					
					$y_pos_orig -= $height;
				
				}
				
				imagettftext ( $im , 10.0 , 0, 150 , 20, $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_time_plot", "From") . " " . $last);
				imagettftext ( $im , 10.0 , 0, 150 , 40, $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_time_plot", "Till") . " " . $first);
				
				imagettftext ( $im , 10.0 , 0, 10 , 40, $white , "core/misc/fonts/arial.ttf" , $max);
				
				$file_process->file_image_create("data/twitter_harvest/files/time_plot/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_timeplot.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_time_plot", "Tweet Time Plot") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/time_plot/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_timeplot.jpg' />";

				return $output . "<p><a href='?tool=tweet_time_plot'>" . $this->language->translate("tools/tweet_time_plot", "Return to Tweet Time Map") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_time_plot", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_time_plot", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_time_plot", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
					
					$output .= "<label>" . $this->language->translate("data/twitter_time_plot", "Time period") . "</label>";
	
					$output .= "<input type='text' name='time_period' />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_time_plot", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_time_plot", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function tweet_time_plot_words(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$tweets = array();
				
				$first = "";
				
				$times = array();
		
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
				
					if($first==""){
					
						$first = $seconds;
					
					}
					
					$window = round(($first-$seconds)/$_POST['time_period']);
					
					if(!isset($tweets[$window])){
					
						$tweets[$window] = array(array(),0);
					
					}
					
					if(!isset($times[$window])){
					
						$times[$window] = 0;
					
					}					
					
					$words = explode(" ", strtolower($tweet->text));
					
					$tweets[$window][0] = array_merge($tweets[$window][0],$words);
					
					$tweets[$window][1]++;
					
					$times[$window]++;
					
				}
				
				$first = "";
				$last = "";
				
				$first_tweet = array_shift($data);
				$last_tweet = array_pop($data);
				
				$first = $first_tweet->created_at;
				$last = $last_tweet->created_at;
				
				$time = explode(" ", $first);
				
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
				
				$max = max($times);
				$min = min($times);
				
				$im = imagecreatetruecolor((count($tweets)*25) + 200, 1100);
				$white = imagecolorallocate($im, 255,255,255);
				
				imageline($im, 100, 980, 100, 25, $white);
				imageline($im, 100, 980, (count($tweets)*25) + 200, 980, $white);
				
				$scale = $max / 200;
				
				$x_pos = 105;
				$y_pos = 980;
				
				$height = ($max - $min)/5;
				
				foreach($tweets as $value){
				
					$cloud = array();
				
					foreach($value[0] as $word){
					
						$word = str_replace("\n", "", $word);
					
						if(substr($word,0,1)!=="#"){
							
							if(substr($word,0,1)!=="@"){
							
								if($word!="rt"){
								
									if(strlen($word)!=1){
							
										if(!isset($cloud[trim($word)])){
										
											$cloud[trim($word)]=0;
										
										}
										
										$cloud[trim($word)]++;
										
									}
									
								}
								
							}
							
						}
					
					}
					
					arsort($cloud);
					
					$cloud = array_slice($cloud,0,10);
				
					imageline($im, $x_pos, $y_pos, $x_pos, $y_pos - (($value[1]/$max)*950), $white);
					imagettftext ( $im , 10.0 , 270, $x_pos , 1000, $white , "core/misc/fonts/arial.ttf" , date ("G:i:s", $seconds));
					
					$string = "";
					
					foreach($cloud as $word => $value){
					
						$string .= $word . " (" . $value . ") ";
					
					}
					
					imagettftext ( $im , 8.0 , 90, $x_pos+12, 978, $white , "core/misc/fonts/arial.ttf" , $string);
					
					$x_pos+=25;
					
					$seconds -= $_POST['time_period'];
					
				}	
				
				$y_pos = 980;	
				
				$height = 980 / 5;
				
				$y_pos_orig = 980 - $height;
				
				imagettftext ( $im , 10.0 , 0, 10 , 980, $white , "core/misc/fonts/arial.ttf" , 0);
				
				for($x=1;$x<=5;$x++){
				
					imagettftext ( $im , 10.0 , 0, 10 , $y_pos_orig, $white , "core/misc/fonts/arial.ttf" ,round(($max/6)*$x,0));
					
					$y_pos_orig -= $height;
				
				}
				
				imagettftext ( $im , 10.0 , 0, 150 , 20, $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_time_plot", "From") . " " . $last);
				imagettftext ( $im , 10.0 , 0, 150 , 40, $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_time_plot", "Till") . " " . $first);
				
				imagettftext ( $im , 10.0 , 0, 10 , 40, $white , "core/misc/fonts/arial.ttf" , $max);
				
				$file_process->file_image_create("data/twitter_harvest/files/time_plot/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_timeplotword.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_time_plot", "Tweet Time Plot") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/time_plot/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_timeplotword.jpg' />";

				return $output . "<p><a href='?tool=tweet_time_plot'>" . $this->language->translate("tools/tweet_time_plot", "Return to Tweet Time Map") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_time_plot", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_time_plot", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_time_plot", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
					
					$output .= "<label>" . $this->language->translate("data/twitter_time_plot", "Time period") . "</label>";
	
					$output .= "<input type='text' name='time_period' />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_time_plot", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_time_plot", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function tweet_time_plot_words_exclude(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$tweets = array();
				
				$first = "";
				
				$times = array();
		
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
				
					if($first==""){
					
						$first = $seconds;
					
					}
					
					$window = round(($first-$seconds)/$_POST['time_period']);
					
					if(!isset($tweets[$window])){
					
						$tweets[$window] = array(array(),0);
					
					}
					
					if(!isset($times[$window])){
					
						$times[$window] = 0;
					
					}					
					
					$words = explode(" ", strtolower($tweet->text));
					
					$tweets[$window][0] = array_merge($tweets[$window][0],$words);
					
					$tweets[$window][1]++;
					
					$times[$window]++;
					
				}
				
				$first = "";
				$last = "";
				
				$first_tweet = array_shift($data);
				$last_tweet = array_pop($data);
				
				$first = $first_tweet->created_at;
				$last = $last_tweet->created_at;
				
				$time = explode(" ", $first);
				
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
				
				$max = max($times);
				$min = min($times);
				
				$im = imagecreatetruecolor((count($tweets)*25) + 200, 1100);
				$white = imagecolorallocate($im, 255,255,255);
				
				imageline($im, 100, 980, 100, 25, $white);
				imageline($im, 100, 980, (count($tweets)*25) + 200, 980, $white);
				
				$scale = $max / 200;
				
				$x_pos = 105;
				$y_pos = 980;
				
				$height = ($max - $min)/5;
				
				$exclude = explode(",", strtolower($_POST['exclude']));
				
				foreach($tweets as $value){
				
					$cloud = array();
				
					foreach($value[0] as $word){
					
						$word = str_replace("\n", "", $word);
					
						if(substr($word,0,1)!=="#"){
							
							if(substr($word,0,1)!=="@"){
							
								if($word!="rt"){
								
									if(strlen($word)!=1){
									
										if(!in_array($word, $exclude)){
								
											if(!isset($cloud[trim($word)])){
											
												$cloud[trim($word)]=0;
											
											}
											
											$cloud[trim($word)]++;
											
										}
										
									}
									
								}
								
							}
							
						}
					
					}
					
					arsort($cloud);
					
					$cloud = array_slice($cloud,0,10);
				
					imageline($im, $x_pos, $y_pos, $x_pos, $y_pos - (($value[1]/$max)*950), $white);
					imagettftext ( $im , 10.0 , 270, $x_pos , 1000, $white , "core/misc/fonts/arial.ttf" , date ("G:i:s", $seconds));
					
					$string = "";
					
					foreach($cloud as $word => $value){
					
						$string .= $word . " (" . $value . ") ";
					
					}
					
					imagettftext ( $im , 8.0 , 90, $x_pos+12, 978, $white , "core/misc/fonts/arial.ttf" , $string);
					
					$x_pos+=25;
					
					$seconds -= $_POST['time_period'];
					
				}	
				
				$y_pos = 980;	
				
				$height = 980 / 5;
				
				$y_pos_orig = 980 - $height;
				
				imagettftext ( $im , 10.0 , 0, 10 , 980, $white , "core/misc/fonts/arial.ttf" , 0);
				
				for($x=1;$x<=5;$x++){
				
					imagettftext ( $im , 10.0 , 0, 10 , $y_pos_orig, $white , "core/misc/fonts/arial.ttf" ,round(($max/6)*$x,0));
					
					$y_pos_orig -= $height;
				
				}
				
				imagettftext ( $im , 10.0 , 0, 150 , 20, $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_time_plot", "From") . " " . $last);
				imagettftext ( $im , 10.0 , 0, 150 , 40, $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_time_plot", "Till") . " " . $first);
				
				imagettftext ( $im , 10.0 , 0, 10 , 40, $white , "core/misc/fonts/arial.ttf" , $max);
				
				$file_process->file_image_create("data/twitter_harvest/files/time_plot/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_timeplotword.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_time_plot", "Tweet Time Plot") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/time_plot/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_timeplotword.jpg' />";

				return $output . "<p><a href='?tool=tweet_time_plot'>" . $this->language->translate("tools/tweet_time_plot", "Return to Tweet Time Map") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_time_plot", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_time_plot", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_time_plot", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
					
					$output .= "<label>" . $this->language->translate("data/twitter_time_plot", "Comma separated words to exclude") . "</label>";
	
					$output .= "<input type='text' name='exclude' />";
					
					$output .= "<label>" . $this->language->translate("data/twitter_time_plot", "Time period") . "</label>";
	
					$output .= "<input type='text' name='time_period' />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_time_plot", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_time_plot", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		
	}