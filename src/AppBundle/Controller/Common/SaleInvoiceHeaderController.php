<?php

namespace AppBundle\Controller\Common;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Customer;
use AppBundle\Entity\Transaction\SaleInvoiceHeader;
use AppBundle\Grid\Common\SaleInvoiceHeaderGridType;

/**
 * @Route("/common/sale_invoice_header")
 */
class SaleInvoiceHeaderController extends Controller
{
    /**
     * @Route("/grid", name="common_sale_invoice_header_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     */
    public function gridAction(Request $request)
    {
        $options = array();

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(SaleInvoiceHeader::class);

        if ($request->query->has('mode')) {
            $options['mode'] = $request->query->get('mode');
        }
        if ($request->query->has('customer_id')) {
            $options['customer'] = $em->getRepository(Customer::class)->find($request->query->get('customer_id'));
        }

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(SaleInvoiceHeaderGridType::class, $repository, $request, $options);

        return $this->render('common/sale_invoice_header/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }
}
