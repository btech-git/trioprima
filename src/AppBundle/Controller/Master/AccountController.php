<?php

namespace AppBundle\Controller\Master;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Account;
use AppBundle\Form\Master\AccountType;
use AppBundle\Grid\Master\AccountGridType;

/**
 * @Route("/master/account")
 */
class AccountController extends Controller
{
    /**
     * @Route("/grid", name="master_account_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Account::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(AccountGridType::class, $repository, $request);

        return $this->render('master/account/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="master_account_index")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function indexAction()
    {
        return $this->render('master/account/index.html.twig');
    }

    /**
     * @Route("/new", name="master_account_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function newAction(Request $request)
    {
        $account = new Account();
        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Account::class);
            $repository->add($account);

            return $this->redirectToRoute('master_account_show', array('id' => $account->getId()));
        }

        return $this->render('master/account/new.html.twig', array(
            'account' => $account,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}", name="master_account_show")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function showAction(Account $account)
    {
        return $this->render('master/account/show.html.twig', array(
            'account' => $account,
        ));
    }

    /**
     * @Route("/{id}/edit", name="master_account_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function editAction(Request $request, Account $account)
    {
        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Account::class);
            $repository->update($account);

            return $this->redirectToRoute('master_account_show', array('id' => $account->getId()));
        }

        return $this->render('master/account/edit.html.twig', array(
            'account' => $account,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/delete", name="master_account_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function deleteAction(Request $request, Account $account)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository(Account::class);
                $repository->remove($account);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('master_account_index');
        }

        return $this->render('master/account/delete.html.twig', array(
            'account' => $account,
            'form' => $form->createView(),
        ));
    }
}
