<?php

namespace AppBundle\Controller\Master;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Product;
use AppBundle\Form\Master\ProductType;
use AppBundle\Grid\Master\ProductGridType;

/**
 * @Route("/master/product")
 */
class ProductController extends Controller
{
    /**
     * @Route("/grid", name="master_product_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Product::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(ProductGridType::class, $repository, $request);

        return $this->render('master/product/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="master_product_index")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function indexAction()
    {
        return $this->render('master/product/index.html.twig');
    }

    /**
     * @Route("/new", name="master_product_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function newAction(Request $request)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Product::class);
            $repository->add($product);

            return $this->redirectToRoute('master_product_show', array('id' => $product->getId()));
        }

        return $this->render('master/product/new.html.twig', array(
            'product' => $product,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}", name="master_product_show")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function showAction(Product $product)
    {
        return $this->render('master/product/show.html.twig', array(
            'product' => $product,
        ));
    }

    /**
     * @Route("/{id}/edit", name="master_product_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function editAction(Request $request, Product $product)
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Product::class);
            $repository->update($product);

            return $this->redirectToRoute('master_product_show', array('id' => $product->getId()));
        }

        return $this->render('master/product/edit.html.twig', array(
            'product' => $product,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/delete", name="master_product_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function deleteAction(Request $request, Product $product)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository(Product::class);
                $repository->remove($product);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('master_product_index');
        }

        return $this->render('master/product/delete.html.twig', array(
            'product' => $product,
            'form' => $form->createView(),
        ));
    }
}
