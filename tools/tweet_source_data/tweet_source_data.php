<?PHP

	class tweet_source_data extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_source_data";
			$classification->name = "Tweet Source Data";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_source_data/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_source_data/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_source_data", "Tweet Network Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_source_data&action=instructions'>" . $this->language->translate("tools/tweet_source_data", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_source_data&action=sources_and_tweets'>" . $this->language->translate("tools/tweet_source_data", "Display sources and tweets per account") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_source_data&action=sources_per_account'>" . $this->language->translate("tools/tweet_source_data", "Display sources per account") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_source_data&action=sources_used_overall'>" . $this->language->translate("tools/tweet_source_data", "Display sources used overall") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_source_data", "help");
			
			return $output . "<p><a href='?tool=tweet_source_data'>" . $this->language->translate("tools/tweet_source_data", "Return to Tweet Network display") . "</a></p>";
				
		}
		
		private function sources_and_tweets(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_source_data", "Tweet Source Data for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
				$sources = array();
		
				foreach($data as $tweet){
				
					if(isset($tweet->source)){
					
						if(!isset($sources[$tweet->user->screen_name])){
						
							$sources[$tweet->user->screen_name] = array();
							
						}							
				
						array_push($sources[$tweet->user->screen_name], $tweet->source . " " . $tweet->text);
				
					}
					
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_source_data", "Tweet Source Data Display") . "</h2>";
				
				foreach($sources as $link => $data){
				
					$output .= "<h3>" . $link . "</h3>";
					foreach($data as $tweet){
					
						$output .= "<p>" . $tweet . "</p>";
					
					}
				
				}
				
				return $output . "<p><a href='?tool=tweet_source_data'>" . $this->language->translate("tools/tweet_source_data", "Return to Tweet Source Data Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_source_data", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_source_data", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_source_data", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_source_data", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_source_data", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function sources_per_account(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_source_data", "Tweet Source Data for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
				$sources = array();
		
				foreach($data as $tweet){
				
					if(isset($tweet->source)){
					
						if(!isset($sources[$tweet->user->screen_name])){
						
							$sources[$tweet->user->screen_name] = array();
							
						}

						if(!isset($sources[$tweet->user->screen_name][$tweet->source])){
						
							$sources[$tweet->user->screen_name][$tweet->source] = 0;
							
						}							
				
						$sources[$tweet->user->screen_name][$tweet->source]++;
				
					}
					
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_source_data", "Tweet Source Data Display") . "</h2>";
				
				foreach($sources as $link => $data){
				
					$output .= "<h3>" . $link . "</h3>";
					foreach($data as $client => $times){
					
						$output .= "<p>" . $this->language->translate("tools/tweet_source_data", "Client") . " : " . $client . " " . $times . "</p>";
					
					}
				
				}
				
				return $output . "<p><a href='?tool=tweet_source_data'>" . $this->language->translate("tools/tweet_source_data", "Return to Tweet Source Data Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_source_data", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_source_data", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_source_data", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_source_data", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_source_data", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function sources_used_overall(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_source_data", "Tweet Source Data for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets = array();
				$sources = array();
		
				foreach($data as $tweet){
				
					if(isset($tweet->source)){

						if(!isset($sources[$tweet->source])){
						
							$sources[$tweet->source] = 0;
							
						}							
				
						$sources[$tweet->source]++;
				
					}
					
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_source_data", "Tweet Source Data Display") . "</h2>";
				
				arsort($sources);
				
				foreach($sources as $link => $data){
				
					$output .= "<p>" . $this->language->translate("tools/tweet_source_data", "Client") . " : " . $link . " " . $data . " " . $this->language->translate("tools/tweet_source_data", " tweets") . "</p>";
				
				}
				
				return $output . "<p><a href='?tool=tweet_source_data'>" . $this->language->translate("tools/tweet_source_data", "Return to Tweet Source Data Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_source_data", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_source_data", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_source_data", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/tweet_source_data", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_source_data", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		
		}
		
		
	}