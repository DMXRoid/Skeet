<?php	namespace Skeet\Generated\Factory;
	
	class ModelCollectionFactoryGenerated {
		public static function getModelCollection($tableName,$definitionArray=array()) {
			$modelCollectionObject = "";
			switch($tableName) {
				case 'address':
					$modelCollectionObject = new \Skeet\Model\AddressCollection($definitionArray);
					break;
				case 'address_type':
					$modelCollectionObject = new \Skeet\Model\AddressTypeCollection($definitionArray);
					break;
				case 'config':
					$modelCollectionObject = new \Skeet\Model\ConfigCollection($definitionArray);
					break;
				case 'country':
					$modelCollectionObject = new \Skeet\Model\CountryCollection($definitionArray);
					break;
				case 'email':
					$modelCollectionObject = new \Skeet\Model\EmailCollection($definitionArray);
					break;
				case 'email_type':
					$modelCollectionObject = new \Skeet\Model\EmailTypeCollection($definitionArray);
					break;
				case 'password_reset':
					$modelCollectionObject = new \Skeet\Model\PasswordResetCollection($definitionArray);
					break;
				case 'session':
					$modelCollectionObject = new \Skeet\Model\SessionCollection($definitionArray);
					break;
				case 'session_variable':
					$modelCollectionObject = new \Skeet\Model\SessionVariableCollection($definitionArray);
					break;
				case 'state':
					$modelCollectionObject = new \Skeet\Model\StateCollection($definitionArray);
					break;
				case 'user':
					$modelCollectionObject = new \Skeet\Model\UserCollection($definitionArray);
					break;
				case 'user_type':
					$modelCollectionObject = new \Skeet\Model\UserTypeCollection($definitionArray);
					break;
							}
			return $modelCollectionObject;
		}
	}
?>