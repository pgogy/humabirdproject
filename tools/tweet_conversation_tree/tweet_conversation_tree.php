<?PHP

	class tweet_conversation_tree extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=tweet_conversation_tree";
			$classification->name = "Tweet Conversation Tree";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/tweet_conversation_tree/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/tweet_conversation_tree/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/tweet_conversation_tree", "Tweet Conversation Tree Display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=tweet_conversation_tree&action=instructions'>" . $this->language->translate("tools/tweet_conversation_tree", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_conversation_tree&action=conversation_depth'>" . $this->language->translate("tools/tweet_conversation_tree", "Display Conversation Tree") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_conversation_tree&action=conversation_depth_user'>" . $this->language->translate("tools/tweet_conversation_tree", "Display Conversation Tree (user name)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_conversation_tree&action=conversation_depth_user_new'>" . $this->language->translate("tools/tweet_conversation_tree", "Display Conversation Tree (user name and new user highlight)") . "</a>
								</li>
								<li>
									<a href='?tool=tweet_conversation_tree&action=conversation_depth_users_involved'>" . $this->language->translate("tools/tweet_conversation_tree", "Display Conversation Tree (users involved)") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/tweet_conversation_tree", "help");
			
			return $output . "<p><a href='?tool=tweet_conversation_tree'>" . $this->language->translate("tools/tweet_conversation_tree", "Return to Tweet Fingerprint") . "</a></p>";
				
		}
		
		private function conversation_depth(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_conversation_tree", "Tweet Reply Pie Chart for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets_no_replies = array();
				
				$tweet_depth = array();
				$tweet_depth[0] = 0;
				
				$tweets = array();
				
				$data = array_reverse($data);
				
				$lookup = array();
				
				$tweet_count = 0;

				$tweet_id = array();
				
				foreach($data as $tweet){
					
					if(isset($tweet->in_reply_to_status_id_str)){
					
						if(!isset($tweet_id[$tweet->in_reply_to_status_id_str])){
						
							$tweet_id[$tweet->in_reply_to_status_id_str] = "";
						
						}
						
					}
					
					$tweet_id[$tweet->id_str] = $tweet->in_reply_to_status_id_str;
				
				}
				
				ksort($tweets);
				
				foreach($tweet_id as $tweet => $tweet_reply){
					
					if(trim($tweet_reply)==""){
					
						$tweets[$tweet] = array();
						
						$lookup[$tweet] = & $tweets[$tweet];
					
						$tweet_count ++;
					
					}else{
						
						$lookup[$tweet_reply][$tweet] = array();
							
						$lookup[$tweet] = & $lookup[$tweet_reply][$tweet];
							
						$tweet_count ++;
							
					}
				
				}
				
				ksort($tweets);
				
				function children($data, &$output, $depth){
			
					foreach($data as $id => $thread){
					
						$output[] = $depth;
						children($thread, $output, $depth + 1);
					
					}
				
				}
				
				$output = array();
				
				foreach($tweets as $id => $thread){
				
					$output[]=1;
					
					children($thread, $output, 2);
				
				}
				
				$data = array_count_values ($output);
				
				$max = max($output);
				
				$im = imagecreatetruecolor(($max*5) + 50, ((count($output) + $data[1]) *5) + 50);
				$white = imagecolorallocate($im, 255,255,255);
				$x_pos = 25;
				$y_pos = 20;
				
				foreach($output as $x_diff){
				
					if($x_diff==1){
					
						$y_pos+=5;
					
					}
				
					imagefilledrectangle($im, ($x_diff*5)+$x_pos, $y_pos , ($x_diff*5)+5+$x_pos, $y_pos+5 , $white );
					
					$y_pos+=5;
					
				}	
								
				$file_process->file_image_create("data/twitter_harvest/files/conversation_tree/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_conversation_tree.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_conversation_tree", "Tweet Conversation Tree") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/conversation_tree/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_conversation_tree.jpg' />";

				return $output . "<p><a href='?tool=tweet_conversation_tree'>" . $this->language->translate("tools/tweet_conversation_tree", "Return to Tweet Conversation Tree") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_conversation_tree", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_conversation_tree", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_conversation_tree", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_conversation_tree", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_conversation_tree", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
	
		private function conversation_depth_user(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_conversation_tree", "Tweet Reply Pie Chart for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets_no_replies = array();
				
				$tweet_depth = array();
				$tweet_depth[0] = 0;
				
				$tweets = array();
				
				$data = array_reverse($data);
				
				$lookup = array();
				
				$tweet_count = 0;

				$tweet_id = array();
				$user_tweet_id = array();
				
				foreach($data as $tweet){
					
					if(isset($tweet->in_reply_to_status_id_str)){
					
						if(!isset($tweet_id[$tweet->in_reply_to_status_id_str])){
						
							$tweet_id[$tweet->in_reply_to_status_id_str] = "";
						
						}
						
					}
					
					$tweet_id[$tweet->id_str] = $tweet->in_reply_to_status_id_str;
				
					$user_tweet_id[$tweet->id_str] = $tweet->user->screen_name;
				
				}
				
				ksort($tweets);
				
				foreach($tweet_id as $tweet => $tweet_reply){
					
					if(trim($tweet_reply)==""){
					
						$tweets[$tweet] = array();
						
						$lookup[$tweet] = & $tweets[$tweet];
					
						$tweet_count ++;
					
					}else{
						
						$lookup[$tweet_reply][$tweet] = array();
							
						$lookup[$tweet] = & $lookup[$tweet_reply][$tweet];
							
						$tweet_count ++;
							
					}
				
				}
				
				ksort($tweets);
				
				function children($data, &$output, &$user_output, $depth){
			
					foreach($data as $id => $thread){
					
						$output[] = $depth;
						$user_output[] = $id;
						children($thread, $output, $user_output, $depth + 1);
					
					}
				
				}
				
				$output = array();
				
				$user_output = array();
				
				foreach($tweets as $id => $thread){
				
					$output[]=1;
					
					$user_output[] = $id;
					
					children($thread, $output, $user_output, 2);
				
				}
				
				$data = array_count_values ($output);
				
				$max = max($output);
				
				$im = imagecreatetruecolor(($max*15) + 350, ((count($output) + $data[1]) *15) + 50);
				$white = imagecolorallocate($im, 255,255,255);
				$black = imagecolorallocate($im, 0,0,0);
				$x_pos = 25;
				$y_pos = 20;
				
				$users = array();
				$users["?"] = 1;
				
				foreach($output as $x_diff){
				
					if($x_diff==1){
					
						$y_pos+=15;
					
					}
				
					imagefilledrectangle($im, ($x_diff*15)+$x_pos, $y_pos , ($x_diff*15)+15+$x_pos, $y_pos+15 , $white );
					
					$data = array_shift($user_output);
					
					if(isset($user_tweet_id[$data])){
					
						if(isset($users[$user_tweet_id[$data]])){
						
							imagettftext ( $im , 10.0 , 0 , ($x_diff*15)+$x_pos+2 , $y_pos+13 , $black , "core/misc/fonts/arial.ttf" , $users[$user_tweet_id[$data]]);						
						
						}else{
						
							$users[$user_tweet_id[$data]] = count($users)+1;
						
							imagettftext ( $im , 10.0 , 0 , ($x_diff*15)+$x_pos+2 , $y_pos+13 , $black , "core/misc/fonts/arial.ttf" , $users[$user_tweet_id[$data]]);
					
						}
					
					}else{
					
						imagettftext ( $im , 10.0 , 0 , ($x_diff*15)+$x_pos+2 , $y_pos+13 , $black , "core/misc/fonts/arial.ttf" , 1);
					
					}
					
					$y_pos+=15;
					
				}

				$x_pos = 200;
				$y_pos = 45;
				
				foreach($users as $user => $id){

					imagettftext ( $im , 10.0 , 0 , $x_pos , $y_pos , $white , "core/misc/fonts/arial.ttf" , $id . ") " . $user);
					$y_pos +=15;

				}
								
				$file_process->file_image_create("data/twitter_harvest/files/conversation_tree/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_conversation_tree_user.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_conversation_tree", "Tweet Conversation Tree") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/conversation_tree/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_conversation_tree_user.jpg' />";

				return $output . "<p><a href='?tool=tweet_conversation_tree'>" . $this->language->translate("tools/tweet_conversation_tree", "Return to Tweet Conversation Tree") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_conversation_tree", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_conversation_tree", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_conversation_tree", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_conversation_tree", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_conversation_tree", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
	
		private function conversation_depth_user_new(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_conversation_tree", "Tweet Reply Pie Chart for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets_no_replies = array();
				
				$tweet_depth = array();
				$tweet_depth[0] = 0;
				
				$tweets = array();
				
				$data = array_reverse($data);
				
				$lookup = array();
				
				$tweet_count = 0;

				$tweet_id = array();
				$user_tweet_id = array();
				
				foreach($data as $tweet){
					
					if(isset($tweet->in_reply_to_status_id_str)){
					
						if(!isset($tweet_id[$tweet->in_reply_to_status_id_str])){
						
							$tweet_id[$tweet->in_reply_to_status_id_str] = "";
						
						}
						
					}
					
					$tweet_id[$tweet->id_str] = $tweet->in_reply_to_status_id_str;
				
					$user_tweet_id[$tweet->id_str] = $tweet->user->screen_name;
				
				}
				
				ksort($tweets);
				
				foreach($tweet_id as $tweet => $tweet_reply){
					
					if(trim($tweet_reply)==""){
					
						$tweets[$tweet] = array();
						
						$lookup[$tweet] = & $tweets[$tweet];
					
						$tweet_count ++;
					
					}else{
						
						$lookup[$tweet_reply][$tweet] = array();
							
						$lookup[$tweet] = & $lookup[$tweet_reply][$tweet];
							
						$tweet_count ++;
							
					}
				
				}
				
				ksort($tweets);
				
				function children($data, &$output, &$user_output, $depth){
			
					foreach($data as $id => $thread){
					
						$output[] = $depth;
						$user_output[] = $id;
						children($thread, $output, $user_output, $depth + 1);
					
					}
				
				}
				
				$output = array();
				
				$user_output = array();
				
				foreach($tweets as $id => $thread){
				
					$output[]=1;
					
					$user_output[] = $id;
					
					children($thread, $output, $user_output, 2);
				
				}
				
				$data = array_count_values ($output);
				
				$max = max($output);
				
				$im = imagecreatetruecolor(($max*15) + 350, ((count($output) + $data[1]) *15) + 50);
				$white = imagecolorallocate($im, 255,255,255);
				$black = imagecolorallocate($im, 0,0,0);
				$red = imagecolorallocate($im, 255,0,0);
				$x_pos = 25;
				$y_pos = 20;
				
				$users = array();
				$users["?"] = 1;
				
				foreach($output as $x_diff){
				
					if($x_diff==1){
					
						$y_pos+=15;
					
					}
				
					$data = array_shift($user_output);
					
					if(isset($user_tweet_id[$data])){
					
						if(isset($users[$user_tweet_id[$data]])){
						
							imagefilledrectangle($im, ($x_diff*15)+$x_pos, $y_pos , ($x_diff*15)+15+$x_pos, $y_pos+15 , $white );
						
							imagettftext ( $im , 10.0 , 0 , ($x_diff*15)+$x_pos+2 , $y_pos+13 , $black , "core/misc/fonts/arial.ttf" , $users[$user_tweet_id[$data]]);						
						
						}else{
						
							imagefilledrectangle($im, ($x_diff*15)+$x_pos, $y_pos , ($x_diff*15)+15+$x_pos, $y_pos+15 , $red );
					
							$users[$user_tweet_id[$data]] = count($users)+1;
						
							imagettftext ( $im , 10.0 , 0 , ($x_diff*15)+$x_pos+2 , $y_pos+13 , $black , "core/misc/fonts/arial.ttf" , $users[$user_tweet_id[$data]]);
					
						}
					
					}else{
					
						imagefilledrectangle($im, ($x_diff*15)+$x_pos, $y_pos , ($x_diff*15)+15+$x_pos, $y_pos+15 , $white );
					
						imagettftext ( $im , 10.0 , 0 , ($x_diff*15)+$x_pos+2 , $y_pos+13 , $black , "core/misc/fonts/arial.ttf" , 1);
					
					}
					
					$y_pos+=15;
					
				}

				$x_pos = 200;
				$y_pos = 45;
				
				foreach($users as $user => $id){

					imagettftext ( $im , 10.0 , 0 , $x_pos , $y_pos , $white , "core/misc/fonts/arial.ttf" , $id . ") " . $user);
					$y_pos +=15;

				}
								
				$file_process->file_image_create("data/twitter_harvest/files/conversation_tree/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_conversation_tree_user_new.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_conversation_tree", "Tweet Conversation Tree") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/conversation_tree/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_conversation_tree_user_new.jpg' />";

				return $output . "<p><a href='?tool=tweet_conversation_tree'>" . $this->language->translate("tools/tweet_conversation_tree", "Return to Tweet Conversation Tree") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_conversation_tree", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_conversation_tree", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_conversation_tree", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_conversation_tree", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_conversation_tree", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
	
		private function conversation_depth_users_involved(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile'])){
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/aggregate/" . $_POST['tweetfile']));
				
				}else{
				
					$data = unserialize($file_process->file_get_all("data/twitter_harvest/files/usertweets/" . $_POST['tweetfile']));
				
				}
				
				$output = "<h2>" . $this->language->translate("tools/tweet_conversation_tree", "Tweet Reply Pie Chart for file ") . " : " . $_POST['tweetfile'] . "</h2>";
				
				$tweets_no_replies = array();
				
				$tweet_depth = array();
				$tweet_depth[0] = 0;
				
				$tweets = array();
				
				$data = array_reverse($data);
				
				$lookup = array();
				
				$tweet_count = 0;

				$tweet_id = array();
				$user_tweet_id = array();
				
				foreach($data as $tweet){
					
					if(isset($tweet->in_reply_to_status_id_str)){
					
						if(!isset($tweet_id[$tweet->in_reply_to_status_id_str])){
						
							$tweet_id[$tweet->in_reply_to_status_id_str] = "";
						
						}
						
					}
					
					$tweet_id[$tweet->id_str] = $tweet->in_reply_to_status_id_str;
					
					$symbol = explode("@", $tweet->text);
				
					$user_tweet_id[$tweet->id_str] = count($symbol);
				
				}
				
				ksort($tweets);
				
				foreach($tweet_id as $tweet => $tweet_reply){
					
					if(trim($tweet_reply)==""){
					
						$tweets[$tweet] = array();
						
						$lookup[$tweet] = & $tweets[$tweet];
					
						$tweet_count ++;
					
					}else{
						
						$lookup[$tweet_reply][$tweet] = array();
							
						$lookup[$tweet] = & $lookup[$tweet_reply][$tweet];
							
						$tweet_count ++;
							
					}
				
				}
				
				ksort($tweets);
				
				function children($data, &$output, &$user_output, $depth){
			
					foreach($data as $id => $thread){
					
						$output[] = $depth;
						$user_output[] = $id;
						children($thread, $output, $user_output, $depth + 1);
					
					}
				
				}
				
				$output = array();
				
				$user_output = array();
				
				foreach($tweets as $id => $thread){
				
					$output[]=1;
					
					$user_output[] = $id;
					
					children($thread, $output, $user_output, 2);
				
				}
				
				$data = array_count_values ($output);
				
				$max = max($output);
				
				$im = imagecreatetruecolor(($max*15) + 50, ((count($output) + $data[1]) *15) + 50);
				$white = imagecolorallocate($im, 255,255,255);
				$black = imagecolorallocate($im, 0,0,0);
				$x_pos = 25;
				$y_pos = 20;
				
				foreach($output as $x_diff){
				
					if($x_diff==1){
					
						$y_pos+=15;
					
					}
				
					imagefilledrectangle($im, ($x_diff*15)+$x_pos, $y_pos , ($x_diff*15)+15+$x_pos, $y_pos+15 , $white );
					
					$data = array_shift($user_output);
					
					if(isset($user_tweet_id[$data])){
					
						$text = $user_tweet_id[$data];
						
					}else{
					
						$text = 0;
					
					}
					
					imagettftext ( $im , 10.0 , 0 , ($x_diff*15)+$x_pos+2 , $y_pos+13 , $black , "core/misc/fonts/arial.ttf" , $text);						
					
					$y_pos+=15;
					
				}
				
				$file_process->file_image_create("data/twitter_harvest/files/conversation_tree/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_conversation_tree_user_involved.jpg", "jpeg", $im);
				
				$output = "<h2>" . $this->language->translate("tools/tweet_conversation_tree", "Tweet Conversation Tree") . "</h2>";
					
				$output .= "<img src='data/twitter_harvest/files/conversation_tree/" . str_replace("%23","", str_replace(".","",$_POST['tweetfile'])) . "_conversation_tree_user_involved.jpg' />";

				return $output . "<p><a href='?tool=tweet_conversation_tree'>" . $this->language->translate("tools/tweet_conversation_tree", "Return to Tweet Conversation Tree") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$tweets = array_merge($file_process->read_folder("data/twitter_harvest/files/aggregate/"),$file_process->read_folder("data/twitter_harvest/files/usertweets/"));
				
				arsort($tweets);
				
				if(count($tweets)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/tweet_conversation_tree", "Display a Tweet file") . "</h2>
							   <p>" . $this->language->translate("tools/tweet_conversation_tree", "Choose a Tweet file to start from") . "</p>
							   <form action='' method='POST'>
									<select name='tweetfile'>
										<option>" . $this->language->translate("tools/tweet_conversation_tree", "Select a file") . "</output>";
								
					while($plain = array_pop($tweets)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/tweet_conversation_tree", "display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/tweet_conversation_tree", "No Tweet files exist, please create one for using this tool.") . "</p>";				
				
				}
			
			}
		
		}
	
	}