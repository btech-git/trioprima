<?php

namespace AppBundle\Controller\Master;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\TaxLiteral;
use AppBundle\Form\Master\TaxLiteralType;
use AppBundle\Grid\Master\TaxLiteralGridType;

/**
 * @Route("/master/tax_literal")
 */
class TaxLiteralController extends Controller
{
    /**
     * @Route("/grid", name="master_tax_literal_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(TaxLiteral::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(TaxLiteralGridType::class, $repository, $request);

        return $this->render('master/tax_literal/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }

    /**
     * @Route("/", name="master_tax_literal_index")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function indexAction()
    {
        return $this->render('master/tax_literal/index.html.twig');
    }

    /**
     * @Route("/new", name="master_tax_literal_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function newAction(Request $request)
    {
        $taxLiteral = new TaxLiteral();
        $form = $this->createForm(TaxLiteralType::class, $taxLiteral);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(TaxLiteral::class);
            $repository->add($taxLiteral);

            return $this->redirectToRoute('master_tax_literal_show', array('id' => $taxLiteral->getId()));
        }

        return $this->render('master/tax_literal/new.html.twig', array(
            'taxLiteral' => $taxLiteral,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}", name="master_tax_literal_show")
     * @Method("GET")
     * @Security("has_role('ROLE_MASTER')")
     */
    public function showAction(TaxLiteral $taxLiteral)
    {
        return $this->render('master/tax_literal/show.html.twig', array(
            'taxLiteral' => $taxLiteral,
        ));
    }

    /**
     * @Route("/{id}/edit", name="master_tax_literal_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function editAction(Request $request, TaxLiteral $taxLiteral)
    {
        $form = $this->createForm(TaxLiteralType::class, $taxLiteral);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(TaxLiteral::class);
            $repository->update($taxLiteral);

            return $this->redirectToRoute('master_tax_literal_show', array('id' => $taxLiteral->getId()));
        }

        return $this->render('master/tax_literal/edit.html.twig', array(
            'taxLiteral' => $taxLiteral,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/delete", name="master_tax_literal_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_MASTER')")
     */
    public function deleteAction(Request $request, TaxLiteral $taxLiteral)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository(TaxLiteral::class);
                $repository->remove($taxLiteral);

                $this->addFlash('success', array('title' => 'Success!', 'message' => 'The record was deleted successfully.'));
            } else {
                $this->addFlash('danger', array('title' => 'Error!', 'message' => 'Failed to delete the record.'));
            }

            return $this->redirectToRoute('master_tax_literal_index');
        }

        return $this->render('master/tax_literal/delete.html.twig', array(
            'taxLiteral' => $taxLiteral,
            'form' => $form->createView(),
        ));
    }
}
