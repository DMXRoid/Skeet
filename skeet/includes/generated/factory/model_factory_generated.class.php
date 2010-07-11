<?php
	namespace Skeet\Generated\Factory;

	class ModelFactoryGenerated {
		public static function getModel($tableName,$primaryKeyID,$customSQL=NULL,$dataRow=NULL) {
			$modelObject = "";
			switch($tableName) {
				case 'address':
					$modelObject = new \Skeet\Model\Address($primaryKeyID,$customSQL,$dataRow);
					break;
				case 'address_type':
					$modelObject = new \Skeet\Model\AddressType($primaryKeyID,$customSQL,$dataRow);
					break;
				case 'config':
					$modelObject = new \Skeet\Model\Config($primaryKeyID,$customSQL,$dataRow);
					break;
				case 'country':
					$modelObject = new \Skeet\Model\Country($primaryKeyID,$customSQL,$dataRow);
					break;
				case 'email':
					$modelObject = new \Skeet\Model\Email($primaryKeyID,$customSQL,$dataRow);
					break;
				case 'email_type':
					$modelObject = new \Skeet\Model\EmailType($primaryKeyID,$customSQL,$dataRow);
					break;
				case 'password_reset':
					$modelObject = new \Skeet\Model\PasswordReset($primaryKeyID,$customSQL,$dataRow);
					break;
				case 'session':
					$modelObject = new \Skeet\Model\Session($primaryKeyID,$customSQL,$dataRow);
					break;
				case 'session_variable':
					$modelObject = new \Skeet\Model\SessionVariable($primaryKeyID,$customSQL,$dataRow);
					break;
				case 'state':
					$modelObject = new \Skeet\Model\State($primaryKeyID,$customSQL,$dataRow);
					break;
				case 'user':
					$modelObject = new \Skeet\Model\User($primaryKeyID,$customSQL,$dataRow);
					break;
				case 'user_type':
					$modelObject = new \Skeet\Model\UserType($primaryKeyID,$customSQL,$dataRow);
					break;
							}
			return $modelObject;
		}
	}
?>