<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Admin\Staff;
use AppBundle\Form\Admin\StaffType;
use AppBundle\Grid\Admin\StaffGridType;

/**
 * @Route("/admin/staff")
 */
class StaffController extends Controller
{
    /**
     * @Route("/grid", name="admin_staff_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Staff::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(StaffGridType::class, $repository, $request);

        return $this->render('admin/staff/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="admin_staff_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('admin/staff/index.html.twig');
    }

    /**
     * @Route("/new", name="admin_staff_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $staff = new Staff();
        $form = $this->createForm(StaffType::class, $staff, array('encoder' => $this->get('security.password_encoder')));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Staff::class);
            $repository->add($staff);

            return $this->redirectToRoute('admin_staff_show', array('id' => $staff->getId()));
        }

        return $this->render('admin/staff/new.html.twig', array(
            'staff' => $staff,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}", name="admin_staff_show")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function showAction(Staff $staff)
    {
        return $this->render('admin/staff/show.html.twig', array(
            'staff' => $staff,
        ));
    }

    /**
     * @Route("/{id}/edit", name="admin_staff_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Staff $staff)
    {
        $form = $this->createForm(StaffType::class, $staff, array('encoder' => $this->get('security.password_encoder')));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Staff::class);
            $repository->update($staff);

            return $this->redirectToRoute('admin_staff_show', array('id' => $staff->getId()));
        }

        return $this->render('admin/staff/edit.html.twig', array(
            'staff' => $staff,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/delete", name="admin_staff_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Staff $staff)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository(Staff::class);
                $repository->remove($staff);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('admin_staff_index');
        }

        return $this->render('admin/staff/delete.html.twig', array(
            'staff' => $staff,
            'form' => $form->createView(),
        ));
    }
}
