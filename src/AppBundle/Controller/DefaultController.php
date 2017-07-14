<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ));
    }

    /**
     * @Route("/menu/{module}", name="menu")
     */
    public function menuAction(Request $request, $module)
    {
        if (in_array($module, array('dashboard', 'admin', 'master', 'transaction', 'report'))) {
            return $this->render('default/menu/'.$module.'.html.twig');
        } else {
            return $this->redirectToRoute('homepage');
        }
    }
}
