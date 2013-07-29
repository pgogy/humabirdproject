<?PHP

	class language_process{
	
		private $language;
		private $file_process;
		
		public function __construct($language, $file_process){
		
			$this->language = $language;
			$this->file_process = $file_process;
		
		}
		
		public function translate_help($class){
		
			if(file_exists(getcwd() . "/languages/" . $this->language . "/" . $class . "/help.txt")){
					
				return file_get_contents(getcwd() . "/languages/" . $this->language . "/" . $class . "/help.txt");
					
			}else{
			
				file_put_contents(getcwd() . "/languages/" . $this->language . "/" . $class . "/help.txt", "<p>Add HTML here</p>");
				return "HTML FILE CREATED";
			
			}
		
		}
		
		public function translate($class, $term_translate){
		
			if(strpos($class, "/")!==FALSE&&strpos($class,"?")===FALSE){
			
				$directories = explode("/", $class);
				
				if(!file_exists(getcwd() . "/languages/" . $this->language . "/" . $directories[0])){
				
					mkdir(getcwd() . "/languages/" . $this->language . "/" . $directories[0]);
				
				}
				
				if(!file_exists(getcwd() . "/languages/" . $this->language . "/" . $directories[0] . "/" . $directories[1])){
				
					mkdir(getcwd() . "/languages/" . $this->language . "/" . $directories[0] . "/" . $directories[1]);
					file_put_contents(getcwd() . "/languages/" . $this->language . "/" . $directories[0] . "/" . $directories[1] . "/" . $term_translate . ".txt", $term_translate);
				
				}
				
				if(!file_exists(getcwd() . "/languages/" . $this->language . "/" . $directories[0] . "/" . $directories[1] . "/" . $term_translate . ".txt")){
				
					file_put_contents(getcwd() . "/languages/" . $this->language . "/" . $directories[0] . "/" . $directories[1] . "/" . $term_translate . ".txt", $term_translate);
					return file_get_contents(getcwd() . "/languages/" . $this->language . "/" . $directories[0] . "/" . $directories[1] . "/" . $term_translate . ".txt");
				
				}
				
				if(file_exists(getcwd() . "/languages/" . $this->language . "/" . $directories[0] . "/" . $directories[1] . "/" . $term_translate . ".txt")){
				
					return file_get_contents(getcwd() . "/languages/" . $this->language . "/" . $directories[0] . "/" . $directories[1] . "/" . $term_translate . ".txt");
					
				}
			
			}else if(strpos($class, "?")!==FALSE){
			
				if(file_exists(getcwd() . "/languages/" . $this->language . "/" . str_replace("tool","tools",str_replace("?","",str_replace("=","/",$class))) . "/" . $term_translate . ".txt")){
				
					return file_get_contents(getcwd() . "/languages/" . $this->language . "/" . str_replace("tool","tools",str_replace("?","",str_replace("=","/",$class))) . "/" . $term_translate . ".txt");
					
				}
			
			}else{
			
				if($class == $term_translate){
				
					if(file_exists(getcwd() . "/languages/" . $this->language . "/data/" . strtolower(str_replace(" ","_",$class)) . "/" . $class . ".txt")){
					
						return file_get_contents(getcwd() . "/languages/" . $this->language . "/data/" . strtolower(str_replace(" ","_",$class)) . "/" . $class . ".txt");
					
					}
					
					if(file_exists(getcwd() . "/languages/" . $this->language . "/tools/" . strtolower(str_replace(" ","_",$class)) . "/" . $class . ".txt")){
					
						return file_get_contents(getcwd() . "/languages/" . $this->language . "/tools/" . strtolower(str_replace(" ","_",$class)) . "/" . $class . ".txt");
					
					}
					
					if(!file_exists(getcwd() . "/languages/" . $this->language . "/" . $class . ".txt")){
					
						file_put_contents(getcwd() . "/languages/" . $this->language . "/" . $class . ".txt", $class);
					
					}
					
					if(file_exists(getcwd() . "/languages/" . $this->language . "/" . $class . ".txt")){
					
						return file_get_contents(getcwd() . "/languages/" . $this->language . "/" . $class . ".txt");
					
					}
					
				
				}else{
			
					if(!file_exists(getcwd() . "/languages/" . $this->language . "/" . $class . ".txt")){
				
						file_put_contents(getcwd() . "/languages/" . $this->language . "/" . $class . ".txt", $term_translate);
				
					}

					if(file_exists(getcwd() . "/languages/" . $this->language . "/" . $class . ".txt")){
	
						return file_get_contents(getcwd() . "/languages/" . $this->language . "/" . $class . ".txt");
						
					}
				
				}
			
			}
			
			echo $class . " -/- " . $term_translate . "<br />";
		
		}
		
	}