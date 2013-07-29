<?PHP

	class theme_display{
	
		public function __construct(){
		
		}
		
		public function display_theme($theme, $file_process, $language){
		
			require_once("look/" . $theme . "/" . $theme . ".php");
			
			if(isset($_POST['newwindow'])){
			
				if($_POST['newwindow']==="true"){
			
					$theme = new $theme($theme, $file_process, $language, true);
					
				}else{
				
					$theme = new $theme($theme, $file_process, $language);
				
				}
				
			}else{
			
				$theme = new $theme($theme, $file_process, $language);
				
			}
		
		}
		
	}