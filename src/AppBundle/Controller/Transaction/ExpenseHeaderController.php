<?php

namespace AppBundle\Controller\Transaction;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Transaction\ExpenseHeader;
use AppBundle\Form\Transaction\ExpenseHeaderType;
use AppBundle\Grid\Transaction\ExpenseHeaderGridType;

/**
 * @Route("/transaction/expense_header")
 */
class ExpenseHeaderController extends Controller
{
    /**
     * @Route("/grid", name="transaction_expense_header_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(ExpenseHeader::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(ExpenseHeaderGridType::class, $repository, $request, array('em' => $em));

        return $this->render('transaction/expense_header/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="transaction_expense_header_index")
     * @Method("GET")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function indexAction()
    {
        return $this->render('transaction/expense_header/index.html.twig');
    }

    /**
     * @Route("/new.{_format}", name="transaction_expense_header_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function newAction(Request $request, $_format = 'html')
    {
        $expenseHeader = new ExpenseHeader();

        $expenseService = $this->get('app.transaction.expense_form');
        $form = $this->createForm(ExpenseHeaderType::class, $expenseHeader, array(
            'service' => $expenseService,
            'init' => array('year' => date('y'), 'month' => date('m'), 'staff' => $this->getUser()),
        ));
        $form->handleRequest($request);

        if ($_format === 'html' && $form->isSubmitted() && $form->isValid()) {
            $expenseService->save($expenseHeader);

            return $this->redirectToRoute('transaction_expense_header_show', array('id' => $expenseHeader->getId()));
        }

        return $this->render('transaction/expense_header/new.'.$_format.'.twig', array(
            'expenseHeader' => $expenseHeader,
            'form' => $form->createView(),
            'expenseDetailsCount' => 0,
        ));
    }

    /**
     * @Route("/{id}", name="transaction_expense_header_show")
     * @Method("GET")
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function showAction(ExpenseHeader $expenseHeader)
    {
        return $this->render('transaction/expense_header/show.html.twig', array(
            'expenseHeader' => $expenseHeader,
        ));
    }

    /**
     * @Route("/{id}/edit.{_format}", name="transaction_expense_header_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function editAction(Request $request, ExpenseHeader $expenseHeader, $_format = 'html')
    {
        $expenseDetailsCount = $expenseHeader->getExpenseDetails()->count();

        $expenseService = $this->get('app.transaction.expense_form');
        $form = $this->createForm(ExpenseHeaderType::class, $expenseHeader, array(
            'service' => $expenseService,
            'init' => array('year' => date('y'), 'month' => date('m'), 'staff' => $this->getUser()),
        ));
        $form->handleRequest($request);

        if ($_format === 'html' && $form->isSubmitted() && $form->isValid()) {
            $expenseService->save($expenseHeader);

            return $this->redirectToRoute('transaction_expense_header_show', array('id' => $expenseHeader->getId()));
        }

        return $this->render('transaction/expense_header/edit.'.$_format.'.twig', array(
            'expenseHeader' => $expenseHeader,
            'form' => $form->createView(),
            'expenseDetailsCount' => $expenseDetailsCount,
        ));
    }

    /**
     * @Route("/{id}/delete", name="transaction_expense_header_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_TRANSACTION')")
     */
    public function deleteAction(Request $request, ExpenseHeader $expenseHeader)
    {
        $expenseService = $this->get('app.transaction.expense_form');
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $expenseService->delete($expenseHeader);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('transaction_expense_header_index');
        }

        return $this->render('transaction/expense_header/delete.html.twig', array(
            'expenseHeader' => $expenseHeader,
            'form' => $form->createView(),
        ));
    }
}
