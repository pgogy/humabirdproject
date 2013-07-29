<?PHP

	class twitter_setup extends data{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
			
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Twitter Analysis";
			$classification->column = "Twitter setup and harvest";
			$classification->link = "?data=twitter_setup";
			$classification->name = "Twitter setup";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='look/" . $theme . "/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='look/" . $theme . "/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>Twitter Setup</h2>
						   <ul>
								<li>
									<a href='?data=twitter_setup&action=instructions'>" . $this->language->translate("data/twitter_setup", "Instructions") . "</a>
								</li>
								<li>
									<a href='?data=twitter_setup&action=create'>" . $this->language->translate("data/twitter_setup", "Setup twitter for harvest") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("data/twitter_setup", "help");
			
			return $output . "<p><a href='?data=twitter_setup'>" . $this->language->translate("data/twitter_setup", "Return to Twitter Setup") . "</a></p>";
				
		}
		
		private function create(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$response1 = $file_process->create_file("data/twitter_setup/files/ckey", $_POST['ckey']);
				$response2 = $file_process->create_file("data/twitter_setup/files/csecret", $_POST['csecret']);
				$response3 = $file_process->create_file("data/twitter_setup/files/okey", $_POST['okey']);
				$response4 = $file_process->create_file("data/twitter_setup/files/osecret", $_POST['osecret']);
				$output = "<p>"  . $this->language->translate("data/twitter_setup", "Files saved") . "</p>";
				return $output . "<p><a href='?data=twitter_setup'>" . $this->language->translate("data/twitter_setup", "Return to Twitter Setup") . "</a></p>";
			
			}else{
		
				$output = "<h2>" . $this->language->translate("data/twitter_setup", "Twitter Setup") . "</h2>
							<form action='' method='POST'>";
					
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				
				if($file_process->file_exists_check("data/twitter_setup/files/ckey")){
				
					$ckey = $file_process->file_get_all("data/twitter_setup/files/ckey");
				
				}else{
				
					$ckey = $this->language->translate("data/twitter_setup", "Enter the consumer key here");
				
				}
				
				if($file_process->file_exists_check("data/twitter_setup/files/csecret")){
				
					$csecret = $file_process->file_get_all("data/twitter_setup/files/csecret");
				
				}else{
				
					$csecret = $this->language->translate("data/twitter_setup", "Enter the consumer secret here");
				
				}
				
				if($file_process->file_exists_check("data/twitter_setup/files/okey")){
				
					$okey = $file_process->file_get_all("data/twitter_setup/files/okey");
				
				}else{
				
					$okey = $this->language->translate("data/twitter_setup", "Enter the OAuth key here");
				
				}
				
				if($file_process->file_exists_check("data/twitter_setup/files/osecret")){
				
					$osecret = $file_process->file_get_all("data/twitter_setup/files/osecret");
				
				}else{
				
					$osecret = $this->language->translate("data/twitter_setup", "Enter the OAuth secret here");
				
				}
				
			
				$output .= "<p>" . $this->language->translate("data/twitter_setup", "Consumer key") . "</p>
						<input type='text' size=100 value='" .  $ckey . "' name='ckey' />
						<p>" . $this->language->translate("data/twitter_setup", "Consumer secret") . "</p>
						<input type='text' size=100 value='" . $csecret . "' name='csecret' />
						<p>" . $this->language->translate("data/twitter_setup", "OAuth key") . "</p>
						<input type='text' size=100 value='" . $okey . "' name='okey' />
						<p>" . $this->language->translate("data/twitter_setup", "OAuth secret") . "</p>
						<input type='text' size=100 value='" . $osecret . "' name='osecret' />
						<input type='submit' value='" . $this->language->translate("data/twitter_setup", "Setup Twitter") . "' />
					</form>";
					
				return $output;
							
			}
		
		}
		
	}