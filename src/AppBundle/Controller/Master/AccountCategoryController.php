<?php

namespace AppBundle\Controller\Master;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\AccountCategory;
use AppBundle\Form\Master\AccountCategoryType;
use AppBundle\Grid\Master\AccountCategoryGridType;

/**
 * @Route("/master/account_category")
 */
class AccountCategoryController extends Controller
{
    /**
     * @Route("/grid", name="master_account_category_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(AccountCategory::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(AccountCategoryGridType::class, $repository, $request);

        return $this->render('master/account_category/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="master_account_category_index")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function indexAction()
    {
        return $this->render('master/account_category/index.html.twig');
    }

    /**
     * @Route("/new", name="master_account_category_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function newAction(Request $request)
    {
        $accountCategory = new AccountCategory();
        $form = $this->createForm(AccountCategoryType::class, $accountCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(AccountCategory::class);
            $repository->add($accountCategory);

            return $this->redirectToRoute('master_account_category_show', array('id' => $accountCategory->getId()));
        }

        return $this->render('master/account_category/new.html.twig', array(
            'accountCategory' => $accountCategory,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}", name="master_account_category_show")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function showAction(AccountCategory $accountCategory)
    {
        return $this->render('master/account_category/show.html.twig', array(
            'accountCategory' => $accountCategory,
        ));
    }

    /**
     * @Route("/{id}/edit", name="master_account_category_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function editAction(Request $request, AccountCategory $accountCategory)
    {
        $form = $this->createForm(AccountCategoryType::class, $accountCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(AccountCategory::class);
            $repository->update($accountCategory);

            return $this->redirectToRoute('master_account_category_show', array('id' => $accountCategory->getId()));
        }

        return $this->render('master/account_category/edit.html.twig', array(
            'accountCategory' => $accountCategory,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/delete", name="master_account_category_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function deleteAction(Request $request, AccountCategory $accountCategory)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository(AccountCategory::class);
                $repository->remove($accountCategory);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('master_account_category_index');
        }

        return $this->render('master/account_category/delete.html.twig', array(
            'accountCategory' => $accountCategory,
            'form' => $form->createView(),
        ));
    }
}
