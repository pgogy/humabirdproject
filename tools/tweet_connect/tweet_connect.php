<?PHP

	class tweet_connect extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_connect";
			$classification->name = "Tweet Connections";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_connect/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_connect/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_connect", "Tweet Connections Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_connect&action=instructions'>" . $this->language->translate("tools/tweet_connect", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_connect&action=connect'>" . $this->language->translate("tools/tweet_connect", "Display Tweet Connections (all)") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_connect", "help");
			
			return $output . "<p><a href='?tool=tweet_connect'>" . $this->language->translate("tools/tweet_connect", "Return to Tweet Connections display") . "</a></p>";
				
		}
			
		private function connect(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_connect", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
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
								
								if(!isset($twitter_users[$name][$inner_name])){
								
									$twitter_users[$name][$inner_name]=1;
									
								}
								
								if(!isset($twitter_users[$inner_name][$name])){
								
									$twitter_users[$inner_name][$name]=1;
									
								}
							
							}
							
						}
					
					}
					
				}
				
				$connections = array();
				
				$max = 0;
				$max_name = 0;
				
				foreach($twitter_users as $name => $data){
				
					if(!isset($connections[count($data)])){
					
						$connections[count($data)]=0;
					
					}
					
					$connections[count($data)]++;
					
					if($max < count($data)){
					
						$max_name = $name;
						$max = count($data);
					
					}
				
				}
				
				ksort($connections);
				
				$degree_ratio = 360 / array_sum($connections);
				
				$im = imagecreatetruecolor(800,625 + count($connections)*40);
				$white = imagecolorallocate($im, 255,255,255);
				$last_angle = 0;
				$speakers_y = 650;
				
				$other_colour = imagecolorallocate($im, 255,255,255);
				$other_angle = 0;
				
				foreach($connections as $key => $data){
				
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

				imagettftext ( $im , 15.0 , 0 , 200 , $speakers_y , $white , "core/misc/fonts/arial.ttf" , $max . " "  . $max_name);
				
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