<?php

namespace AppBundle\Controller\Transaction;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Transaction\SaleInvoiceHeader;
use AppBundle\Form\Transaction\SaleInvoiceHeaderType;
use AppBundle\Grid\Transaction\SaleInvoiceHeaderGridType;

/**
 * @Route("/transaction/sale_invoice_header")
 */
class SaleInvoiceHeaderController extends Controller
{
    /**
     * @Route("/grid", name="transaction_sale_invoice_header_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(SaleInvoiceHeader::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(SaleInvoiceHeaderGridType::class, $repository, $request);

        return $this->render('transaction/sale_invoice_header/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="transaction_sale_invoice_header_index")
     * @Method("GET")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function indexAction()
    {
        return $this->render('transaction/sale_invoice_header/index.html.twig');
    }

    /**
     * @Route("/new.{_format}", name="transaction_sale_invoice_header_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function newAction(Request $request, $_format = 'html')
    {
        $saleInvoiceHeader = new SaleInvoiceHeader();

        $saleInvoiceService = $this->get('app.transaction.sale_invoice_form');
        $form = $this->createForm(SaleInvoiceHeaderType::class, $saleInvoiceHeader, array(
            'service' => $saleInvoiceService,
            'init' => array('year' => date('y'), 'month' => date('m'), 'taxInvoiceCode' => $this->get('app.master.tax_literal_repository')->find(1)->getCode(), 'staff' => $this->getUser()),
        ));
        $form->handleRequest($request);

        if ($_format === 'html' && $form->isSubmitted() && $form->isValid()) {
            $saleInvoiceService->save($saleInvoiceHeader);

            return $this->redirectToRoute('transaction_sale_invoice_header_show', array('id' => $saleInvoiceHeader->getId()));
        }

        return $this->render('transaction/sale_invoice_header/new.'.$_format.'.twig', array(
            'saleInvoiceHeader' => $saleInvoiceHeader,
            'form' => $form->createView(),
            'saleInvoiceDetailsCount' => 0,
        ));
    }

    /**
     * @Route("/{id}", name="transaction_sale_invoice_header_show")
     * @Method("GET")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function showAction(SaleInvoiceHeader $saleInvoiceHeader)
    {
        return $this->render('transaction/sale_invoice_header/show.html.twig', array(
            'saleInvoiceHeader' => $saleInvoiceHeader,
        ));
    }

    /**
     * @Route("/{id}/edit.{_format}", name="transaction_sale_invoice_header_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function editAction(Request $request, SaleInvoiceHeader $saleInvoiceHeader, $_format = 'html')
    {
        $saleInvoiceDetailsCount = $saleInvoiceHeader->getSaleInvoiceDetails()->count();

        $saleInvoiceService = $this->get('app.transaction.sale_invoice_form');
        $form = $this->createForm(SaleInvoiceHeaderType::class, $saleInvoiceHeader, array(
            'service' => $saleInvoiceService,
            'init' => array('year' => date('y'), 'month' => date('m'), 'taxInvoiceCode' => $this->get('app.master.tax_literal_repository')->find(1)->getCode(), 'staff' => $this->getUser()),
        ));
        $form->handleRequest($request);

        if ($_format === 'html' && $form->isSubmitted() && $form->isValid()) {
            $saleInvoiceService->save($saleInvoiceHeader);

            return $this->redirectToRoute('transaction_sale_invoice_header_show', array('id' => $saleInvoiceHeader->getId()));
        }

        return $this->render('transaction/sale_invoice_header/edit.'.$_format.'.twig', array(
            'saleInvoiceHeader' => $saleInvoiceHeader,
            'form' => $form->createView(),
            'saleInvoiceDetailsCount' => $saleInvoiceDetailsCount,
        ));
    }

    /**
     * @Route("/{id}/delete", name="transaction_sale_invoice_header_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function deleteAction(Request $request, SaleInvoiceHeader $saleInvoiceHeader)
    {
        $saleInvoiceService = $this->get('app.transaction.sale_invoice_form');
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid() && $saleInvoiceService->isValidForDelete($saleInvoiceHeader)) {
                $saleInvoiceService->delete($saleInvoiceHeader);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('transaction_sale_invoice_header_index');
        }

        return $this->render('transaction/sale_invoice_header/delete.html.twig', array(
            'saleInvoiceHeader' => $saleInvoiceHeader,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/{type}/memo", name="transaction_sale_invoice_header_memo")
     * @Method("GET")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function memoAction(Request $request, SaleInvoiceHeader $saleInvoiceHeader, $type)
    {
        $show = $request->query->getBoolean('show', false);

        return $this->render('transaction/sale_invoice_header/memo_'.$type.'.html.twig', array(
            'saleInvoiceHeader' => $saleInvoiceHeader,
            'show' => $show,
        ));
    }

    /**
     * @Route("/export", name="transaction_sale_invoice_header_export")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function exportAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(SaleInvoiceHeader::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(SaleInvoiceHeaderGridType::class, $repository, $request, array('em' => $em));

        $excel = $this->get('phpexcel');
        $excelXmlReader = $this->get('lib.excel.xml_reader');
        $xml = $this->renderView('transaction/sale_invoice_header/export.xml.twig', array(
            'grid' => $grid->createView(),
        ));
        $excelObject = $excelXmlReader->load($xml);
        $writer = $excel->createWriter($excelObject, 'CSV');
        $response = $excel->createStreamedResponse($writer);

        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'eFaktur.csv');
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}
