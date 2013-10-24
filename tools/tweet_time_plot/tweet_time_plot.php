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
		
		private function map_tweets_time($data){
		
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
				
				if(count($windows[$window])>$max){
				
					$max = count($windows[$window]);
				
				}
			
			}
			
			return array($windows,$max,$width,$first,$last);
		
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
				
				$response = $this->map_tweets_time($data);
				
				$windows = $response[0];
				$max = $response[1];
				$width = $response[2];
				$first = $response[3];
				$last = $response[4];
				
				$width_val = round($width/$_POST['time_period']);
				
				$im = imagecreatetruecolor(($width_val*11) + 200, 800);
				$white = imagecolorallocate($im, 255,255,255);
				
				imageline($im, 100, 620, 100, 25, $white);
				imageline($im, 100, 620, ($width_val*11) + 150, 620, $white);
				
				$x_pos = 125;
				$y_pos = 620;
				
				for($x=0; $x<=($last-$first); $x+=$_POST['time_period']){
				
					if(isset($windows[$x])){
				
						$height = ((count($windows[$x])/$max)*600);
						
					}else{
					
						$height = 0;
					
					}
					
					imageline($im, $x_pos, $y_pos, $x_pos, $y_pos - $height, $white);
					imagettftext( $im , 8.0 , 270, $x_pos, 625, $white , "core/misc/fonts/arial.ttf" , date("H:i:s jS M Y", $first+($x)));
					
					$x_pos+=11;
					
				}
				
				imagettftext ( $im , 10.0 , 0, 80 , 620, $white , "core/misc/fonts/arial.ttf" , 0);
				
				$y_pos_orig = 620 - (600/5);
				
				$height = 600 / 5;
				
				for($x=1;$x<=5;$x++){
				
					imagettftext( $im , 10.0 , 0, 80 , $y_pos_orig, $white , "core/misc/fonts/arial.ttf" ,round(($max/6)*$x,0));
					
					$y_pos_orig -= $height;
				
				}
				
				imagettftext( $im , 8.0 , 0, 125, 760, $white , "core/misc/fonts/arial.ttf" , date("H:i:s jS M Y", $first));
				
				imagettftext( $im , 8.0 , 0, 125, 775, $white , "core/misc/fonts/arial.ttf" , date("H:i:s jS M Y", $last));
				
				$file_process->file_image_create("data/twitter_harvest/files/time_plot/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_time_plot.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_time_plot", "Tweet Time Plot") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/time_plot/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_time_plot.jpg' />";

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
				
				$response = $this->map_tweets_time($data);
				
				$windows = $response[0];
				$max = $response[1];
				$width = $response[2];
				$first = $response[3];
				$last = $response[4];
				
				$width_val = round($width/$_POST['time_period']);
				
				$im = imagecreatetruecolor(($width_val*20) + 200, 800);
				$white = imagecolorallocate($im, 255,255,255);
				
				imageline($im, 100, 620, 100, 25, $white);
				imageline($im, 100, 620, ($width_val*20) + 150, 620, $white);
				
				$x_pos = 125;
				$y_pos = 620;
				
				for($x=0; $x<=($last-$first); $x+=$_POST['time_period']){
				
					$cloud = array();
				
					if(isset($windows[$x])){
					
						$words = $windows[$x];
						
						foreach($words as $index => $tweets){
						
							foreach($tweets as $tweet){
							
								$word_list = explode(" ", $tweet->text);
								
								foreach($word_list as $word){
								
									$word = strtolower(trim($word));
								
									if($word!="rt" && strlen($word)>=2 && substr($word,0,1)!="@"){
								
										if(isset($cloud[$word])){
										
											$cloud[$word]++;
										
										}else{
										
											$cloud[$word]=1;
										
										}
										
									}
								
								}
							
							}
						
						}
				
						$height = ((count($windows[$x])/$max)*600);
						
					}else{
					
						$height = 0;
					
					}
					
					imageline($im, $x_pos, $y_pos, $x_pos, $y_pos - $height, $white);
					imagettftext( $im , 8.0 , 270, $x_pos, 625, $white , "core/misc/fonts/arial.ttf" , date("H:i:s jS M Y", $first+($x)));
					
					$small_cloud = array_slice($cloud,0,5);
					
					$string = "";
					
					foreach($small_cloud as $word => $value){
					
						$string .= $word . " (" . $value . ") ";
					
					}
					
					imagettftext( $im , 8.0 , 90, $x_pos+13, 610, $white , "core/misc/fonts/arial.ttf" , $string);
					
					$x_pos+=20;
					
				}
				
				imagettftext ( $im , 10.0 , 0, 80 , 620, $white , "core/misc/fonts/arial.ttf" , 0);
				
				$y_pos_orig = 620 - (600/5);
				
				$height = 600 / 5;
				
				for($x=1;$x<=5;$x++){
				
					imagettftext( $im , 10.0 , 0, 80 , $y_pos_orig, $white , "core/misc/fonts/arial.ttf" ,round(($max/6)*$x,0));
					
					$y_pos_orig -= $height;
				
				}
				
				imagettftext( $im , 8.0 , 0, 125, 760, $white , "core/misc/fonts/arial.ttf" , date("H:i:s jS M Y", $first));
				
				imagettftext( $im , 8.0 , 0, 125, 775, $white , "core/misc/fonts/arial.ttf" , date("H:i:s jS M Y", $last));
				
				$file_process->file_image_create("data/twitter_harvest/files/time_plot/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_time_plot_words.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_time_plot", "Tweet Time Plot") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/time_plot/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_time_plot_words.jpg' />";

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
				
				$response = $this->map_tweets_time($data);
				
				$windows = $response[0];
				$max = $response[1];
				$width = $response[2];
				$first = $response[3];
				$last = $response[4];
				
				$width_val = round($width/$_POST['time_period']);
				
				$im = imagecreatetruecolor(($width_val*20) + 200, 800);
				$white = imagecolorallocate($im, 255,255,255);
				
				imageline($im, 100, 620, 100, 25, $white);
				imageline($im, 100, 620, ($width_val*20) + 150, 620, $white);
				
				$x_pos = 125;
				$y_pos = 620;
				
				$exclude = explode(",", strtolower($_POST['exclude']));
				
				for($x=0; $x<=($last-$first); $x+=$_POST['time_period']){
				
					$cloud = array();
				
					if(isset($windows[$x])){
					
						$words = $windows[$x];
						
						foreach($words as $index => $tweets){
						
							foreach($tweets as $tweet){
							
								$word_list = explode(" ", $tweet->text);
								
								foreach($word_list as $word){
								
									$word = strtolower(trim($word));
								
									if($word!="rt" && strlen($word)>=2 && substr($word,0,1)!="@" && !in_array($word,$exclude)){
								
										if(isset($cloud[$word])){
										
											$cloud[$word]++;
										
										}else{
										
											$cloud[$word]=1;
										
										}
										
									}
								
								}
							
							}
						
						}
				
						$height = ((count($windows[$x])/$max)*600);
						
					}else{
					
						$height = 0;
					
					}
					
					imageline($im, $x_pos, $y_pos, $x_pos, $y_pos - $height, $white);
					imagettftext( $im , 8.0 , 270, $x_pos, 625, $white , "core/misc/fonts/arial.ttf" , date("H:i:s jS M Y", $first+$x));
					
					$small_cloud = array_slice($cloud,0,5);
					
					$string = "";
					
					foreach($small_cloud as $word => $value){
					
						$string .= $word . " (" . $value . ") ";
					
					}
					
					imagettftext( $im , 8.0 , 90, $x_pos+13, 610, $white , "core/misc/fonts/arial.ttf" , $string);
					
					$x_pos+=20;
					
				}
				
				imagettftext ( $im , 10.0 , 0, 80 , 620, $white , "core/misc/fonts/arial.ttf" , 0);
				
				$y_pos_orig = 620 - (600/5);
				
				$height = 600 / 5;
				
				for($x=1;$x<=5;$x++){
				
					imagettftext( $im , 10.0 , 0, 80 , $y_pos_orig, $white , "core/misc/fonts/arial.ttf" ,round(($max/6)*$x,0));
					
					$y_pos_orig -= $height;
				
				}
				
				imagettftext( $im , 8.0 , 0, 125, 760, $white , "core/misc/fonts/arial.ttf" , date("H:i:s jS M Y", $first));
				
				imagettftext( $im , 8.0 , 0, 125, 775, $white , "core/misc/fonts/arial.ttf" , date("H:i:s jS M Y", $last));
				
				$file_process->file_image_create("data/twitter_harvest/files/time_plot/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_time_plot_words.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_time_plot", "Tweet Time Plot") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/time_plot/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_time_plot_words.jpg' />";

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