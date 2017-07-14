<?php

namespace AppBundle\Controller\Common;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Account;
use AppBundle\Grid\Common\AccountGridType;

/**
 * @Route("/common/account")
 */
class AccountController extends Controller
{
    /**
     * @Route("/grid", name="common_account_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Account::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(AccountGridType::class, $repository, $request, array('em' => $em));

        return $this->render('common/account/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }
}
