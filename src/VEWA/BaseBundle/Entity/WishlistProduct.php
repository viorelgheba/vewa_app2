<?php

namespace VEWA\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VEWA\BaseBundle\Entity\WishlistProduct
 *
 * @ORM\Table(name="wishlist_products")
 * @ORM\Entity(repositoryClass="VEWA\BaseBundle\Repository\WishlistProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class WishlistProduct
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \VEWA\BaseBundle\Entity\Wishlist
     * @ORM\ManyToOne(targetEntity="\VEWA\BaseBundle\Entity\Wishlist", inversedBy="products", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="wishlist_id", referencedColumnName="id", nullable=true)
     */
    protected $wishlist;

    /**
     * @var \VEWA\BaseBundle\Entity\Product
     * @ORM\ManyToOne(targetEntity="\VEWA\BaseBundle\Entity\Product", inversedBy="wishlistProducts", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $status;

    /**
     * @var \DateTime
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @var \DateTime
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    protected $modified;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->created = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Wishlist
     */
    public function getWishlist()
    {
        return $this->wishlist;
    }

    /**
     * @param Wishlist $wishlist
     */
    public function setWishlist($wishlist)
    {
        $this->wishlist = $wishlist;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     *
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param \DateTime $modified
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    }



}
