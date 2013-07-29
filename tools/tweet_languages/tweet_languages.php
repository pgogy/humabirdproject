<?PHP

	class tweet_languages extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_languages";
			$classification->name = "Tweet Languages";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_languages/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_languages/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_languages", "Tweet Language Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_languages&action=instructions'>" . $this->language->translate("tools/tweet_languages", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_languages&action=languages'>" . $this->language->translate("tools/tweet_languages", "Display tweet language") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_languages", "help");
			
			return $output . "<p><a href='?tool=tweet_languages'>" . $this->language->translate("tools/tweet_languages", "Return to Tweet Languages display") . "</a></p>";
				
		}
			
		private function languages(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_languages", "Tweet Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$languages = array();
				
				foreach($data as $tweet){
				
					if(isset($languages[$tweet->metadata->iso_language_code])){
					
						$languages[$tweet->metadata->iso_language_code]++;
					
					}else{
					
						$languages[$tweet->metadata->iso_language_code]=1;
					
					}
				
				}
				
				$output = "<h3>" . $this->language->translate("tools/tweet_languages", "Languages") . "</h3>";
				
				arsort($languages);
				
				foreach($languages as $language => $count){
				
					$output .= "<p>" . $language . " : " . $count . "</p>";
				
				}
				
				return $output . "<p><a href='?tool=tweet_languages'>" . $this->language->translate("tools/tweet_languages", "Return to Tweet Languages") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_languages", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_languages", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_languages", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_languages", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_languages", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}