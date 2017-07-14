<?php

namespace AppBundle\Controller\Common;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Supplier;
use AppBundle\Entity\Transaction\PurchaseInvoiceHeader;
use AppBundle\Grid\Common\PurchaseInvoiceHeaderGridType;

/**
 * @Route("/common/purchase_invoice_header")
 */
class PurchaseInvoiceHeaderController extends Controller
{
    /**
     * @Route("/grid", name="common_purchase_invoice_header_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     */
    public function gridAction(Request $request)
    {
        $options = array();

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(PurchaseInvoiceHeader::class);

        if ($request->query->has('mode')) {
            $options['mode'] = $request->query->get('mode');
        }
        if ($request->query->has('supplier_id')) {
            $options['supplier'] = $em->getRepository(Supplier::class)->find($request->query->getInt('supplier_id'));
        }

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(PurchaseInvoiceHeaderGridType::class, $repository, $request, $options);

        return $this->render('common/purchase_invoice_header/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }
}
