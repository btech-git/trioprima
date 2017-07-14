<?php

namespace AppBundle\Controller\Common;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Master\Product;
use AppBundle\Grid\Common\ProductGridType;

/**
 * @Route("/common/product")
 */
class ProductController extends Controller
{
    /**
     * @Route("/grid", name="common_product_grid", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Product::class);

        $grid = $this->get('lib.grid.datagrid');
        $grid->build(ProductGridType::class, $repository, $request, array('em' => $em));

        return $this->render('common/product/grid.html.twig', array(
            'grid' => $grid->createView(),
        ));
    }
}
