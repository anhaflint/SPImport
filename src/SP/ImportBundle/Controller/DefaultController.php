<?php

namespace SP\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SPImportBundle:Default:index.html.twig', array());
    }
}
