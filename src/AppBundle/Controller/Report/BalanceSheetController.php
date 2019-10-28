<?php

namespace AppBundle\Controller\Report;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Account;
use AppBundle\Grid\Report\BalanceSheetGridType;

/**
 * @Route("/report/balance_sheet")
 */
class BalanceSheetController extends Controller
{
    /**
     * @Route("/grid", name="report_balance_sheet_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_REPORT')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Account::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(BalanceSheetGridType::class, $repository, $request);

        return $this->render('report/balance_sheet/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="report_balance_sheet_index")
     * @Method("GET")
     * @Security("has_role('ROLE_REPORT')")
     */
    public function indexAction()
    {
        return $this->render('report/balance_sheet/index.html.twig');
    }
}
