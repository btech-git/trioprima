<?php

namespace AppBundle\Controller\Report;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Customer;
use AppBundle\Grid\Report\ReceivableSaleGridType;
use AppBundle\Grid\Report\CustomerSaleInvoiceGridType;

/**
 * @Route("/report/receivable_sale")
 */
class ReceivableSaleController extends Controller
{
    /**
     * @Route("/grid", name="report_receivable_sale_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_REPORT')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Customer::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(ReceivableSaleGridType::class, $repository, $request);

        return $this->render('report/receivable_sale/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="report_receivable_sale_index")
     * @Method("GET")
     * @Security("has_role('ROLE_REPORT')")
     */
    public function indexAction()
    {
        return $this->render('report/receivable_sale/index.html.twig');
    }

    /**
     * @Route("/export", name="report_receivable_sale_export")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_REPORT')")
     */
    public function exportAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Customer::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(CustomerSaleInvoiceGridType::class, $repository, $request);

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
    }
}
