<?php

namespace VEWA\BaseBundle\Repository;

use Doctrine\ORM\EntityRepository;

class WishlistProductRepository extends EntityRepository
{
    public function getWishlistProducts($wishlistId) {
        return $this->createQueryBuilder('wp')
            ->select('wp.id AS wishlistProductId, wp.modified, p.emagId AS id, p.id AS productId, p.name AS title, p.image, p.link, p.price')
            ->innerJoin('wp.product', 'p')
            ->where('wp.wishlist = :wishlistId')->setParameter('wishlistId', $wishlistId)
            ->andWhere('wp.status = 1')
            ->orderBy('wp.modified', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }
}
