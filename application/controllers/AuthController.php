<?php

class AuthController extends Zend_Controller_Action
{
	
	public function loginAction()
	{
		if($this->getRequest()->isPost())
		{
			echo "is post";
		}
	}
	
}