<?PHP

	class tweet_user_graph extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_user_graph";
			$classification->name = "Tweet User Graph";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_user_graph/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_user_graph/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_user_graph", "Tweet User Graph Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_user_graph&action=instructions'>" . $this->language->translate("tools/tweet_user_graph", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_user_graph&action=mentions'>" . $this->language->translate("tools/tweet_user_graph", "Graph (Mentions of an Individual)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_user_graph&action=tweets'>" . $this->language->translate("tools/tweet_user_graph", "Graph (Number of tweets sent by an Individual)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_user_graph&action=mentionsgroup'>" . $this->language->translate("tools/tweet_user_graph", "Graph (Grouped by mentions)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_user_graph&action=tweetsgroup'>" . $this->language->translate("tools/tweet_user_graph", "Graph (Grouped by number of tweets sent by an Individual)") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_user_graph", "help");
			
			return $output . "<p><a href='?tool=tweet_user_graph'>" . $this->language->translate("tools/tweet_user_graph", "Return to Tweet User Graph") . "</a></p>";
				
		}
		
		private function mentions(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_user_graph", "Tweet User Graph for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$twitter_users = array();
				$twitter_connections = array();
				
				foreach($data as $tweet){
					
					$name = strtolower($tweet->user->screen_name);
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->user_mentions)!=0){
						
							if(!isset($twitter_users[$name])){
								
								$twitter_users[$name] = 1;
							
							}
							
							foreach($tweet->entities->user_mentions as $key => $inner_data){
							
								$inner_name = strtolower($inner_data->screen_name);
								
								if(!isset($twitter_connections[$inner_name])){
							
									$twitter_connections[$inner_name] = 0;
						
								}
								
								$twitter_connections[$inner_name]++;
							
							}
							
						}
					
					}
					
				}
				
				$size = (count($twitter_connections)*2) + 100;
				
				$font_size = 10.0;
				
				arsort($twitter_connections);
				
				$top = array_shift($twitter_connections);
				array_unshift($twitter_connections, $top);
				
				$im = imagecreatetruecolor($size,$top+100);
				
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$x_pos = 50;
								
				foreach($twitter_connections as $key => $value){
				
					$name = $key;
					
					if(!isset($twitter_users[$name])){
						
						imageline($im, $x_pos, $top+50, $x_pos, ($top+50) - $value, $white);
										
					}else{
					
						imageline($im, $x_pos, $top+50, $x_pos, ($top+50) - $value, $red);
					
					}
					
					$x_pos +=2;
					
				}
				
				$file_process->file_image_create("data/twitter_harvest/files/graph/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_mentionsgraph.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_user_graph", "Tweet User Graph") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/graph/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_mentionsgraph.jpg' />";

				return $output . "<p><a href='?tool=tweet_user_graph'>" . $this->language->translate("tools/tweet_user_graph", "Return to Tweet User Graph Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_user_graph", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_user_graph", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_user_graph", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_user_graph", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_user_graph", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function tweets(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_user_graph", "Tweet User Graph for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$twitter_users = array();
				$twitter_connections = array();
				
				foreach($data as $tweet){
					
					$name = strtolower($tweet->user->screen_name);
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->user_mentions)!=0){
						
							if(!isset($twitter_users[$name])){
								
								$twitter_users[$name] = 0;
							
							}
								
							$twitter_users[$name]++;
							
						}
					
					}
					
				}
				
				$size = (count($twitter_users)*2) + 100;
				
				$font_size = 10.0;
				
				arsort($twitter_users);
				
				$top = array_shift($twitter_users);
				array_unshift($twitter_users, $top);
				
				$im = imagecreatetruecolor($size,$top+100);
				
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$x_pos = 50;
								
				foreach($twitter_users as $key => $value){
				
					$name = $key;
					
					imageline($im, $x_pos, $top+50, $x_pos, ($top+50) - $value, $red);
					
					$x_pos +=2;
					
				}
				
				$file_process->file_image_create("data/twitter_harvest/files/graph/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_tweetsgraph.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_user_graph", "Tweet User Graph") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/graph/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_tweetsgraph.jpg' />";

				return $output . "<p><a href='?tool=tweet_user_graph'>" . $this->language->translate("tools/tweet_user_graph", "Return to Tweet User Graph Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_user_graph", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_user_graph", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_user_graph", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_user_graph", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_user_graph", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function mentionsgroup(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_user_graph", "Tweet User Graph for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$twitter_users = array();
				$twitter_connections = array();
				
				foreach($data as $tweet){
					
					$name = strtolower($tweet->user->screen_name);
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->user_mentions)!=0){
						
							if(!isset($twitter_users[$name])){
								
								$twitter_users[$name] = 1;
							
							}
							
							foreach($tweet->entities->user_mentions as $key => $inner_data){
							
								$inner_name = strtolower($inner_data->screen_name);
								
								if(!isset($twitter_connections[$inner_name])){
							
									$twitter_connections[$inner_name] = 0;
						
								}
								
								$twitter_connections[$inner_name]++;
							
							}
							
						}
					
					}
					
				}
				
				$connections_total = array();
				
				foreach($twitter_connections as $key => $value){
				
					if(!isset($connections_total[$value])){
					
						$connections_total[$value] = 0;
					
					}
					
					$connections_total[$value]++;
				
				}
				
				$size = (count($connections_total)*15) + 100;
				
				ksort($connections_total);
				
				$top = max($connections_total);
				
				$im = imagecreatetruecolor($size,$top+100);
				
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$x_pos = 50;
								
				foreach($connections_total as $key => $value){
				
					imageline($im, $x_pos, $top+50, $x_pos, ($top+50) - $value, $red);
					
					imagettftext ( $im , 8.0 , 270 , $x_pos , $top+50 , $white , "core/misc/fonts/arial.ttf" , $key . "(" . $value . ")");
					
					$x_pos +=15;
					
				}
				
				$file_process->file_image_create("data/twitter_harvest/files/graph/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_mentionsgroupgraph.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_user_graph", "Tweet User Graph") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/graph/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_mentionsgroupgraph.jpg' />";

				return $output . "<p><a href='?tool=tweet_user_graph'>" . $this->language->translate("tools/tweet_user_graph", "Return to Tweet User Graph Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_user_graph", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_user_graph", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_user_graph", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_user_graph", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_user_graph", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function tweetsgroup(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_user_graph", "Tweet User Graph for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$twitter_users = array();
				$twitter_connections = array();
				
				foreach($data as $tweet){
					
					$name = strtolower($tweet->user->screen_name);
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->user_mentions)!=0){
						
							if(!isset($twitter_users[$name])){
								
								$twitter_users[$name] = 0;
							
							}
								
							$twitter_users[$name]++;
							
						}
					
					}
					
				}
				
				$tweets_total = array();
				
				foreach($twitter_users as $key => $value){
				
					if(!isset($tweets_total[$value])){
					
						$tweets_total[$value] = 0;
					
					}
					
					$tweets_total[$value]++;
				
				}
				
				$size = (count($tweets_total)*15) + 100;
				
				ksort($tweets_total);
				
				$top = max($tweets_total);
				
				$im = imagecreatetruecolor($size,$top+100);
				
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$x_pos = 50;
								
				foreach($tweets_total as $key => $value){
				
					imageline($im, $x_pos, $top+50, $x_pos, ($top+50) - $value, $red);
					
					imagettftext ( $im , 8.0 , 270 , $x_pos , $top+50 , $white , "core/misc/fonts/arial.ttf" , $key . "(" . $value . ")");
					
					$x_pos +=15;
					
				}
				
				$file_process->file_image_create("data/twitter_harvest/files/graph/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_tweetsgroupgraph.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_user_graph", "Tweet User Graph") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/graph/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_tweetsgroupgraph.jpg' />";

				return $output . "<p><a href='?tool=tweet_user_graph'>" . $this->language->translate("tools/tweet_user_graph", "Return to Tweet User Graph Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_user_graph", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_user_graph", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_user_graph", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_user_graph", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_user_graph", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}