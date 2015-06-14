<?php

namespace VEWA\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * VEWA\BaseBundle\Entity\Wishlist
 *
 * @ORM\Table(name="wishlist")
 * @ORM\Entity(repositoryClass="VEWA\BaseBundle\Repository\WishlistRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Wishlist
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
     * @var \VEWA\BaseBundle\Entity\Device
     * @ORM\OneToOne(targetEntity="\VEWA\BaseBundle\Entity\Device", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="device_id", referencedColumnName="id")
     */
    protected $device;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\VEWA\BaseBundle\Entity\WishlistProduct", mappedBy="wishlist", indexBy="id", fetch="EXTRA_LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="wishlist_id", referencedColumnName="id", nullable=true)
     */
    protected $products;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $name;

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
        $this->products = new ArrayCollection();
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
     * @return Device
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param Device $device
     */
    public function setDevice(Device $device)
    {
        $this->device = $device;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param Product $product
     *
     * @return $this
     */
    public function addProducts(Product $product)
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }
        $product->setStatus(Product::STATUS_ENABLED);

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
