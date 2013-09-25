<?PHP

	class tweet_concordancer extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_concordancer";
			$classification->name = "Tweet Concordancer";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_concordancer/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_concordancer/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_concordancer", "Tweet Concordancer") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_concordancer&action=instructions'>" . $this->language->translate("tools/tweet_concordancer", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_concordancer&action=display'>" . $this->language->translate("tools/tweet_concordancer", "Search tweets") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_concordancer", "help");
			
			return $output . "<p><a href='?tool=tweet_concordancer'>" . $this->language->translate("tools/tweet_concordancer", "Return to Tweet Concordancer") . "</a></p>";
				
		}
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_concordancer", "Tweet Concordancer for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				foreach($data as $tweet){
				
					if(strpos($tweet->text, $_POST['search_term'])!==FALSE){
				
						$output .= "<p>" . str_replace($_POST['search_term'],"<strong>" . $_POST['search_term'] . "</strong>", $tweet->text) . "</p>";
				
					}
				
				}
				
				return $output . "<p><a href='?tool=tweet_concordancer'>" . $this->language->translate("tools/tweet_concordancer", "Return to Tweet Concordancer") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_concordancer", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_concordancer", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_concordancer", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<label>" . $this->language->translate("tools/tweet_concordancer", "Search Term") . "</label>
									<input type='text' name='search_term' />
									<input type='submit' value='" . $this->language->translate("tools/tweet_concordancer", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_concordancer", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
	}