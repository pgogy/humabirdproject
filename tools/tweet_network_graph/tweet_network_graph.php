<?PHP

	class tweet_network_graph extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_network_graph";
			$classification->name = "Tweet Network Graph";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_network_graph/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_network_graph/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_network_graph", "Tweet Network Graph Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_network_graph&action=instructions'>" . $this->language->translate("tools/tweet_network_graph", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_network_graph&action=order_network'>" . $this->language->translate("tools/tweet_network_graph", "Network (Time conversation entered ordered)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_network_graph&action=connections_network'>" . $this->language->translate("tools/tweet_network_graph", "Network (Connection ordered)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_network_graph&action=mentions_network'>" . $this->language->translate("tools/tweet_network_graph", "Network (Mentions ordered)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_network_graph&action=tweets_network'>" . $this->language->translate("tools/tweet_network_graph", "Network (tweets sent)") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_network_graph", "help");
			
			return $output . "<p><a href='?tool=tweet_network_graph'>" . $this->language->translate("tools/tweet_network_graph", "Return to Tweet Network display") . "</a></p>";
				
		}
		
		private function order_network(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network_graph", "Tweet Network Graph for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$twitter_users = array();
				$twitter_connections = array();
				
				foreach($data as $tweet){
					
					$name = strtolower($tweet->user->screen_name);
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->user_mentions)!=0){
						
							if(!isset($twitter_users[$name])){
								
								$twitter_users[$name] = array();
							
							}
							
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
				
				$size = (count($twitter_users)*2) + 100;
				
				$font_size = 10.0;
				
				$im = imagecreatetruecolor($size,$size);
				
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				$interactions = array_keys($twitter_users);
				
				$y_pos = 50;
								
				foreach($twitter_users as $key => $value){
				
					$name = $key;
					
					$value = $twitter_users[$name];
				
					if(count($value)!=0){
					
						$names = array_keys($value);
						
						$proceed = false;
						
						foreach($names as $name){

							if(in_array($name,$interactions)){
							
								$proceed = true;
							
							}

						}
						
						if($proceed){
							
							for($x=0;$x<count($interactions);$x++){
							
								if(isset($value[$interactions[$x]])){
									
									imageline($im, 50, $y_pos, (count($twitter_users)*2)+50, 50+$x, $red);
									
								}
								
							}
							
							foreach($value as $name => $value){
							
								if(!in_array($name, $interactions)){
								
									imageline($im, 50, $y_pos, (count($twitter_users)*2)+50, (count($twitter_users)*2)+52, $white);
								
								}
							
							}
							
						}else{

							imageline($im, 50, $y_pos, (count($twitter_users)*2)+50, (count($twitter_users)*2)+54, $white);

						}
						
						$y_pos += 2;
						
					}
					
				}
				
				$file_process->file_image_create("data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_timeflatgraph.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network_graph", "Tweet Network Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_timeflatgraph.jpg' />";

				return $output . "<p><a href='?tool=tweet_network_graph'>" . $this->language->translate("tools/tweet_network_graph", "Return to Tweet Network Graph Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_network_graph", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_network_graph", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_network_graph", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_network_graph", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_network_graph", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		
		private function connections_network(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network_graph", "Tweet Network Graph for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$twitter_users = array();
				$twitter_connections = array();
				
				foreach($data as $tweet){
					
					$name = strtolower($tweet->user->screen_name);
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->user_mentions)!=0){
						
							if(!isset($twitter_users[$name])){
								
								$twitter_users[$name] = array();
							
							}
							
							if(!isset($twitter_connections[$name])){
							
								$twitter_connections[$name] = 0;
					
							}
							
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
								
								$twitter_connections[$name]++;
							
							}
							
						}
					
					}
					
				}
				
				foreach($twitter_users as $key => $value){
				
					if(count($value)==0){
					
						unset($twitter_users[$key]);
					
					}
				
				}
				
				$size = (count($twitter_users)*2) + 100;
				
				$font_size = 10.0;
				
				$im = imagecreatetruecolor($size,$size);
				
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				arsort($twitter_connections);
				
				$interactions = array_keys($twitter_connections);
				
				$y_pos = 50;
								
				foreach($twitter_connections as $key => $value){
				
					$name = $key;
					
					$value = $twitter_users[$name];
				
					if(count($value)!=0){
					
						$names = array_keys($value);
						
						$proceed = false;
						
						foreach($names as $name){

							if(in_array($name,$interactions)){
							
								$proceed = true;
							
							}

						}
						
						if($proceed){
							
							for($x=0;$x<count($interactions);$x++){
							
								if(isset($value[$interactions[$x]])){
									
									imageline($im, 50, $y_pos, (count($twitter_users)*2)+50, 50+$x, $red);
									
								}
								
							}
							
							foreach($value as $name => $value){
							
								if(!in_array($name, $interactions)){
								
									imageline($im, 50, $y_pos, (count($twitter_users)*2)+50, (count($twitter_users)*2)+52, $white);
								
								}
							
							}
							
						}else{

							imageline($im, 50, $y_pos, (count($twitter_users)*2)+50, (count($twitter_users)*2)+54, $white);

						}
						
						$y_pos += 2;
						
					}
					
				}
				
				$file_process->file_image_create("data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_flatgraph.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network_graph", "Tweet Network Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_flatgraph.jpg' />";

				return $output . "<p><a href='?tool=tweet_network_graph'>" . $this->language->translate("tools/tweet_network_graph", "Return to Tweet Network Graph Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_network_graph", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_network_graph", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_network_graph", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_network_graph", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_network_graph", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function mentions_network(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network_graph", "Tweet Network Graph for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$twitter_users = array();
				$twitter_connections = array();
				
				foreach($data as $tweet){
					
					$name = strtolower($tweet->user->screen_name);
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->user_mentions)!=0){
						
							if(!isset($twitter_users[$name])){
								
								$twitter_users[$name] = array();
							
							}
							
							foreach($tweet->entities->user_mentions as $key => $inner_data){
							
								$inner_name = strtolower($inner_data->screen_name);
								
								if(!isset($twitter_connections[$inner_name])){
							
									$twitter_connections[$inner_name] = 0;
						
								}
								
								if(!isset($twitter_users[$inner_name])){
								
									$twitter_users[$inner_name] = array();
								
								}
								
								if(isset($twitter_users[$name][$inner_name])){
								
									$twitter_users[$name][$inner_name]++;
									
								}else{
								
									$twitter_users[$name][$inner_name]=1;
									
								}
								
								$twitter_connections[$inner_name]++;
							
							}
							
						}
					
					}
					
				}
				
				foreach($twitter_users as $key => $value){
				
					if(count($value)==0){
					
						unset($twitter_users[$key]);
					
					}
				
				}
				
				$size = (count($twitter_users)*2) + 100;
				
				$font_size = 10.0;
				
				$im = imagecreatetruecolor($size,$size);
				
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				arsort($twitter_connections);
				
				$interactions = array_keys($twitter_connections);
				
				$y_pos = 50;
								
				foreach($twitter_connections as $key => $value){
				
					$name = $key;
					
					if(isset($twitter_users[$name])){
						
						$value = $twitter_users[$name];
					
						if(count($value)!=0){
						
							$names = array_keys($value);
							
							$proceed = false;
							
							foreach($names as $name){

								if(in_array($name,$interactions)){
								
									$proceed = true;
								
								}

							}
							
							if($proceed){
								
								for($x=0;$x<count($interactions);$x++){
								
									if(isset($value[$interactions[$x]])){
										
										imageline($im, 50, $y_pos, (count($twitter_users)*2)+50, 50+$x, $red);
										
									}
									
								}
								
								foreach($value as $name => $value){
								
									if(!in_array($name, $interactions)){
									
										imageline($im, 50, $y_pos, (count($twitter_users)*2)+50, (count($twitter_users)*2)+52, $white);
									
									}
								
								}
								
							}else{

								imageline($im, 50, $y_pos, (count($twitter_users)*2)+50, (count($twitter_users)*2)+54, $white);

							}
							
							$y_pos += 2;
							
						}
						
					}
					
				}
				
				$file_process->file_image_create("data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_flatgraph.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network_graph", "Tweet Network Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_flatgraph.jpg' />";

				return $output . "<p><a href='?tool=tweet_network_graph'>" . $this->language->translate("tools/tweet_network_graph", "Return to Tweet Network Graph Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_network_graph", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_network_graph", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_network_graph", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_network_graph", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_network_graph", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function tweets_network(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network_graph", "Tweet Network Graph for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$twitter_users = array();
				$twitter_connections = array();
				
				foreach($data as $tweet){
					
					$name = strtolower($tweet->user->screen_name);
				
					if(!isset($twitter_connections[$name])){
							
						$twitter_connections[$name] = 0;
					
					}
					
					$twitter_connections[$name]++;
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->user_mentions)!=0){
						
							if(!isset($twitter_users[$name])){
								
								$twitter_users[$name] = array();
							
							}
							
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
				
				foreach($twitter_users as $key => $value){
				
					if(count($value)==0){
					
						unset($twitter_users[$key]);
					
					}
				
				}
				
				if(count($twitter_connections)<5000){
				
					$size = (count($twitter_connections)*2) + 100;
					$increment = 2;
					
				}else{
				
					$size = count($twitter_connections)+100;
					$increment = 1;
					
				}
				
				$font_size = 10.0;
				
				$im = imagecreatetruecolor($size,$size);
				
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				
				arsort($twitter_connections);
				
				$interactions = array_keys($twitter_connections);
				
				$y_pos = 50;
								
				foreach($twitter_connections as $key => $value){
				
					$name = $key;
					
					if(isset($twitter_users[$name])){
					
						$value = $twitter_users[$name];
					
						if(count($value)!=0){
						
							$names = array_keys($value);
							
							$proceed = false;
							
							foreach($names as $name){

								if(in_array($name,$interactions)){
								
									$proceed = true;
								
								}

							}
							
							if($proceed){
								
								for($x=0;$x<count($interactions);$x++){
								
									if(isset($value[$interactions[$x]])){
										
										imageline($im, 50, $y_pos, $size-50, 50+$x, $red);
										
									}
									
								}
								
								foreach($value as $name => $value){
								
									if(!in_array($name, $interactions)){
									
										imageline($im, 50, $y_pos, $size-50, $size-50, $white);
									
									}
								
								}
								
							}else{

								imageline($im, 50, $y_pos, $size-50, ($size)-50, $white);

							}
							
							$y_pos += $increment;
							
						}
					
					}
					
				}
				
				$file_process->file_image_create("data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_flatgraph_tweets.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_network_graph", "Tweet Network Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/network/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_flatgraph_tweets.jpg' />";

				return $output . "<p><a href='?tool=tweet_network_graph'>" . $this->language->translate("tools/tweet_network_graph", "Return to Tweet Network Graph Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_network_graph", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_network_graph", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_network_graph", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_network_graph", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_network_graph", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}