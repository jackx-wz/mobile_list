<?php

Class ErrorController extends Yaf_Controller_Abstract{
    public function errorAction(){
        $exception = $this->getRequest()->getException();
        $this->getView()->assign("exception", $exception);
    }
}
