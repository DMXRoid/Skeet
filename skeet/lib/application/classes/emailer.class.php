<?
	/*
		emailer.class.php
		
		Basic class to perform essential email sending tasks.  Allows for multipart, text-only,
		and HTML-only emails.  Default is text only.
		
	*/

	class Emailer {
			
		/*
			From: settings.  Changable to whatever you want, defaults to
			DEFAULT_FROM_NAME and DEFAULT_FROM_ADDRESS defined in global_variables.inc.php
		*/
		private $fromName = DEFAULT_FROM_NAME;
		private $fromAddress = DEFAULT_FROM_ADDRESS;
		
		/*
			Recipient lists.  Each is an array of arrays, in the following format:
			$recipients[] = array("name"=>"Dingle McGee","email_address"=>"zongle@dongle.com");
		*/
		
		private $recipients = array();
		private $ccRecipients = array();
		private $bccRecipients = array();
		private $subject;
		private $messageText;
		private $messageHTML;
		private $emailType = EMAIL_TYPE_TEXT_ONLY;	
	
		public function setFromName($fromName) {
			$this->fromName = $fromName;
		}
		
		public function setFromAddress($fromAddress) {
			$this->fromAddress = $fromAddress;
		}
		
		
		public function setSubject($subject) {
			$this->subject = $subject;
		}
		
		public function setMessageText($messageText) {
			$this->messageText = $messageText;
		}
		
		public function setMessageHTML($messageHTML) {
			$this->messageHTML = $messageHTML;
		}
		
		public function setEmailType($emailType) {
			$this->emailType = $emailType;
		}
		
		public function addRecipient($name,$emailAddress) {
			$this->recipients[$emailAddress] = array("name"=>$name,"email_address"=>$emailAddress);
		}
		public function addCCRecipient($name,$emailAddress) {
			$this->ccRecipients[$emailAddress] = array("name"=>$name,"email_address"=>$emailAddress);
		}

		public function addBCCRecipient($name,$emailAddress) {
			$this->bccRecipients[$emailAddress] = array("name"=>$name,"email_address"=>$emailAddress);
		}	
		
		public function clearRecipients() {
			$this->recipients = array();
			$this->bccRecipients = array();
			$this->ccRecipients = array();
		}
		
		public function getFromName() {
			return $this->fromName;
		}
		
		public function getFromAddress() {
			return $this->fromAddress;
		}
		
		public function getSubject() {
			return $this->subject;
		}
		
		public function getMessageText() {
			return $this->messageText;
		}
		
		public function getMessageHTML() {
			return $this->messageHTML;
		}
		
		public function send() {
			$headers = "";
			if(count($this->recipients)) {
				switch($this->emailType) {
					case EMAIL_TYPE_TEXT_ONLY:
						$message = $this->getMessageText();
						break;
						
					case EMAIL_TYPE_HTML_ONLY:
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";		
						$message = $this->getMessageHTML();
						break;
				}
				$headers .= 'From: ' . $this->getFromName() . ' <' . $this->getFromAddress() . '>' . "\r\n";
				
				$displayArray = array();
				$toArray = array();
				
				foreach($this->recipients as $name => $value) {
					$displayArray[] = " " . $name . " <" . $value . ">";
					$toArray[] = $value;
				}
				
				$headers .= 'To: ' . implode(",",$displayArray) . "\r\n";
				
				if(count($this->ccRecipients)) {
					$ccArray = array();
					foreach($this->ccRecipients as $name => $value) {
						$ccArray[] = $name . ' <' . $value . '>';
					}
					$headers .= 'Cc: ' . implode(",",$ccArray) . "\r\n";
				}
				if(count($this->bccRecipients)) {
					$bccArray = array();
					foreach($this->bccRecipients as $name => $value) {
						$bccArray[] = $name . ' <' . $value . '>';
					}
					$headers .= 'Bcc: ' . implode(",",$bccArray) . "\r\n";
				}					
				
				$to = implode(",",$toArray);
				if(mail($to,$this->getSubject(),$message,$headers)) {
					return true;	
				}
				else {
					/*
						If you want, un-comment this next bit.  
					*/	
				}
			}
		}
	}
?>