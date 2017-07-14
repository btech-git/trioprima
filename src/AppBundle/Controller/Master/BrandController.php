<?php

namespace AppBundle\Controller\Master;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Brand;
use AppBundle\Form\Master\BrandType;
use AppBundle\Grid\Master\BrandGridType;

/**
 * @Route("/master/brand")
 */
class BrandController extends Controller
{
    /**
     * @Route("/grid", name="master_brand_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Brand::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(BrandGridType::class, $repository, $request);

        return $this->render('master/brand/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="master_brand_index")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function indexAction()
    {
        return $this->render('master/brand/index.html.twig');
    }

    /**
     * @Route("/new", name="master_brand_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function newAction(Request $request)
    {
        $brand = new Brand();
        $form = $this->createForm(BrandType::class, $brand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Brand::class);
            $repository->add($brand);

            return $this->redirectToRoute('master_brand_show', array('id' => $brand->getId()));
        }

        return $this->render('master/brand/new.html.twig', array(
            'brand' => $brand,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}", name="master_brand_show")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function showAction(Brand $brand)
    {
        return $this->render('master/brand/show.html.twig', array(
            'brand' => $brand,
        ));
    }

    /**
     * @Route("/{id}/edit", name="master_brand_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function editAction(Request $request, Brand $brand)
    {
        $form = $this->createForm(BrandType::class, $brand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Brand::class);
            $repository->update($brand);

            return $this->redirectToRoute('master_brand_show', array('id' => $brand->getId()));
        }

        return $this->render('master/brand/edit.html.twig', array(
            'brand' => $brand,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/delete", name="master_brand_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function deleteAction(Request $request, Brand $brand)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository(Brand::class);
                $repository->remove($brand);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('master_brand_index');
        }

        return $this->render('master/brand/delete.html.twig', array(
            'brand' => $brand,
            'form' => $form->createView(),
        ));
    }
}
