<?php

namespace VEWA\BaseBundle\Service;

use Doctrine\ORM\Query;
use Sensio\Bundle\BuzzBundle\SensioBuzzBundle;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bridge\Monolog\Logger;
use VEWA\BaseBundle\Entity\Device;
use VEWA\BaseBundle\Entity\Product;
use VEWA\BaseBundle\Entity\Wishlist;
use VEWA\BaseBundle\Entity\WishlistProduct;

class WishlistService
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /** @var \Symfony\Bridge\Monolog\Logger */
    protected $logger;

    public function __construct(Container $container, Registry $doctrine, Logger $logger)
    {
        $this->container = $container;
        $this->doctrine  = $doctrine;
        $this->logger    = $logger;
    }

    private function getDoctrine()
    {
        return $this->doctrine;
    }

    private function getManager()
    {
        return $this->getDoctrine()->getManager();
    }

    public function getLastEntries()
    {
        return $this->getDoctrine()
            ->getRepository('VEWABaseBundle:WishlistProduct')
            ->findBy([], ['id' => 'DESC'], 10);
    }

    /**
     * @param string $deviceId
     * @return Wishlist
     */
    public function findWishlist($deviceId)
    {
        /** @var Wishlist $wishlist */
        $wishlist = $this->getDoctrine()
            ->getRepository('VEWABaseBundle:Wishlist')
            ->findOneBy(['device' => $deviceId], ['id' => 'DESC']);

        return $wishlist;
    }

    /**
     * @param Device $device
     * @return Wishlist
     */
    public function addWishlist($device)
    {
        $wishlist = new Wishlist();
        $wishlist->setDevice($device);
        $wishlist->setStatus(Wishlist::STATUS_ENABLED);
        $wishlist->setCreated(new \DateTime('now'));

        $this->getDoctrine()->getManager()->persist($wishlist);

        return $wishlist;
    }

    /**
     * @param Wishlist $wishlist
     * @return array
     */
    public function getWishlistProducts($wishlist)
    {
        if ($wishlist !== null) {
            $products = $wishlist->getProducts();
            if (!empty($products)) {
                $list = [];

                /** @var WishlistProduct $product */
                foreach ($products as $product) {
                    if ($product->getStatus() == WishlistProduct::STATUS_DISABLED) {
                        continue;
                    }

                    $list[] = [
                        'id'      => $product->getProduct()->getEmagId(),
                        'title'   => $product->getProduct()->getName(),
                        'image'   => $product->getProduct()->getImage(),
                        'link'    => $product->getProduct()->getLink(),
                        'price'   => $product->getProduct()->getPrice(),
                    ];
                }

                return $list;
            }
        }

        return [];
    }

    /**
     * @param int $wishlistId
     * @param int $productId
     * @return WishlistProduct
     */
    public function getWishlistProduct($wishlistId, $productId) {
        /** @var Product $product */
        $product = $this->getDoctrine()
            ->getRepository('VEWABaseBundle:Product')
            ->findOneBy(['emagId' => $productId], ['id' => 'DESC']);

        $product = $this->getDoctrine()
            ->getRepository('VEWABaseBundle:WishlistProduct')
            ->findOneBy(['product' => $product->getId(), 'wishlist' => $wishlistId], ['id' => 'DESC']);

        return $product;
    }

    /**
     * @param Wishlist $wishlist
     * @param int $productId
     *
     * @return WishlistProduct
     */
    public function addProductToWishlist($wishlist, $productId)
    {
        /** @var Product $product */
        $product = $this->getDoctrine()
            ->getRepository('VEWABaseBundle:Product')
            ->findOneBy(['emagId' => $productId], ['id' => 'DESC']);

        $wishlistProduct = new WishlistProduct();
        $wishlistProduct->setWishlist($wishlist);
        $wishlistProduct->setProduct($product);
        $wishlistProduct->setCreated(new \DateTime('now'));
        $wishlistProduct->setStatus(WishlistProduct::STATUS_ENABLED);

        $this->getDoctrine()->getManager()->persist($wishlistProduct);

        return $wishlistProduct;
    }
}
