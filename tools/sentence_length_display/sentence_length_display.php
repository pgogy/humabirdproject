<?PHP

	class sentence_length_display extends tool{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
		
		}
		
		public function classification(){
		
			$classification = new StdClass();
		
			$classification->type = "Text Analysis";
			$classification->column = "Tools";
			$classification->link = "?tool=sentence_length_display";
			$classification->name = "Sentence Length Display";
			
			return $classification;
		
		}
		
		public function head($file_process){
		
			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='tools/sentence_length_display/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='tools/sentence_length_display/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
			
			return $output;
		
		}
		
		public function index(){
		
			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("tools/sentence_length_display", "Sentence length display") . "</h2>
						   <ul>
								<li>
									<a href='?tool=sentence_length_display&action=instructions'>" . $this->language->translate("tools/sentence_length_display", "Instructions") . "</a>
								</li>
								<li>
									<a href='?tool=sentence_length_display&action=display'>" . $this->language->translate("tools/sentence_length_display", "Display") . "</a>
								</li>
								<li>
									<a href='?tool=sentence_length_display&action=compare'>" . $this->language->translate("tools/sentence_length_display", "Compare (Visual)") . "</a>
								</li>
								<li>
									<a href='?tool=sentence_length_display&action=compare_maths'>" . $this->language->translate("tools/sentence_length_display", "Compare (Maths)") . "</a>
								</li>
								<li>
									<a href='?tool=sentence_length_display&action=different_lengths'>" . $this->language->translate("tools/sentence_length_display", "Different Lengths") . "</a>
								</li>
						   </ul>";
						   
			}else{
			
				$output = self::$_GET['action']();
			
			}
				
			return $output;
			
		}
		
		private function instructions(){
		
			$output = $this->language->translate_help("tools/sentence_length_display", "help");
			
			return $output . "<p><a href='?tool=sentence_length_display'>" . $this->language->translate("tools/sentence_length_display", "Return to Sentence length display") . "</a></p>";
				
		}
		
		private function display(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/sentence_length/files/" . $_POST['slfile']));
				
				$output = "<h2>" . $this->language->translate("tools/sentence_length_display", "Displaying sentence length for file") . " : " . $_POST['slfile'] . "</h2>";
				
				foreach($data as $index => $length){
				
					$output .= "<p>" . $this->language->translate("tools/sentence_length_display", "Sentence") . " " . ($index + 1) . " : " . $this->language->translate("tools/sentence_length_display", "Length") . " " . $length . "</p>";
				
				}
				
				return $output . "<p><a href='?tool=sentence_length_display'>" . $this->language->translate("tools/sentence_length_display", "Return to Sentence Length Display") . "</a></p>";
				
			}else{
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/sentence_length/files/");
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/sentence_length_display", "Display a Sentence Length file") . "</h2>
							   <p>" . $this->language->translate("tools/sentence_length_display", "Choose a sentence length to start from") . "</p>
							   <form action='' method='POST'>
									<select name='slfile'>
										<option>" . $this->language->translate("tools/sentence_length_display", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
									<input type='submit' value='" . $this->language->translate("tools/sentence_length_display", "Display") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p" . $this->language->translate("tools/sentence_length_display", "No Sentence Length files exist. Please create one before using this tool.") . "p>";				
				
				}
			
			}
		
		}
		
		private function compare(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/sentence_length/files/" . $_POST['slfileone']));
				
				$output = "<h2>" . $this->language->translate("tools/sentence_length_display", "Sentence length compared between") . "</h2>";
				$output .= "<p>" . $this->language->translate("tools/sentence_length_display", "Results for ") . " " . $_POST['slfileone'] . " " . $this->language->translate("tools/sentence_length_display", "and") . " " . $_POST['slfiletwo'] . "</p>";
				$output .= "<div class='sl_compare'>";
				
				foreach($data as $index => $length){
				
					$output .= "<p>" . $this->language->translate("tools/sentence_length_display", "Sentence") . " " . ($index + 1) . " : " . $this->language->translate("tools/sentence_length_display", "Length") . " " . $length . "</p>";
				
				}
				
				$output .="</div>";
				
				$data = unserialize($file_process->file_get_all("data/sentence_length/files/" . $_POST['slfiletwo']));
			
				$output .= "<div class='sl_compare'>";
				
				foreach($data as $index => $length){
				
					$output .= "<p>" . $this->language->translate("tools/sentence_length_display", "Sentence") . " " . ($index + 1) . " : " . $this->language->translate("tools/sentence_length_display", "Length") . " " . $length . "</p>";
				
				}
				
				$output .="</div>";
				
				return $output . "<p><a href='?tool=sentence_length_display'>" . $this->language->translate("tools/sentence_length_display", "Return to Sentence Length Display") . "</a></p>";
			
			}else{
		
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/sentence_length/files/");
				$second_plain_text = $plain_text;
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/sentence_length_display", "Compare two sentence length files") . "</h2>
							   <form action='' method='POST'>
									<label>
										" . $this->language->translate("tools/sentence_length_display", "File one") . "
									</label>
									<select name='slfileone'>
										<option>" . $this->language->translate("tools/sentence_length_display", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
					
					$output .= "<label>
									" . $this->language->translate("tools/sentence_length_display", "File two") . "
								</label>
								<select name='slfiletwo'>
										<option>" . $this->language->translate("tools/sentence_length_display", "Select a file") . "</output>";
								
					while($plain = array_pop($second_plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/sentence_length_display", "Compare") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/sentence_length_display", "No Sentence length files exist, please create one before using this tool") . "</p>";				
				
				}
							
			}
		
		}
		
		private function compare_maths(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/sentence_length/files/" . $_POST['slfileone']));
				$data_two = unserialize($file_process->file_get_all("data/sentence_length/files/" . $_POST['slfiletwo']));
				
				$output = "<h2>Sentence length compared between </h2>";
				$output .= "<p>Results for " . $_POST['slfileone'] . " and " . $_POST['slfiletwo'] . "</p>";
				
				foreach($data as $index => $length){
				
					$output .= "<p>" . $this->language->translate("tools/sentence_length_display", "Sentence") . " " . ($index + 1) . " : " . $this->language->translate("tools/sentence_length_display", "Length") . " " . $length . " | ";
					
					$second_length = array_shift($data_two);
					
					$output .= $second_length . " | " . $this->language->translate("tools/sentence_length_display", "Difference") . " " . (max($length,$second_length) - min($length,$second_length));
					
					$output .= "</p>";
				
				}
				
				return $output . "<p><a href='?tool=sentence_length_display'>" . $this->language->translate("tools/sentence_length_display", "Return to Sentence Length Display") . "</a></p>";
			
			}else{
		
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/sentence_length/files/");
				$second_plain_text = $plain_text;
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/sentence_length_display", "Compare two sentence length files (maths)") . "</h2>
							   <form action='' method='POST'>
									<label>
										" . $this->language->translate("tools/sentence_length_display", "File one") . "
									</label>
									<select name='slfileone'>
										<option>" . $this->language->translate("tools/sentence_length_display", "Select a file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
					
					$output .= "<label>
									" . $this->language->translate("tools/sentence_length_display", "File two") . "
								</label>
								<select name='slfiletwo'>
										<option>" . $this->language->translate("tools/sentence_length_display", "Select a file") . "</output>";
								
					while($plain = array_pop($second_plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/sentence_length_display", "Compare") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/sentence_length_display", "No before and after files exist, please create one before using this tool") . "</p>";				
				
				}
							
			}
		
		}

		private function different_lengths(){
		
			if(count($_POST)!==0){
			
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$data = unserialize($file_process->file_get_all("data/sentence_length/files/" . $_POST['slfileone']));
				$data_two = unserialize($file_process->file_get_all("data/sentence_length/files/" . $_POST['slfiletwo']));
				
				$output = "<h2>" . $this->language->translate("tools/sentence_length_display", "Sentence length compared between") . " </h2>";
				$output .= "<p>" . $this->language->translate("tools/sentence_length_display", "Results for ") . " " . $_POST['slfileone'] . " " . $this->language->translate("tools/sentence_length_display", "and") . " " . $_POST['slfiletwo'] . "</p>";
				
				foreach($data as $index => $length){
				
					$second_length = array_shift($data_two);
				
					if((max($length,$second_length) - min($length,$second_length))!==0){
					
						$output .= "<p>" . $this->language->translate("tools/sentence_length_display", "Sentence") . " " . ($index + 1) . " " . $this->language->translate("tools/sentence_length_display", "has a difference of") . " " . (max($length,$second_length) - min($length,$second_length)) . "</p>";
					
					}else{
					
						$output .= "<p>" . $this->language->translate("tools/sentence_length_display", "Sentence length equal") . " : " . ($index + 1) . " </p>";
						
					}
				
				}
				
				return $output . "<p><a href='?tool=sentence_length_display'>" . $this->language->translate("tools/sentence_length_display", "Return to Sentence length display") . "</a></p>";
			
			}else{
		
				require_once("core/file_handling/file_handling.php");
				$file_process = new file_handling();
				$plain_text = $file_process->read_folder("data/sentence_length/files/");
				$second_plain_text = $plain_text;
				
				arsort($plain_text);
				
				if(count($plain_text)!==0){
				
					$output = "<h2>" . $this->language->translate("tools/sentence_length_display", "Compare two sentence length files (show different lengths)") . "</h2>
							   <form action='' method='POST'>
									<label>
										" . $this->language->translate("tools/sentence_length_display", "File one") . "
									</label>
									<select name='slfileone'>
										<option>" . $this->language->translate("tools/sentence_length_display", "Select file") . "</output>";
								
					while($plain = array_pop($plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />";
					
					$output .= "<label>
									" . $this->language->translate("tools/sentence_length_display", "File two") . "
								</label>
								<select name='slfiletwo'>
										<option>" . $this->language->translate("tools/sentence_length_display", "Select a file") . "</output>";
								
					while($plain = array_pop($second_plain_text)){
					
						$output .= "<option value='" . $plain . "'>" . $plain . "</output>";
					
					}
	
					$output .=	"</select><br />
								 <input type='submit' value='" . $this->language->translate("tools/sentence_length_display", "Compare") . "' />
							   </form>";
					
					return $output;
		
				}else{
				
					$output = "<p>" . $this->language->translate("tools/sentence_length_display", "No sentence length files exist, please create one before using this tool	") . "</p>";				
				
					return $output;
				
				}
							
			}
		
		}
		
	}