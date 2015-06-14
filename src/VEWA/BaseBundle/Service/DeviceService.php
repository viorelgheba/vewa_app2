<?php

namespace VEWA\BaseBundle\Service;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bridge\Monolog\Logger;
use VEWA\BaseBundle\Entity\Device;

class DeviceService
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

    public function getDevice($key)
    {
        return $this->getDoctrine()
            ->getRepository('VEWABaseBundle:Device')
            ->findOneBy(['deviceKey' => $key]);
    }

    public function registerNewDevice($key, $name = null)
    {
        /** @var EntityManager $entityManager */
        $em = $this->getManager();

        /** @var Device $device */
        $device = new Device();

        // Setting data
        $device->setDeviceKey($key);
        if (!is_null($name)) {
            $device->setDeviceName($name);
        }
        $device->setStatus(Device::STATUS_ENABLED);

        // Writing data
        $em->persist($device);
        $em->flush();

        return $device;
    }
}
