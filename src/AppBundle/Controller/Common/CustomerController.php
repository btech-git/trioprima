<?php

namespace AppBundle\Controller\Common;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Customer;
use AppBundle\Grid\Common\CustomerGridType;

/**
 * @Route("/common/customer")
 */
class CustomerController extends Controller
{
    /**
     * @Route("/grid", name="common_customer_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Customer::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(CustomerGridType::class, $repository, $request);

        return $this->render('common/customer/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }
}
