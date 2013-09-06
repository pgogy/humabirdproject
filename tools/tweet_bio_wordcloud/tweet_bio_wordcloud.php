<?PHP

	class tweet_bio_wordcloud extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_bio_wordcloud";
			$classification->name = "Tweet Bio Word Cloud";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_bio_wordcloud/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_bio_wordcloud/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_bio_wordcloud", "Tweet Bio Word Cloud Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_bio_wordcloud&action=instructions'>" . $this->language->translate("tools/tweet_bio_wordcloud", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_bio_wordcloud&action=wordoccurrence'>" . $this->language->translate("tools/tweet_bio_wordcloud", "Display Word Occurrences") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_bio_wordcloud&action=wordoccurrence_unique'>" . $this->language->translate("tools/tweet_bio_wordcloud", "Display Word Occurrences (unique users)") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_bio_wordcloud", "help");
			
			return $output . "<p><a href='?tool=tweet_bio_wordcloud'>" . $this->language->translate("tools/tweet_bio_wordcloud", "Return to Tweet Bio Word cloud dispkay ") . "</a></p>";
				
		}
		
		private function wordoccurrence(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_bio_wordcloud", "Tweet Bio Word Cloud Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$output .= "<p>" . count($data) . " " . $this->language->translate("tools/tweet_bio_wordcloud", "Tweets") . " : " . $_POST['tweetfile'] . "</p>";
				
				$word_list = array();
				
				foreach($data as $tweet){
				
					$words = explode(" ", $tweet->user->description);
					
					foreach($words as $word){
					
						if(strpos($word,"http")!==0){
					
							if(substr($word,0,1)!=="@"&&$word!=""){
							
								if(isset($word_list[$word])){
								
									$word_list[$word]++;
								
								}else{
								
									$word_list[$word] = 1;
								
								}
							
							}
							
						}
					
					}						
				
				}
				
				arsort($word_list);
				
				$output .= "<h2>" . $this->language->translate("tools/tweet_bio_wordcloud", "Words") . "</h2>";
				
				foreach($word_list as $word => $total){
				
					$output .= "<p>" . $word . " : " . $total . "</p>";
				
				}
						
				return $output . "<p><a href='?tool=tweet_bio_wordcloud'>" . $this->language->translate("tools/tweet_bio_wordcloud", "Return to Tweet Bio Word Cloud Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_bio_wordcloud", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_bio_wordcloud", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_bio_wordcloud", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_bio_wordcloud", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_bio_wordcloud", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		private function wordoccurrence_unique(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_bio_wordcloud", "Tweet Bio Word Cloud Display for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$output .= "<p>" . count($data) . " " . $this->language->translate("tools/tweet_bio_wordcloud", "Tweets") . " : " . $_POST['tweetfile'] . "</p>";
				
				$word_list = array();
				
				$users = array();
				
				foreach($data as $tweet){
				
					if(!in_array($tweet->user->id,$users)){
					
						array_push($users,$tweet->user->id);
					
						$words = explode(" ", $tweet->user->description);
						
						foreach($words as $word){
						
							if(strpos($word,"http")!==0){
						
								if(substr($word,0,1)!=="@"&&$word!=""){
								
									if(isset($word_list[$word])){
									
										$word_list[$word]++;
									
									}else{
									
										$word_list[$word] = 1;
									
									}
								
								}
								
							}
						
						}						
					
					}
				
				}
					
				arsort($word_list);
				
				$output .= "<h2>" . $this->language->translate("tools/tweet_bio_wordcloud", "Words") . "</h2>";
				
				foreach($word_list as $word => $total){
				
					$output .= "<p>" . $word . " : " . $total . "</p>";
				
				}
						
				return $output . "<p><a href='?tool=tweet_bio_wordcloud'>" . $this->language->translate("tools/tweet_bio_wordcloud", "Return to Tweet Bio Word Cloud Display") . "</a></p>";
	
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_bio_wordcloud", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_bio_wordcloud", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_bio_wordcloud", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_bio_wordcloud", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_bio_wordcloud", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
		
		
	}