<?php

namespace AppBundle\Controller\Master;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\PaymentType;
use AppBundle\Form\Master\PaymentTypeType;
use AppBundle\Grid\Master\PaymentTypeGridType;

/**
 * @Route("/master/payment_type")
 */
class PaymentTypeController extends Controller
{
    /**
     * @Route("/grid", name="master_payment_type_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(PaymentType::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(PaymentTypeGridType::class, $repository, $request);

        return $this->render('master/payment_type/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="master_payment_type_index")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function indexAction()
    {
        return $this->render('master/payment_type/index.html.twig');
    }

    /**
     * @Route("/new", name="master_payment_type_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function newAction(Request $request)
    {
        $paymentType = new PaymentType();
        $form = $this->createForm(PaymentTypeType::class, $paymentType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(PaymentType::class);
            $repository->add($paymentType);

            return $this->redirectToRoute('master_payment_type_show', array('id' => $paymentType->getId()));
        }

        return $this->render('master/payment_type/new.html.twig', array(
            'paymentType' => $paymentType,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}", name="master_payment_type_show")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function showAction(PaymentType $paymentType)
    {
        return $this->render('master/payment_type/show.html.twig', array(
            'paymentType' => $paymentType,
        ));
    }

    /**
     * @Route("/{id}/edit", name="master_payment_type_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function editAction(Request $request, PaymentType $paymentType)
    {
        $form = $this->createForm(PaymentTypeType::class, $paymentType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(PaymentType::class);
            $repository->update($paymentType);

            return $this->redirectToRoute('master_payment_type_show', array('id' => $paymentType->getId()));
        }

        return $this->render('master/payment_type/edit.html.twig', array(
            'paymentType' => $paymentType,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/delete", name="master_payment_type_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function deleteAction(Request $request, PaymentType $paymentType)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository(PaymentType::class);
                $repository->remove($paymentType);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('master_payment_type_index');
        }

        return $this->render('master/payment_type/delete.html.twig', array(
            'paymentType' => $paymentType,
            'form' => $form->createView(),
        ));
    }
}
