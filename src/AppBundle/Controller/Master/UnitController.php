<?php

namespace AppBundle\Controller\Master;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Unit;
use AppBundle\Form\Master\UnitType;
use AppBundle\Grid\Master\UnitGridType;

/**
 * @Route("/master/unit")
 */
class UnitController extends Controller
{
    /**
     * @Route("/grid", name="master_unit_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Unit::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(UnitGridType::class, $repository, $request);

        return $this->render('master/unit/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="master_unit_index")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function indexAction()
    {
        return $this->render('master/unit/index.html.twig');
    }

    /**
     * @Route("/new", name="master_unit_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function newAction(Request $request)
    {
        $unit = new Unit();
        $form = $this->createForm(UnitType::class, $unit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Unit::class);
            $repository->add($unit);

            return $this->redirectToRoute('master_unit_show', array('id' => $unit->getId()));
        }

        return $this->render('master/unit/new.html.twig', array(
            'unit' => $unit,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}", name="master_unit_show")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function showAction(Unit $unit)
    {
        return $this->render('master/unit/show.html.twig', array(
            'unit' => $unit,
        ));
    }

    /**
     * @Route("/{id}/edit", name="master_unit_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function editAction(Request $request, Unit $unit)
    {
        $form = $this->createForm(UnitType::class, $unit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Unit::class);
            $repository->update($unit);

            return $this->redirectToRoute('master_unit_show', array('id' => $unit->getId()));
        }

        return $this->render('master/unit/edit.html.twig', array(
            'unit' => $unit,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/delete", name="master_unit_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function deleteAction(Request $request, Unit $unit)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository(Unit::class);
                $repository->remove($unit);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('master_unit_index');
        }

        return $this->render('master/unit/delete.html.twig', array(
            'unit' => $unit,
            'form' => $form->createView(),
        ));
    }
}
