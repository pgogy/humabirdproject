<?PHP

	class tweet_hashtags extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_hashtags";
			$classification->name = "Tweet Hashtag Display";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_hashtags/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_hashtags/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_hashtags", "Tweet hashtag display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_hashtags&action=instructions'>" . $this->language->translate("tools/tweet_hashtags", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_hashtags&action=hashtags'>" . $this->language->translate("tools/tweet_hashtags", "Display hashtags") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_hashtags&action=hashtags_used'>" . $this->language->translate("tools/tweet_hashtags", "Display hashtags (Pie Chart)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_hashtags&action=hashtags_lowercase'>" . $this->language->translate("tools/tweet_hashtags", "Display hashtags (all lower case)") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_hashtags", "help");
			
			return $output . "<p><a href='?tool=tweet_hashtags'>" . $this->language->translate("tools/tweet_hashtags", "Return to Tweet Hashtags display") . "</a></p>";
				
		}
		
		private function hashtags_lowercase(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_hashtags", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$hashtags = array();
				
				foreach($data as $tweet){
				
					if(isset($tweet->entities->hashtags)){
				
						foreach($tweet->entities->hashtags as $key => $data){
						
							if(isset($hashtag[strtolower($data->text)])){
							
								$hashtag[strtolower($data->text)]++;
								
							}else{
							
								$hashtag[strtolower($data->text)]=1;
								
							}
						
						}
						
					}
					
				}
				
				arsort($hashtag);
				
				
				$output = "<h3>" . $this->language->translate("tools/tweet_hashtags", "Hashtags") . "</h3>";
				
				foreach($hashtag as $hash => $count){
				
					$output .= "<p>" . $hash . " : " . $count . "</p>";
				
				}
				
				return $output . "<p><a href='?tool=tweet_hashtags'>" . $this->language->translate("tools/tweet_hashtags", "Return to Tweet Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_hashtags", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_hashtags", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_hashtags", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_hashtags", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_hashtags", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function hashtags(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_hashtags", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$hashtags = array();
				
				foreach($data as $tweet){
				
					if(isset($tweet->entities->hashtags)){
				
						foreach($tweet->entities->hashtags as $key => $data){
						
							if(isset($hashtag[$data->text])){
							
								$hashtag[$data->text]++;
								
							}else{
							
								$hashtag[$data->text]=1;
								
							}
						
						}
						
					}
					
				}
				
				arsort($hashtag);
				
				
				$output = "<h3>" . $this->language->translate("tools/tweet_hashtags", "Hashtags") . "</h3>";
				
				foreach($hashtag as $hash => $count){
				
					$output .= "<p>" . $hash . " : " . $count . "</p>";
				
				}
				
				return $output . "<p><a href='?tool=tweet_hashtags'>" . $this->language->translate("tools/tweet_hashtags", "Return to Tweet Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_hashtags", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_hashtags", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_hashtags", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_hashtags", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_hashtags", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
	
		private function hashtags_used(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_connect", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$twitter_hashtags = array();
				
				foreach($data as $tweet){
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->hashtags)!=0){
					
							foreach($tweet->entities->hashtags as $key => $inner_data){
							
								$hashtag = strtolower($inner_data->text);
								
								if(!isset($twitter_hashtags[$hashtag])){
								
									$twitter_hashtags[$hashtag]=0;
									
								}
								
								$twitter_hashtags[$hashtag]++;
							
							}
							
						}
					
					}	
					
				}
				
				arsort($twitter_hashtags);
				
				$degree_ratio = 360 / array_sum($twitter_hashtags);
				
				$im = imagecreatetruecolor(800,625 + count($twitter_hashtags)*40);
				$white = imagecolorallocate($im, 255,255,255);
				$last_angle = 0;
				$speakers_y = 650;
				
				$other_colour = imagecolorallocate($im, 255,255,255);
				$other_angle = 0;
				
				foreach($twitter_hashtags as $key => $data){
				
					if(($data*$degree_ratio)>1){
					
						$colour = imagecolorallocate($im, rand(10,240),rand(10,240),rand(10,240));
						imagefilledarc ($im , 400 , 400 , 450 , 450 , $last_angle , $last_angle+($data*$degree_ratio) , $colour, IMG_ARC_PIE );
						imagefilledrectangle($im, 170, $speakers_y-15 , 190, $speakers_y , $colour );
						imagettftext ( $im , 15.0 , 0 , 200 , $speakers_y , $white , "core/misc/fonts/arial.ttf" , $key . " "  . $data);
						$speakers_y += 18;
						
					}else{
					
						$colour = imagecolorallocate($im, rand(10,240),rand(10,240),rand(10,240));
						imagefilledrectangle($im, 170, $speakers_y-15 , 190, $speakers_y , $colour );
						imagettftext ( $im , 15.0 , 0 , 200 , $speakers_y , $white , "core/misc/fonts/arial.ttf" , $key . " "  . $data);
						$speakers_y += 18;
					
					}

					$last_angle += (integer)$data*$degree_ratio;
					
				}	

				$file_process->file_image_create("data/twitter_harvest/files/connections/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . ".jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_connect", "Tweet Connections Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/connections/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . ".jpg' />";

				return $output . "<p><a href='?tool=tweet_connect'>" . $this->language->translate("tools/tweet_connect", "Return to Tweet Connections Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_connect", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_connect", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_connect", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_connect", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_connect", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
	
	}