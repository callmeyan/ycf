<?php
class YCRException extends Exception{
	function __construct($message,$code = -1){
		$this->code = $code;
		$this->message = $message;
	}
}
class RouterException extends YCRException{

	function __construct($message){
		parent::__construct($message,$this->getCode() + 5000);
	}
}
class FileException extends YCRException{
	function __construct($message){
		parent::__construct($message,$this->getCode() + 4000);
	}
}

class NotFoundException extends  YCRException{
    function __construct($message){
        parent::__construct($message,$this->getCode() + 6000);
    }
}


class DBException extends YCRException{
	function __construct($message,$code){
		parent::__construct($message,$this->getCode() + 6000);
	}
}
class TemplateException extends  YCRException{}
class ServiceException extends YCRException{}
class AppException extends YCRException{}