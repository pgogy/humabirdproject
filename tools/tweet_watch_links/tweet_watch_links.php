<?PHP

	class tweet_watch_links extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_watch_links";
			$classification->name = "Tweet Watch for Links";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_watch_links/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_watch_links/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_watch_links", "Tweet Network Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_watch_links&action=instructions'>" . $this->language->translate("tools/tweet_watch_links", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_watch_links&action=alltweets'>" . $this->language->translate("tools/tweet_watch_links", "Display Tweet Watch with links") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_watch_links", "help");
			
			return $output . "<p><a href='?tool=tweet_watch_links'>" . $this->language->translate("tools/tweet_watch_links", "Return to Tweet Network display") . "</a></p>";
				
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
				
				$output = "<h2>" . $this->language->translate("tools/tweet_watch_links", "Tweet Watch Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
				$urls = array();
		
				foreach($data as $tweet){
				
					if(isset($tweet->entities)){
				
						if(count($tweet->entities->urls)!=0){
						
							$tweets[] = 1;
							
							foreach($tweet->entities->urls as $url){
							
								if(isset($urls[$url->expanded_url])){
								
									$urls[$url->expanded_url]++;
								
								}else{
								
									$urls[$url->expanded_url]=1;
									
								}
								
							}
						
						}else{
						
							$tweets[] = 0;
						
						}
				
					}
					
				}
				
				$ratio = 360 / (count($tweets));				
				
				$users = max(count($tweets), 200);				
				$size = max(count($tweets)*1.1, 1200);
				$width = max(count($tweets), 1200);
				
				$im = imagecreatetruecolor($size, $size + 50 + (count($urls) * 20));
				$white = imagecolorallocate($im, 255,255,255);
				$grey = imagecolorallocate($im, 200,200,200);
				$red = imagecolorallocate($im, 255,0,0);
				
				$clock_point = 0;	
				
				$interactions = array_keys($tweets);
				
				foreach($tweets as $key => $value){
				
					if($value!=0){
				
						$y_pos = ($size / 2) + (integer)round( (($width-20) / 2) * sin( deg2rad($clock_point) ) );
						$x_pos = ($size / 2) + (integer)round( (($width-20) / 2) * cos( deg2rad($clock_point) ) );
						
						imageline($im, $size/2, $size/2, $x_pos, $y_pos, $white);
						
					}else{
					
						$y_pos = ($size / 2) + (integer)round( (($width-40) / 2) * sin( deg2rad($clock_point) ) );
						$x_pos = ($size / 2) + (integer)round( (($width-40) / 2) * cos( deg2rad($clock_point) ) );
						
						imageline($im, $size/2, $size/2, $x_pos, $y_pos, $red);						
					
					}
				
					$clock_point+=$ratio;
				
				}	
				
				$y_pos = $size+50;
				
				arsort($urls);
				
				foreach($urls as $url => $share){
				
					if($share==1){
				
						imagettftext ( $im , 15.0 , 0, 200 , $y_pos , $white , "core/misc/fonts/arial.ttf" , $url . " : " . $share . " " . $this->language->translate("tools/tweet_watch_links", "share"));
					
					}else{
					
						imagettftext ( $im , 15.0 , 0, 200 , $y_pos , $white , "core/misc/fonts/arial.ttf" , $url . " : " . $share . " " . $this->language->translate("tools/tweet_watch_links", "shares"));
					
					}
					
					$y_pos +=20;
				
				}
				
				$file_process->file_image_create("data/twitter_harvest/files/links_watch/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_linkswatch.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_watch_links", "Tweet Watch for Links Display") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/links_watch/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_linkswatch.jpg' />";

				return $output . "<p><a href='?tool=tweet_watch_links'>" . $this->language->translate("tools/tweet_watch_links", "Return to Tweet Watch for links Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_watch_links", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_watch_links", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_watch_links", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_watch_links", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_watch_links", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}