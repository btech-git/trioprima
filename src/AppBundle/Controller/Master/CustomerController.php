<?php

namespace AppBundle\Controller\Master;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Customer;
use AppBundle\Form\Master\CustomerType;
use AppBundle\Grid\Master\CustomerGridType;

/**
 * @Route("/master/customer")
 */
class CustomerController extends Controller
{
    /**
     * @Route("/grid", name="master_customer_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Customer::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(CustomerGridType::class, $repository, $request);

        return $this->render('master/customer/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="master_customer_index")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function indexAction()
    {
        return $this->render('master/customer/index.html.twig');
    }

    /**
     * @Route("/new", name="master_customer_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function newAction(Request $request)
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Customer::class);
            $repository->add($customer);

            return $this->redirectToRoute('master_customer_show', array('id' => $customer->getId()));
        }

        return $this->render('master/customer/new.html.twig', array(
            'customer' => $customer,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}", name="master_customer_show")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function showAction(Customer $customer)
    {
        return $this->render('master/customer/show.html.twig', array(
            'customer' => $customer,
        ));
    }

    /**
     * @Route("/{id}/edit", name="master_customer_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function editAction(Request $request, Customer $customer)
    {
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Customer::class);
            $repository->update($customer);

            return $this->redirectToRoute('master_customer_show', array('id' => $customer->getId()));
        }

        return $this->render('master/customer/edit.html.twig', array(
            'customer' => $customer,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/delete", name="master_customer_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function deleteAction(Request $request, Customer $customer)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository(Customer::class);
                $repository->remove($customer);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('master_customer_index');
        }

        return $this->render('master/customer/delete.html.twig', array(
            'customer' => $customer,
            'form' => $form->createView(),
        ));
    }
}
