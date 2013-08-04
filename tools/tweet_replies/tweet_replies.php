<?PHP

	class tweet_replies extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_replies";
			$classification->name = "Tweet Replies";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_replies/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_replies/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_replies", "Tweet Fingerprint Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_replies&action=instructions'>" . $this->language->translate("tools/tweet_replies", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_replies&action=alltweets'>" . $this->language->translate("tools/tweet_replies", "Display Tweet Fingerprint (all)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_replies&action=tweet_contrib'>" . $this->language->translate("tools/tweet_replies", "Display Tweet Fingerprint (users with over X tweets)") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_replies", "help");
			
			return $output . "<p><a href='?tool=tweet_replies'>" . $this->language->translate("tools/tweet_replies", "Return to Tweet Fingerprint") . "</a></p>";
				
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
				
				$output = "<h2>" . $this->language->translate("tools/tweet_replies", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
				$twitter_users = array();
				
				foreach($data as $tweet){
				
					$tweets[] = $tweet->user->screen_name;
					
					if(!in_array($tweet->user->screen_name, array_keys($twitter_users))){
								
						$twitter_users[$tweet->user->screen_name] = count($twitter_users)+1; 
					
					}
					
				}
				
				$im = imagecreatetruecolor(count($tweets)+200, (count($twitter_users)*12) + 50);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				$x_pos = 150;
				$y_pos = 0;
				
				foreach($tweets as $tweet){
							
					imageline($im, $x_pos, $y_pos + (($twitter_users[$tweet]-1)*12), $x_pos, $y_pos + ((($twitter_users[$tweet]-1)*12) + 10), imagecolorallocate($im, (integer)(255),0,0));
					imagettftext ( $im , 10.0 , 0, 5 , $y_pos + ($twitter_users[$tweet]*12) , $white , "core/misc/fonts/arial.ttf" , $tweet);
					
					$x_pos += 1;
				
				}	
				
				$file_process->file_image_create("data/twitter_harvest/files/fingerprint/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_all.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_replies", "Tweet Fingerprint Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/fingerprint/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_all.jpg' />";

				return $output . "<p><a href='?tool=tweet_replies'>" . $this->language->translate("tools/tweet_replies", "Return to Tweet Fingerprint Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_replies", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_replies", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_replies", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									  <input type='submit' value='" . $this->language->translate("tools/tweet_replies", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_replies", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
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
				
				$output = "<h2>" . $this->language->translate("tools/tweet_replies", "Tweet Reply Pie Chart for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
				
				$replies = 0;
				$nonreplies = 0;
				
				foreach($data as $tweet){
				
					if(isset($tweet->in_reply_to_user_id_str)!=""){
					
						$replies++;
					
					}else{
					
						$nonreplies++;
					
					}
					
				}	
				
				$ratio = 360 / ($replies + $nonreplies);
				
				$im = imagecreatetruecolor(600,600);
				$white = imagecolorallocate($im, 255,255,255);				
				$colour = imagecolorallocate($im, rand(10,240),rand(10,240),rand(10,240));

				imagefilledarc ($im , 300 , 300 , 250 , 250 , 0 , 0 + ($ratio * $replies) , $white, IMG_ARC_PIE );
				imagefilledrectangle($im, 170, 485 , 190, 500 , $white );
				imagettftext ( $im , 15.0 , 0 , 200 , 500 , $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_replies", "Replies") . " (" . $replies . ")" );
								
				imagefilledarc ($im , 300 , 300 , 250 , 250 , ($ratio * $replies) , 360, $colour, IMG_ARC_PIE );
				imagefilledrectangle($im, 170, 505 , 190, 520 , $colour );
				imagettftext ( $im , 15.0 , 0 , 200 , 520 , $white , "core/misc/fonts/arial.ttf" , $this->language->translate("tools/tweet_replies", "Originals") . " (" . $nonreplies . ")");	
				
				$file_process->file_image_create("data/twitter_harvest/files/piechart/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_reply_pie.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_replies", "Tweet Fingerprint Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/piechart/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_reply_pie.jpg' />";

				return $output . "<p><a href='?tool=tweet_replies'>" . $this->language->translate("tools/tweet_replies", "Return to Tweet Reply") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_replies", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_replies", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_replies", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_replies", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_replies", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}