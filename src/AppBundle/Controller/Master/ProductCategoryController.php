<?php

namespace AppBundle\Controller\Master;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\ProductCategory;
use AppBundle\Form\Master\ProductCategoryType;
use AppBundle\Grid\Master\ProductCategoryGridType;

/**
 * @Route("/master/product_category")
 */
class ProductCategoryController extends Controller
{
    /**
     * @Route("/grid", name="master_product_category_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(ProductCategory::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(ProductCategoryGridType::class, $repository, $request);

        return $this->render('master/product_category/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="master_product_category_index")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function indexAction()
    {
        return $this->render('master/product_category/index.html.twig');
    }

    /**
     * @Route("/new", name="master_product_category_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function newAction(Request $request)
    {
        $productCategory = new ProductCategory();
        $form = $this->createForm(ProductCategoryType::class, $productCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(ProductCategory::class);
            $repository->add($productCategory);

            return $this->redirectToRoute('master_product_category_show', array('id' => $productCategory->getId()));
        }

        return $this->render('master/product_category/new.html.twig', array(
            'productCategory' => $productCategory,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}", name="master_product_category_show")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function showAction(ProductCategory $productCategory)
    {
        return $this->render('master/product_category/show.html.twig', array(
            'productCategory' => $productCategory,
        ));
    }

    /**
     * @Route("/{id}/edit", name="master_product_category_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function editAction(Request $request, ProductCategory $productCategory)
    {
        $form = $this->createForm(ProductCategoryType::class, $productCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(ProductCategory::class);
            $repository->update($productCategory);

            return $this->redirectToRoute('master_product_category_show', array('id' => $productCategory->getId()));
        }

        return $this->render('master/product_category/edit.html.twig', array(
            'productCategory' => $productCategory,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/delete", name="master_product_category_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function deleteAction(Request $request, ProductCategory $productCategory)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository(ProductCategory::class);
                $repository->remove($productCategory);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('master_product_category_index');
        }

        return $this->render('master/product_category/delete.html.twig', array(
            'productCategory' => $productCategory,
            'form' => $form->createView(),
        ));
    }
}
