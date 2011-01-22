<?
	namespace Skeet\Link;
	class GenericLink extends AbstractLink {
		protected $pageName;
		public function __construct($pageName) {
			$this->pageName = $pageName;                                                                                                                                                                                                                                                       

			if(preg_match("/[A-Z]/",$pageName)) {
         	$fileName = substr_replace(preg_replace("/([A-Z])/e",'strtolower("_\\1")',$pageName),'',0,1);
         	$fileArray = explode("_",$fileName);
			}
			else {
				$fileArray = explode(" ", $pageName);
			}
                                                                                                                                                                                                                                                                                            
         if(file_exists(strtolower($pageName) . ".html")) {                                                                                                                                                                                                                                 
            $this->fileName = strtolower($pageName) . ".html";                                                                                                                                                                                                                              
         }                                                                                                                                                                                                                                                                                  
         else {                                                                                                                                                                                                                                                                             
            $this->fileName = implode("/",$fileArray);                                                                                                                                                                                                                                      
         }                                                            		
			$this->pageName = $pageName;
			
		}
	}
?>