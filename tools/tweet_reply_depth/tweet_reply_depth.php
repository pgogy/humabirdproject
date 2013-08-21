<?PHP

	class tweet_reply_depth extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_reply_depth";
			$classification->name = "Tweet Reply Depth";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_reply_depth/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_reply_depth/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_reply_depth", "Tweet Reply Depth Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_reply_depth&action=instructions'>" . $this->language->translate("tools/tweet_reply_depth", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_reply_depth&action=tweet_depth'>" . $this->language->translate("tools/tweet_reply_depth", "Display Tweet Depth") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_reply_depth", "help");
			
			return $output . "<p><a href='?tool=tweet_reply_depth'>" . $this->language->translate("tools/tweet_reply_depth", "Return to Tweet Fingerprint") . "</a></p>";
				
		}
		
		private function tweet_depth(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_reply_depth", "Tweet Reply Pie Chart for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
				
				$tweet_depth = array();
				$tweet_depth[0] = 0;
				
				$replies = array();
				
				foreach($data as $tweet){
				
					if(isset($tweet->id_str)){
					
						if($tweet->in_reply_to_status_id_str!=""){
						
							$replies[$tweet->id_str] = $tweet->in_reply_to_status_id_str;
							
						}else{
						
							$tweet_depth[0]++;
						
						}
						
					}
					
				}	
				
				$depth = 1;
				
				while(count($replies)!=0){
				
					$tweet_depth[$depth] = count($replies);
				
					$replies = array_unique($replies);
					$keys = array_values($replies);
				
					$new_replies = array();
				
					foreach($keys as $id => $value){
					
						if(isset($replies[$value])){
					
							$new_replies[$value] = $replies[$value];
						
						}
					
					}
					
					$depth++;
				
					$replies = $new_replies;
					
				}
				
				$degree_ratio = 360 / array_sum($tweet_depth);
				
				$im = imagecreatetruecolor(800,625 + count($tweet_depth)*40);
				$white = imagecolorallocate($im, 255,255,255);
				$last_angle = 0;
				$speakers_y = 650;
				
				$other_colour = imagecolorallocate($im, 255,255,255);
				$other_angle = 0;
				
				foreach($tweet_depth as $key => $data){
				
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
								
				$file_process->file_image_create("data/twitter_harvest/files/piechart/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_reply_depth_pie.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_reply_depth", "Tweet Reply Depth Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/piechart/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_reply_depth_pie.jpg' />";

				return $output . "<p><a href='?tool=tweet_reply_depth'>" . $this->language->translate("tools/tweet_reply_depth", "Return to Tweet Reply Depth") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_reply_depth", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_reply_depth", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_reply_depth", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_reply_depth", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_reply_depth", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}