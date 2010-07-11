<?
	/*
	
		login_page.class.php
		
		
	*/

	namespace Skeet\Page;
	class LoginPage extends AbstractPage {
		protected $pageName = "Login";
		protected $requireLogin = false;
		
		public function __construct() {
			parent::__construct();
			if(requestExists("do_login")) {
				$this->checkLoginInput();
				
				/*
					Because the check*Input() functions should all set the class variable $errorMessages to
					an array of the errors in user input, if there are _no_ errors, we want to execute some code.
					If there ARE errors, we want to do nothing, because the page will just continue loading, and our
					error messages will be displayed.
					
				*/
				
				if(count($this->getErrorMessages()) == 0) {
					/*
						The UserFactory::doLogin() request in checkLoginInput() does all the work of actually logging the user 
						in, so now that we know that's been successful, we can redirect to the user-only page that we want
						
						In this example, that's the homepage.
					*/
					LinkFactory::getLink("Home")->doRedirect();
				}
			}
			elseif(requestExists("do_register")) {
				$this->checkRegisterInput();
				
				if(count($this->getErrorMessages()) == 0) {
					$user = new User();
					$user->setUsername(getRequestValue("username"));
					$user->changePassword(getRequestValue("password"));
					$user->setFirstName(getRequestValue("first_name"));
					$user->setLastName(getRequestValue("last_name"));
					$user->setLastLoginDatetime(date("Y-m-d H:i:s"));
					$user->save();
					
					$email = new Email();
					$email->setEmailTypeID(USER_EMAIL_TYPE_PRIMARY);
					$email->setUserID($user->getID());
					$email->setEmailAddress(getRequestValue("email_address"));
					$email->save();
					
					UserFactory::doLogin($user->getUsername(),getRequestValue("password"));
					LinkFactory::getLink("Home")->doRedirect();
				}
			}
		}
		
		protected function checkRegisterInput() {
			$errorMessages = array();
			
			if(!requestExists("username") || strlen(getRequestValue("username")) < 6) {
				$errorMessages["username"] = "You must answer a username of at least 6 characters.";
			}
			elseif(UserFactory::doesUserExist(getRequestValue("username"))) {
				$errorMessages["username"] = "The username you chose is already in use.";
			}
			
			if(!requestExists("password") || !isGoodPassword(getRequestValue("password"))) {
				$errorMessages["password"] = "You must enter a password of at least 6 characters.";
			}
			elseif(!requestExists("password_confirm")) {
				$errorMessages["password_confirm"] = "You must confirm your password.";
			}
			elseif(getRequestValue("password") != getRequestValue("password_confirm")) {
				$errorMessages["password"] = "The passwords you entered do not match.";
			}
			
			if(!getRequestValue("first_name")) {
				$errorMessages["first_name"] = "You must enter a first name.";
			}
			
			if(!getRequestValue("last_name")) {
				$errorMessages["last_name"] = "You must enter a last name.";
			}
			
			if(!getRequestValue("email_address") || !isGoodEmail(getRequestValue("email_address"))) {
				$errorMessages["email_address"] = "You must enter a valid email address.";
			}
			
			$this->errorMessages = $errorMessages;
		}
		
		protected function checkLoginInput() {
			$errorMessages = array();
			if(!requestExists("username")) {
				$errorMessages["username"] = "You did not enter a username.";
			}
			
			if(!requestExists("password")) {
				$errorMessages["password"] = "You did not enter a password.";
			}
			
			if(!UserFactory::doLogin(getRequestValue("username"),getRequestValue("password"))) {
				$errorMessages["login"] = "That username/password combination does not exist in our system.";
			}
			$this->errorMessages = $errorMessages;
		}
		
	}
?>