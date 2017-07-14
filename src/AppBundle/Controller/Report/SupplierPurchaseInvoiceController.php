<?php

namespace AppBundle\Controller\Report;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Supplier;
use AppBundle\Grid\Report\SupplierPurchaseInvoiceGridType;

/**
 * @Route("/report/supplier_purchase_invoice")
 */
class SupplierPurchaseInvoiceController extends Controller
{
    /**
     * @Route("/grid", name="report_supplier_purchase_invoice_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_REPORT')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Supplier::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(SupplierPurchaseInvoiceGridType::class, $repository, $request);

        return $this->render('report/supplier_purchase_invoice/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="report_supplier_purchase_invoice_index")
     * @Method("GET")
     * @Security("has_role('ROLE_REPORT')")
     */
    public function indexAction()
    {
        return $this->render('report/supplier_purchase_invoice/index.html.twig');
    }

    /**
     * @Route("/export", name="report_supplier_purchase_invoice_export")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_REPORT')")
     */
    public function exportAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Supplier::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(SupplierPurchaseInvoiceGridType::class, $repository, $request);

        $excel = $this->get('phpexcel');
        $excelXmlReader = $this->get('lib.excel.xml_reader');
        $xml = $this->renderView('report/supplier_purchase_invoice/export.xml.twig', array(
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
