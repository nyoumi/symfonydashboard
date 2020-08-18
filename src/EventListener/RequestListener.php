<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Session\Session;




class RequestListener
{

    private $session;
    public function __construct(Session $session)
    {
        $this->session = $session;
    }
    public function onKernelRequest(RequestEvent $event)
    {
// You get the exception object from the received event
        $request = $event->getRequest();
        if($request->get("source")=="external"){
            $this->session->set("requested_url",$request->getPathInfo());
            $this->session->set("requested_url_data",$request->query->all());
            $this->session->set("requested_url_method",$request->getMethod());

        }

    }
}
