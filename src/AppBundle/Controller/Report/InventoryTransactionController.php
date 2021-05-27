<?php

namespace AppBundle\Controller\Report;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Product;
use AppBundle\Entity\Report\Inventory;
use AppBundle\Grid\Report\InventoryTransactionGridType;

/**
 * @Route("/report/inventory_transaction")
 */
class InventoryTransactionController extends Controller
{
    /**
     * @Route("/grid", name="report_inventory_transaction_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_REPORT')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Product::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(InventoryTransactionGridType::class, $repository, $request, array('em' => $em));

        return $this->render('report/inventory_transaction/grid.html.twig', array(
            'grid' => $grid->createView(),
            'inventoryRepository' => $em->getRepository(Inventory::class),
        ));
    }

    /**
     * @Route("/", name="report_inventory_transaction_index")
     * @Method("GET")
     * @Security("has_role('ROLE_REPORT')")
     */
    public function indexAction()
    {
        return $this->render('report/inventory_transaction/index.html.twig');
    }
    
    /**
     * @Route("/export", name="report_inventory_transaction_export")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_REPORT')")
     */
    public function exportAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Product::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(InventoryTransactionGridType::class, $repository, $request, array('em' => $em));

        $excel = $this->get('phpexcel');
        $excelXmlReader = $this->get('lib.excel.xml_reader');
        $xml = $this->renderView('report/inventory_transaction/export.xml.twig', array(
            'grid' => $grid->createView(),
            'inventoryRepository' => $em->getRepository(Inventory::class),
        ));
        $excelObject = $excelXmlReader->load($xml);
        $writer = $excel->createWriter($excelObject, 'Excel5');
        $response = $excel->createStreamedResponse($writer);

        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'report.xls');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}
