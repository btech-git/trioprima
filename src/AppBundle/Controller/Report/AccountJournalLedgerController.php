<?php

namespace AppBundle\Controller\Report;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Account;
use AppBundle\Grid\Report\AccountJournalLedgerGridType;

/**
 * @Route("/report/account_journal_ledger")
 */
class AccountJournalLedgerController extends Controller
{
    /**
     * @Route("/grid", name="report_account_journal_ledger_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_REPORT')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Account::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(AccountJournalLedgerGridType::class, $repository, $request);

        $accountJournalLedgerService = $this->get('app.report.account_journal_ledger_summary');
        $dataGridView = $grid->createView();
        $beginningBalanceData = $accountJournalLedgerService->getBeginningBalanceData($dataGridView);

        return $this->render('report/account_journal_ledger/grid.html.twig', array(
            'grid' => $dataGridView,
            'beginningBalanceData' => $beginningBalanceData,
        ));
    }

    /**
     * @Route("/", name="report_account_journal_ledger_index")
     * @Method("GET")
     * @Security("has_role('ROLE_REPORT')")
     */
    public function indexAction()
    {
        return $this->render('report/account_journal_ledger/index.html.twig');
    }

    /**
     * @Route("/export", name="report_account_journal_ledger_export")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_REPORT')")
     */
    /*
    public function exportAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(VehicleModel::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(VehicleModelSaleInvoiceGridType::class, $repository, $request);

        $excel = $this->get('phpexcel');
        $excelXmlReader = $this->get('lib.excel.xml_reader');
        $xml = $this->renderView('report/customer_sale_invoice/export.xml.twig', array(
            'grid' => $grid->createView(),
        ));
        $excelObject = $excelXmlReader->load($xml);
        $writer = $excel->createWriter($excelObject, 'Excel5');
        $response = $excel->createStreamedResponse($writer);

        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'report.xls');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }*/
}
