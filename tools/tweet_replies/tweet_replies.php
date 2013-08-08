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

				$output = "<h2>" . $this->language->translate("tools/tweet_replies", "Tweet Replies") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_replies&action=instructions'>" . $this->language->translate("tools/tweet_replies", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_replies&action=tweet_replies'>" . $this->language->translate("tools/tweet_replies", "Display Tweet Replies") . "</a>
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
		
		private function tweet_replies(){
		
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
				
				$tweet_replies = 0;
				$tweet_nonreplies = 0;
				
				$replies = array();
				
				foreach($data as $tweet){
				
					if(isset($tweet->id_str)){
					
						if($tweet->in_reply_to_status_id_str!=""){
						
							$tweet_replies++;
							
						}else{
						
							$tweet_nonreplies++;
						
						}
						
					}
					
				}	
				
				$depth = 1;
				
				$degree_ratio = 360 / ($tweet_replies + $tweet_nonreplies);
				
				$im = imagecreatetruecolor(800,625 + ($tweet_replies + $tweet_nonreplies)*40);
				$white = imagecolorallocate($im, 255,255,255);
				$red = imagecolorallocate($im, 255,0,0);
				$last_angle = 90.0;
				$speakers_y = 650;
				
				$red = imagecolorallocate($im, 255,0,0);
				$other_angle = 0;
				
				imagefilledarc ($im , 400 , 400 , 450 , 450 , $last_angle , $last_angle+($tweet_replies*$degree_ratio) , $white, IMG_ARC_PIE );
				imagefilledrectangle($im, 170, $speakers_y-15 , 190, $speakers_y , $white );
				imagettftext ( $im , 15.0 , 0 , 200 , $speakers_y , $white , "core/misc/fonts/arial.ttf" , $tweet_replies . " "  . $this->language->translate("tools/tweet_replies", " tweets - replies"));
				$speakers_y += 18;
						
				$last_angle += ($tweet_replies*$degree_ratio);
					
				imagefilledarc ($im , 400 , 400 , 450 , 450 , $last_angle , $last_angle+($tweet_nonreplies*$degree_ratio) , $red, IMG_ARC_PIE );
				imagefilledrectangle($im, 170, $speakers_y-15 , 190, $speakers_y , $red );
				imagettftext ( $im , 15.0 , 0 , 200 , $speakers_y , $white , "core/misc/fonts/arial.ttf" , $tweet_nonreplies . " "  . $this->language->translate("tools/tweet_replies", " tweets"));
				$speakers_y += 18;
						
				$file_process->file_image_create("data/twitter_harvest/files/piechart/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_reply_depth_pie.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_replies", "Tweet Reply Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/piechart/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_reply_depth_pie.jpg' />";

				return $output . "<p><a href='?tool=tweet_reply_depth'>" . $this->language->translate("tools/tweet_replies", "Return to Tweet Reply") . "</a></p>";
				
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