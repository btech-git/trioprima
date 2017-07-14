<?php

namespace AppBundle\Controller\Master;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Supplier;
use AppBundle\Form\Master\SupplierType;
use AppBundle\Grid\Master\SupplierGridType;

/**
 * @Route("/master/supplier")
 */
class SupplierController extends Controller
{
    /**
     * @Route("/grid", name="master_supplier_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Supplier::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(SupplierGridType::class, $repository, $request);

        return $this->render('master/supplier/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="master_supplier_index")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function indexAction()
    {
        return $this->render('master/supplier/index.html.twig');
    }

    /**
     * @Route("/new", name="master_supplier_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function newAction(Request $request)
    {
        $supplier = new Supplier();
        $form = $this->createForm(SupplierType::class, $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Supplier::class);
            $repository->add($supplier);

            return $this->redirectToRoute('master_supplier_show', array('id' => $supplier->getId()));
        }

        return $this->render('master/supplier/new.html.twig', array(
            'supplier' => $supplier,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}", name="master_supplier_show")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function showAction(Supplier $supplier)
    {
        return $this->render('master/supplier/show.html.twig', array(
            'supplier' => $supplier,
        ));
    }

    /**
     * @Route("/{id}/edit", name="master_supplier_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function editAction(Request $request, Supplier $supplier)
    {
        $form = $this->createForm(SupplierType::class, $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Supplier::class);
            $repository->update($supplier);

            return $this->redirectToRoute('master_supplier_show', array('id' => $supplier->getId()));
        }

        return $this->render('master/supplier/edit.html.twig', array(
            'supplier' => $supplier,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/delete", name="master_supplier_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function deleteAction(Request $request, Supplier $supplier)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository(Supplier::class);
                $repository->remove($supplier);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('master_supplier_index');
        }

        return $this->render('master/supplier/delete.html.twig', array(
            'supplier' => $supplier,
            'form' => $form->createView(),
        ));
    }
}
