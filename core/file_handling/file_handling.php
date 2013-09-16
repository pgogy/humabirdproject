<?PHP

	class file_handling{
	
		public function __construct(){
		
		}
		
		public function file_exists_check($file_path){
		
			if(file_exists($file_path)){
			
				return true;
				
			}else{
			
				return false;
				
			}
		
		}
		
		public function file_get_all($file_path){

			return file_get_contents($file_path);
		
		}
		
		public function file_upload_error($error){
		
			switch($error){
				
				case 1 : return array(false, "The uploaded file exceeds the upload_max_filesize directive in php.ini"); break;
				case 1 : return array(false, "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"); break;
				case 1 : return array(false, "The uploaded file was only partially uploaded"); break;
				case 1 : return array(false, "No file was uploaded"); break;
				case 1 : return array(false, "Missing a temporary folder"); break;
				case 1 : return array(false, "Failed to write file to disk"); break;
				case 1 : return array(false, "A PHP extension stopped the file upload"); break;
				default : break;
				
			}
			
		}
		
		public function upload_file($file_path, $filename, $files){
		
			if(trim($filename)===""){
			
				return array(false, "No filename set");
			
			}else if(!file_exists($file_path . str_replace(" ", "_", $filename))){
		
				if (move_uploaded_file($files['uploadfile']['tmp_name'], $file_path . str_replace(" ", "_", $filename))) {
					return array(true,"File uploaded");
				} else {
					return array(false,"File failed to upload");
				}
				
			}else{
			
				return array(false, "File already exists");
			
			}
		
		}
		
		public function delete_file($file_path){
		
			if(file_exists($file_path)){

				if(unlink($file_path)){
			
					return array(true, "File " . $file_path . " deleted");
			
				}else{
			
					return array(false, "File not deleted");
			
				}
				
			}else{
			
				return array(false, "File does not exist");
			
			}
		
		}
		
		public function update_file($file_path, $data){
		
			if(!file_exists($file_path)){

				if(file_put_contents($file_path, $data)){
			
					return array(true, "File " . $file_path . " created");
			
				}else{
			
					return array(false, "File content failed to be saved");
			
				}
				
			}else{
			
				if(file_put_contents($file_path, $data)){
			
					return array(true, "File " . $file_path . " created");
			
				}else{
			
					return array(false, "File content failed to be saved");
			
				}
			
			}
		
		}
		
		public function create_file($file_path, $data){
		
			if(!file_exists($file_path)){

				if(file_put_contents($file_path, $data)){
			
					return array(true, "File " . $file_path . " created");
			
				}else{
			
					return array(false, "File content failed to be saved");
			
				}
				
			}else{
			
				return array(false, "File exists");
			
			}
		
		}
		
		public function file_image_create($name, $type, $im){
		
			if($type=="jpeg"){
			
				imagejpeg($im, $name);
			
			}
		
		}
		
		public function read_folder_files_only($file_path){

			$includes = array();

			$files = opendir($file_path);
					
			while($file = readdir($files)){
			
				if( $file != "." && $file != ".." && $file != ".gitignore"){
				
					if(!is_dir($file_path . $file)){
				
						array_push($includes, $file);
					
					}
					
				}
			
			}
			
			return $includes;
			
		}
		
		public function read_folder($file_path){

			$includes = array();

			if(is_dir($file_path)){			
			
				$files = opendir($file_path);
						
				while($file = readdir($files)){
				
					if( $file != "." && $file != ".." && $file != ".gitignore"){
					
						array_push($includes, $file);
						
					}
				
				}
			
			}
			
			return $includes;
			
		}
		
	}