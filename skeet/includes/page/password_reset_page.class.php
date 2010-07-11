<?
	class PasswordResetPage extends AbstractPage {
		protected $user;
		
		public function __construct() {
			parent::__construct();
			if(getRequestValue("do_reset_password")) {
				$this->checkInput();
				if(!count($this->getErrorMessages())) {
					$passwordReset = new PasswordReset();
					$passwordReset->setUserID($this->getUser()->getID());
					$passwordReset->createToken();
					$passwordReset->save();
					$passwordReset->sendResetEmail();
				}
			}
		}
		
		protected function checkInput() {
			$errorMessages = array();
			
			if($user = UserFactory::getUserFromUsername(getRequestValue("username"))) {
				$this->user = $user;
			}
			else {
				$errorMessages["username"] = "The username entered does not exist.";
			}
		}
		
		public function getUser() {
			return $this->user;
		}
	}
?>