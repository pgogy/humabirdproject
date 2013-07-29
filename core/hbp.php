<?PHP

	class humabirdproject{
	
		private $language;
	
		public function __construct($language){
		
			$this->language = $language;
			self::display_page();
		
		}
		
		private function display_page(){

			require_once("file_handling/file_handling.php");			
			$file_process = new file_handling();			
			$theme = $file_process->file_get_all("look/chosen_theme.txt");
			
			require_once("language_process/language_process.php");			
			$language = new language_process($this->language, $file_process);
			
			require_once("theme_display/theme_display.php");			
			$theme_display = new theme_display();			
			$theme_display->display_theme($theme, $file_process, $language);			
		
		}
		
	}
	
	$humabirdproject = new humabirdproject("EN-GB");