<?php

namespace AppBundle\Controller\Transaction;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Transaction\PurchasePaymentHeader;
use AppBundle\Form\Transaction\PurchasePaymentHeaderType;
use AppBundle\Grid\Transaction\PurchasePaymentHeaderGridType;

/**
 * @Route("/transaction/purchase_payment_header")
 */
class PurchasePaymentHeaderController extends Controller
{
    /**
     * @Route("/grid", name="transaction_purchase_payment_header_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(PurchasePaymentHeader::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(PurchasePaymentHeaderGridType::class, $repository, $request);

        return $this->render('transaction/purchase_payment_header/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="transaction_purchase_payment_header_index")
     * @Method("GET")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function indexAction()
    {
        return $this->render('transaction/purchase_payment_header/index.html.twig');
    }

    /**
     * @Route("/new.{_format}", name="transaction_purchase_payment_header_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function newAction(Request $request, $_format = 'html')
    {
        $purchasePaymentHeader = new PurchasePaymentHeader();

        $purchasePaymentService = $this->get('app.transaction.purchase_payment_form');
        $form = $this->createForm(PurchasePaymentHeaderType::class, $purchasePaymentHeader, array(
            'service' => $purchasePaymentService,
            'init' => array('year' => date('y'), 'month' => date('m'), 'staff' => $this->getUser()),
        ));
        $form->handleRequest($request);

        if ($_format === 'html' && $form->isSubmitted() && $form->isValid()) {
            $purchasePaymentService->save($purchasePaymentHeader);

            return $this->redirectToRoute('transaction_purchase_payment_header_show', array('id' => $purchasePaymentHeader->getId()));
        }

        return $this->render('transaction/purchase_payment_header/new.'.$_format.'.twig', array(
            'purchasePaymentHeader' => $purchasePaymentHeader,
            'form' => $form->createView(),
            'purchasePaymentDetailsCount' => 0,
        ));
    }

    /**
     * @Route("/{id}", name="transaction_purchase_payment_header_show")
     * @Method("GET")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function showAction(PurchasePaymentHeader $purchasePaymentHeader)
    {
        return $this->render('transaction/purchase_payment_header/show.html.twig', array(
            'purchasePaymentHeader' => $purchasePaymentHeader,
        ));
    }

    /**
     * @Route("/{id}/edit.{_format}", name="transaction_purchase_payment_header_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function editAction(Request $request, PurchasePaymentHeader $purchasePaymentHeader, $_format = 'html')
    {
        $purchasePaymentDetailsCount = $purchasePaymentHeader->getPurchasePaymentDetails()->count();

        $purchasePaymentService = $this->get('app.transaction.purchase_payment_form');
        $form = $this->createForm(PurchasePaymentHeaderType::class, $purchasePaymentHeader, array(
            'service' => $purchasePaymentService,
            'init' => array('year' => date('y'), 'month' => date('m'), 'staff' => $this->getUser()),
        ));
        $form->handleRequest($request);

        if ($_format === 'html' && $form->isSubmitted() && $form->isValid()) {
            $purchasePaymentService->save($purchasePaymentHeader);

            return $this->redirectToRoute('transaction_purchase_payment_header_show', array('id' => $purchasePaymentHeader->getId()));
        }

        return $this->render('transaction/purchase_payment_header/edit.'.$_format.'.twig', array(
            'purchasePaymentHeader' => $purchasePaymentHeader,
            'form' => $form->createView(),
            'purchasePaymentDetailsCount' => $purchasePaymentDetailsCount,
        ));
    }

    /**
     * @Route("/{id}/delete", name="transaction_purchase_payment_header_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function deleteAction(Request $request, PurchasePaymentHeader $purchasePaymentHeader)
    {
        $purchasePaymentService = $this->get('app.transaction.purchase_payment_form');
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $purchasePaymentService->delete($purchasePaymentHeader);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('transaction_purchase_payment_header_index');
        }

        return $this->render('transaction/purchase_payment_header/delete.html.twig', array(
            'purchasePaymentHeader' => $purchasePaymentHeader,
            'form' => $form->createView(),
        ));
    }
}
