<?PHP

	class hbp13{
	
		private $page;
		private $file_process;
		private $language;
	
		public function __construct($theme, $file_process, $language, $raw = false){
		
			$this->page = new StdClass;
			$this->file_process = $file_process;
			$this->language = $language;
			$this->home = "Home";
			
			if($raw){
			
				if(isset($_GET['data'])){
			
					require_once("data/" . $_GET['data'] . "/" . $_GET['data'] . ".php");	
					$data_page = new $_GET['data']($this->language);
					$this->page->index = $data_page->index();
				
				}else if(isset($_GET['tool'])){
				
					require_once("tools/tool.php");	
					require_once("tools/" . $_GET['tool'] . "/" . $_GET['tool'] . ".php");	
					$tool_page = new $_GET['tool']($this->language);	
					$this->page->index = $tool_page->index();
				
				}
				
				echo $this->page->index;
			
			}else{
			
				self::render($theme);
				self::display();
				
			}
		
		}
		
		public function render($theme){
		
			$this->page->head = $this->head($theme);
			
			if(isset($_GET['data'])){
			
				require_once("data/data.php");	
				require_once("data/" . $_GET['data'] . "/" . $_GET['data'] . ".php");	
				$data_page = new $_GET['data']($this->language);			
				$this->page->index_data = $data_page->head($this->file_process);
			
			}else if(isset($_GET['tool'])){
			
				require_once("tools/tool.php");	
				require_once("tools/" . $_GET['tool'] . "/" . $_GET['tool'] . ".php");	
				$tool_page = new $_GET['tool']($this->language);			
				$this->page->index_tool = $tool_page->head($this->file_process);
			
			}
			
			$this->page->body = $this->body();
			$this->page->header = $this->header();
			
			if(isset($_GET['data'])){
			
				require_once("data/" . $_GET['data'] . "/" . $_GET['data'] . ".php");	
				$data_page = new $_GET['data']($this->language);
				$this->page->index = $data_page->index();
			
			}else if(isset($_GET['tool'])){
			
				require_once("tools/" . $_GET['tool'] . "/" . $_GET['tool'] . ".php");	
				$tool_page = new $_GET['tool']($this->language);	
				$this->page->index = $tool_page->index();
			
			}else{
			
				$this->page->index = $this->index();
			
			}
			
			$this->page->footer = $this->footer();
		
		}
		
		public function head($theme){
		
			$scripts = $this->file_process->read_folder(dirname(__FILE__) . "/scripts");
			
			$output = "";
			
			while($script = array_pop($scripts)){
			
				$output .= "\t<script type='text/javascript' language='javascript' src='look/" . $theme . "/scripts/" . $script . "'></script>\n";
			
			}
			
			$css = $this->file_process->read_folder(dirname(__FILE__) . "/css");
			
			while($style = array_pop($css)){
			
				$output .= "\t\t<link href='look/" . $theme . "/css/" . $style . "' rel='stylesheet' type='text/css'>\n";
			
			}
		
			return "\n\t<head>\n\t" . $output . "\t
					<link rel='icon' href='http://localhost/humabird/favicon.ico'>
					<title>Huma bird project</title>
					</head>";
		
		}
		
		private function header(){
		
			$html = "<div id='side'></div>\n
					<div id='main'>
						<div id='logo'>
							<img src='look/hbp13/images/banner.png' style='vertical-align:middle' /> - <span><a href='/humabird'>" . $this->language->translate("index", "HOME") . "</a>";
			
			if(isset($_GET['data'])){
			
				$html .= " | <a href='?data=" . $_GET['data'] . "'>" . $this->language->translate("data/" . $_GET['data'], ucfirst(str_replace("_"," ",$_GET['data']))) . "</a>";
			
			}
			
			if(isset($_GET['tool'])){
			
				$html .= " | <a href='?tool=" . $_GET['tool'] . "'>" . $this->language->translate("tools/" . $_GET['tool'], ucfirst(str_replace("_"," ",$_GET['tool']))) . "</a>";
			
			}
				
			$html .= "</span>
				</div>\n\t";
				
			return $html;
		
		}
		
		private function index(){
		
			require_once("data/data.php");	
			require_once("tools/tool.php");	
			
			$output = array();
				
			$datum = $this->file_process->read_folder("data");
						
			arsort($datum);
			
			while($data = array_pop($datum)){
			
				if($data !="data.php" && $data!=".DS_Store"){
				
					require_once("data/" . $data . "/" . $data . ".php");	
					$data_page = new $data($this->language);			
					$classification = $data_page->classification();
						
					if(!isset($output[$classification->type])){
					
						$output[$classification->type] = array();
						
					}
					
					if(!isset($output[$classification->type]['columns'][$classification->column])){
					
						$output[$classification->type]['columns'][$classification->column] = array(array($classification->link, $this->language->translate("data/" . $data, $classification->name)));
						
					}else{
					
						array_push($output[$classification->type]['columns'][$classification->column], array($classification->link, $this->language->translate("data/" . $data, $classification->name)));
					
					}
					
				}
			
			}
			
			$tools = $this->file_process->read_folder("tools");
			
			while($tool = array_pop($tools)){
			
				if($tool !="tool.php" && $tool!=".DS_Store"){
				
					require_once("tools/" . $tool . "/" . $tool . ".php");	
					$tool_page = new $tool($this->language);			
					$classification = $tool_page->classification();
					
					if(!isset($output[$classification->type])){
					
						$output[$classification->type] = array();
						
					}
					
					if(!isset($output[$classification->type]['columns'][$classification->column])){
					
						$output[$classification->type]['columns'][$classification->column] = array(array($classification->link, $this->language->translate("tools/" . $tool, $classification->name)));
						
					}else{
					
						array_push($output[$classification->type]['columns'][$classification->column], array($classification->link, $this->language->translate("tools/" . $tool, $classification->name)));
					
					}
			
				}
			
			}
			
			$screen_output = "";
			
			ksort($output);
			
			$screen_output .= "<div><h2>" . $this->language->translate("Welcome header","Welcome to the Huma Bird Project") . "</h2><p>" . $this->language->translate("Welcome","The Huma Bird Project is a series of tools to allow people to analyse various resources. Please choose from a tool or data type below to start work. Each tool has its own help pages.") . "</p></div>";
			
			foreach($output as $type => $type_data){
			
				$intro_desc = $type;
			
				$screen_output .= "<div class='type'><h2 onclick='javascript:expand_all(this)'><span>+</span>" . $this->language->translate($type,$type) . "</h2>";

				foreach($type_data as $column => $parts){
						
					foreach($parts as $type => $options){
					
						asort($options);
					
						$screen_output .= "<div class='columns hide'>
							<div>";
						
						$screen_output .= "<h3 onclick='javascript:expand(this)'><span>+</span>" . $this->language->translate($type,$type) . "</h3>";
						
						$screen_output .= "<ul class='hide'>";
						
						foreach($options as $name => $details){
						
							$screen_output .= "<li>
													<a href='" . $details[0] . "'>" . $this->language->translate($details[0],$details[1]) . "</a>
											   </li>";
						
						}
						
						$screen_output .= "</ul></div></div>";
						
					}
				
				}
				
				$screen_output .= "</div>";
			
			}
		
			return $screen_output;
		
		}
		
		private function body(){
		
			$output = "\n\t<body>";
			
			return $output;
		
		}
		
		private function footer(){
		
			return "</div>\n\t</body>";
		
		}
		
		private function display(){
		
			echo "<html>";
		
			foreach($this->page as $section => $html){
			
				echo "\n\t<!--" . $section . "-->";
				echo $html;
			
			}
			
			echo "\n</html>";
		
		}
			
	}