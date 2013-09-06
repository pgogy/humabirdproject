<?PHP

	class tweet_watch extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_watch";
			$classification->name = "Tweet Watch";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_watch/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_watch/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_watch", "Tweet Watch Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_watch&action=instructions'>" . $this->language->translate("tools/tweet_watch", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_watch&action=alltweets'>" . $this->language->translate("tools/tweet_watch", "Display Tweet Watch") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_watch", "help");
			
			return $output . "<p><a href='?tool=tweet_watch'>" . $this->language->translate("tools/tweet_watch", "Return to Tweet Network display") . "</a></p>";
				
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
				
				$output = "<h2>" . $this->language->translate("tools/tweet_watch", "Tweet Watch Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
		
				foreach($data as $tweet){
				
					if($tweet->in_reply_to_status_id_str!=""){
						
						$tweets[$tweet->id_str] = $tweet->in_reply_to_status_id_str;
					
					}else{
				
						$tweets[$tweet->id_str]  = 0;
						
					}
					
				}
				
				$ratio = 360 / (count($tweets));				
				
				$users = max(count($tweets), 200);				
				$size = max(count($tweets), 1200);
				$width = max(count($tweets), 1200);
				
				$width = min(10000, $width);
				$size = min(12000, $size);
				
				$im = imagecreatetruecolor($size * 1.2, $size * 1.2);
				$white = imagecolorallocate($im, 255,255,255);
				$grey = imagecolorallocate($im, 200,200,200);
				$red = imagecolorallocate($im, 255,0,0);
				
				$clock_point = 0;	
				
				$interactions = array_keys($tweets);
				
				foreach($tweets as $key => $value){
				
					if($value!=0){
				
						$y_pos = (($size*1.1) / 2) + (integer)round( ($width / 2) * sin( deg2rad($clock_point) ) );
						$x_pos = (($size*1.1) / 2) + (integer)round( ($width / 2) * cos( deg2rad($clock_point) ) );
						
						$mark_y_pos = (($size*1.1) / 2) + (integer)round( (($width+20/2) - ($width/2)) * sin( deg2rad($clock_point) ) );
						$mark_x_pos = (($size*1.1) / 2) + (integer)round( (($width+20/2) - ($width/2)) * cos( deg2rad($clock_point) ) );
						
						for($x=0;$x<count($interactions);$x++){
						
							if($tweets[$interactions[$x]]==$key){
								
								$line_y_pos = (($size*1.1) / 2) + (integer)round( ($width / 2) * sin( deg2rad($ratio * $x) ) );
								$line_x_pos = (($size*1.1) / 2) + (integer)round( ($width / 2) * cos( deg2rad($ratio * $x) ) );
											
								imageline($im, $x_pos, $y_pos, $line_x_pos, $line_y_pos, $red);
								imageline($im, $x_pos, $y_pos, $mark_x_pos, $mark_y_pos, $white);
								
								break;
									
							}
								
						}
						
					}else{
					
						$y_pos = (($size*1.1) / 2) + (integer)round( ($width / 2) * sin( deg2rad($clock_point) ) );
						$x_pos = (($size*1.1) / 2) + (integer)round( ($width / 2) * cos( deg2rad($clock_point) ) );
						
						$mark_y_pos = (($size*1.1) / 2) + (integer)round( (($width+20/2) - ($width/2)) * sin( deg2rad($clock_point) ) );
						$mark_x_pos = (($size*1.1) / 2) + (integer)round( (($width+20/2) - ($width/2)) * cos( deg2rad($clock_point) ) );
						
						imageline($im, $x_pos, $y_pos, $mark_x_pos, $mark_y_pos, $red);						
					
					}
				
					$clock_point+=$ratio;
				
				}	
				
				$file_process->file_image_create("data/twitter_harvest/files/watch/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_watch.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_watch", "Tweet Watch Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/watch/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_watch.jpg' />";

				return $output . "<p><a href='?tool=tweet_watch'>" . $this->language->translate("tools/tweet_watch", "Return to Tweet Watch Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_watch", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_watch", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_watch", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_watch", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_watch", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}