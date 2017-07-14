<?php

namespace AppBundle\Controller\Report;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Product;
use AppBundle\Entity\Report\Inventory;
use AppBundle\Grid\Report\ProductStockGridType;

/**
 * @Route("/report/product_stock")
 */
class ProductStockController extends Controller
{
    /**
     * @Route("/grid", name="report_product_stock_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_REPORT')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Product::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(ProductStockGridType::class, $repository, $request, array('em' => $em));

        return $this->render('report/product_stock/grid.html.twig', array(
            'grid' => $grid->createView(),
            'inventoryRepository' => $em->getRepository(Inventory::class),
        ));
    }

    /**
     * @Route("/", name="report_product_stock_index")
     * @Method("GET")
     * @Security("has_role('ROLE_REPORT')")
     */
    public function indexAction()
    {
        return $this->render('report/product_stock/index.html.twig');
    }
}
