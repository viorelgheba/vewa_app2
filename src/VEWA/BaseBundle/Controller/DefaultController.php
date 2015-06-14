<?php

namespace VEWA\BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $wishlistService = $this->get('vewa_base.wishlist');
        $products = $wishlistService->getLastEntries();
        $templateParams = array(
            'products' => $products
        );
        return $this->render('VEWABaseBundle::index.html.twig', $templateParams);
    }
}
