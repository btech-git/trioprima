<?php

namespace AppBundle\Controller\Transaction;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Transaction\SaleReceiptHeader;
use AppBundle\Form\Transaction\SaleReceiptHeaderType;
use AppBundle\Grid\Transaction\SaleReceiptHeaderGridType;

/**
 * @Route("/transaction/sale_receipt_header")
 */
class SaleReceiptHeaderController extends Controller
{
    /**
     * @Route("/grid", name="transaction_sale_receipt_header_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(SaleReceiptHeader::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(SaleReceiptHeaderGridType::class, $repository, $request);

        return $this->render('transaction/sale_receipt_header/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="transaction_sale_receipt_header_index")
     * @Method("GET")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function indexAction()
    {
        return $this->render('transaction/sale_receipt_header/index.html.twig');
    }

    /**
     * @Route("/new.{_format}", name="transaction_sale_receipt_header_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function newAction(Request $request, $_format = 'html')
    {
        $saleReceiptHeader = new SaleReceiptHeader();

        $saleReceiptService = $this->get('app.transaction.sale_receipt_form');
        $form = $this->createForm(SaleReceiptHeaderType::class, $saleReceiptHeader, array(
            'service' => $saleReceiptService,
            'init' => array('year' => date('y'), 'month' => date('m'), 'staff' => $this->getUser()),
        ));
        $form->handleRequest($request);

        if ($_format === 'html' && $form->isSubmitted() && $form->isValid()) {
            $saleReceiptService->save($saleReceiptHeader);

            return $this->redirectToRoute('transaction_sale_receipt_header_show', array('id' => $saleReceiptHeader->getId()));
        }

        return $this->render('transaction/sale_receipt_header/new.'.$_format.'.twig', array(
            'saleReceiptHeader' => $saleReceiptHeader,
            'form' => $form->createView(),
            'saleReceiptDetailsCount' => 0,
        ));
    }

    /**
     * @Route("/{id}", name="transaction_sale_receipt_header_show")
     * @Method("GET")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function showAction(SaleReceiptHeader $saleReceiptHeader)
    {
        return $this->render('transaction/sale_receipt_header/show.html.twig', array(
            'saleReceiptHeader' => $saleReceiptHeader,
        ));
    }

    /**
     * @Route("/{id}/edit.{_format}", name="transaction_sale_receipt_header_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function editAction(Request $request, SaleReceiptHeader $saleReceiptHeader, $_format = 'html')
    {
        $saleReceiptDetailsCount = $saleReceiptHeader->getSaleReceiptDetails()->count();

        $saleReceiptService = $this->get('app.transaction.sale_receipt_form');
        $form = $this->createForm(SaleReceiptHeaderType::class, $saleReceiptHeader, array(
            'service' => $saleReceiptService,
            'init' => array('year' => date('y'), 'month' => date('m'), 'staff' => $this->getUser()),
        ));
        $form->handleRequest($request);

        if ($_format === 'html' && $form->isSubmitted() && $form->isValid()) {
            $saleReceiptService->save($saleReceiptHeader);

            return $this->redirectToRoute('transaction_sale_receipt_header_show', array('id' => $saleReceiptHeader->getId()));
        }

        return $this->render('transaction/sale_receipt_header/edit.'.$_format.'.twig', array(
            'saleReceiptHeader' => $saleReceiptHeader,
            'form' => $form->createView(),
            'saleReceiptDetailsCount' => $saleReceiptDetailsCount,
        ));
    }

    /**
     * @Route("/{id}/delete", name="transaction_sale_receipt_header_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function deleteAction(Request $request, SaleReceiptHeader $saleReceiptHeader)
    {
        $saleReceiptService = $this->get('app.transaction.sale_receipt_form');
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $saleReceiptService->delete($saleReceiptHeader);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('transaction_sale_receipt_header_index');
        }

        return $this->render('transaction/sale_receipt_header/delete.html.twig', array(
            'saleReceiptHeader' => $saleReceiptHeader,
            'form' => $form->createView(),
        ));
    }
}
