<?php

namespace AppBundle\Controller\Transaction;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Transaction\PurchaseReturnHeader;
use AppBundle\Form\Transaction\PurchaseReturnHeaderType;
use AppBundle\Grid\Transaction\PurchaseReturnHeaderGridType;

/**
 * @Route("/transaction/purchase_return_header")
 */
class PurchaseReturnHeaderController extends Controller
{
    /**
     * @Route("/grid", name="transaction_purchase_return_header_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(PurchaseReturnHeader::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(PurchaseReturnHeaderGridType::class, $repository, $request);

        return $this->render('transaction/purchase_return_header/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="transaction_purchase_return_header_index")
     * @Method("GET")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function indexAction()
    {
        return $this->render('transaction/purchase_return_header/index.html.twig');
    }

    /**
     * @Route("/new.{_format}", name="transaction_purchase_return_header_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function newAction(Request $request, $_format = 'html')
    {
        $purchaseReturnHeader = new PurchaseReturnHeader();

        $purchaseReturnService = $this->get('app.transaction.purchase_return_form');
        $form = $this->createForm(PurchaseReturnHeaderType::class, $purchaseReturnHeader, array(
            'service' => $purchaseReturnService,
            'init' => array('year' => date('y'), 'month' => date('m'), 'staff' => $this->getUser()),
        ));
        $form->handleRequest($request);

        if ($_format === 'html' && $form->isSubmitted() && $form->isValid()) {
            $purchaseReturnService->save($purchaseReturnHeader);

            return $this->redirectToRoute('transaction_purchase_return_header_show', array('id' => $purchaseReturnHeader->getId()));
        }

        return $this->render('transaction/purchase_return_header/new.'.$_format.'.twig', array(
            'purchaseReturnHeader' => $purchaseReturnHeader,
            'form' => $form->createView(),
            'purchaseReturnDetailsCount' => 0,
        ));
    }

    /**
     * @Route("/{id}", name="transaction_purchase_return_header_show")
     * @Method("GET")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function showAction(PurchaseReturnHeader $purchaseReturnHeader)
    {
        return $this->render('transaction/purchase_return_header/show.html.twig', array(
            'purchaseReturnHeader' => $purchaseReturnHeader,
        ));
    }

    /**
     * @Route("/{id}/edit.{_format}", name="transaction_purchase_return_header_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function editAction(Request $request, PurchaseReturnHeader $purchaseReturnHeader, $_format = 'html')
    {
        $purchaseReturnDetailsCount = $purchaseReturnHeader->getPurchaseReturnDetails()->count();

        $purchaseReturnService = $this->get('app.transaction.purchase_return_form');
        $form = $this->createForm(PurchaseReturnHeaderType::class, $purchaseReturnHeader, array(
            'service' => $purchaseReturnService,
            'init' => array('year' => date('y'), 'month' => date('m'), 'staff' => $this->getUser()),
        ));
        $form->handleRequest($request);

        if ($_format === 'html' && $form->isSubmitted() && $form->isValid()) {
            $purchaseReturnService->save($purchaseReturnHeader);

            return $this->redirectToRoute('transaction_purchase_return_header_show', array('id' => $purchaseReturnHeader->getId()));
        }

        return $this->render('transaction/purchase_return_header/edit.'.$_format.'.twig', array(
            'purchaseReturnHeader' => $purchaseReturnHeader,
            'form' => $form->createView(),
            'purchaseReturnDetailsCount' => $purchaseReturnDetailsCount,
        ));
    }

    /**
     * @Route("/{id}/delete", name="transaction_purchase_return_header_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function deleteAction(Request $request, PurchaseReturnHeader $purchaseReturnHeader)
    {
        $purchaseReturnService = $this->get('app.transaction.purchase_return_form');
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $purchaseReturnService->delete($purchaseReturnHeader);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('transaction_purchase_return_header_index');
        }

        return $this->render('transaction/purchase_return_header/delete.html.twig', array(
            'purchaseReturnHeader' => $purchaseReturnHeader,
            'form' => $form->createView(),
        ));
    }
}
