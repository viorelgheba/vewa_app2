<?php

namespace VEWA\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use VEWA\ApiBundle\Exception\ApiException;
use VEWA\BaseBundle\Entity\Device;
use VEWA\BaseBundle\Entity\Wishlist;
use VEWA\BaseBundle\Entity\WishlistProduct;
use VEWA\BaseBundle\Service\WishlistService;

class WishlistController extends Controller
{
    /**
     * @Route("/wishlist")
     * @Method({"POST"})
     */
    public function indexAction(Request $request)
    {
        $logger     = $this->get('vewa_api.logger');
        $deviceKey  = $request->request->get('deviceId', null);
        $deviceName = $request->request->get('device', null);

        if (!is_null($deviceKey)) {
            /** @var DeviceService $deviceService */
            $deviceService = $this->get('vewa_base.device');

            // check if the device is already registered, otherwise we register it
            /** @var Device $currentDevice */
            $currentDevice = $deviceService->getDevice($deviceKey);
            if (!$currentDevice) {
                $currentDevice = $deviceService->registerNewDevice($deviceKey, $deviceName);
                $logger->info("[API][WISHLIST] Registering a new device with ID:{$deviceKey} and NAME:{$deviceName}");
            }

            try {
                /** @var WishlistService $wishlistService */
                $wishlistService = $this->get('vewa_base.wishlist');

                $wishlist = $wishlistService->findWishlist($currentDevice->getId());

                if ($wishlist === null) {
                    $wishlist = $wishlistService->addWishlist($currentDevice);
                    $this->get('doctrine')->getManager()->flush();
                }

                $logger->info("[API][WISHLIST] Wishlist ID " . $wishlist->getId());
                $entries = $wishlistService->getWishlistProducts($wishlist);

                $logger->info('[API][WISHLIST] Found item/s: ' . json_encode($entries) . ' | Total item/s: ' . count($entries));

                return new JsonResponse([
                    'success' => true,
                    'data'    => [
                        'request'  => [
                            'deviceId'   => $deviceKey,
                            'deviceName' => $deviceName,
                        ],
                        'response' => [
                            'entries' => $entries,
                            'total'   => count($entries),
                        ],
                    ]
                ]);
            } catch (ApiException $exception) {
                return new JsonResponse([
                    'success' => false,
                    'code'    => $exception->getCode(),
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return new JsonResponse([
            'success' => false,
            'code'    => ApiException::API_UNKNOWN_ERROR,
            'message' => 'Invalid parameters setup',
        ]);
    }

    /**
     * @Route("/add_product")
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        $logger     = $this->get('vewa_api.logger');
        $productId  = $request->request->get('productId', null);
        $deviceKey  = $request->request->get('deviceId', null);
        $deviceName = $request->request->get('device', null);

        $wishlistProductId = null;

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine')->getManager();

        if (!is_null($deviceKey)) {
            /** @var DeviceService $deviceService */
            $deviceService = $this->get('vewa_base.device');

            // check if the device is already registered, otherwise we register it
            /** @var Device $currentDevice */
            $currentDevice = $deviceService->getDevice($deviceKey);
            if (!$currentDevice) {
                $currentDevice = $deviceService->registerNewDevice($deviceKey, $deviceName);
                $logger->info("[API][WISHLIST] Registering a new device with ID:{$deviceKey} and NAME:{$deviceName}");
            }

            /** @var WishlistService $wishlistService */
            $wishlistService = $this->get('vewa_base.wishlist');

            $wishlist = $wishlistService->findWishlist($currentDevice->getId());

            if ($wishlist === null) {
                $wishlist = $wishlistService->addWishlist($currentDevice);
                $em->flush();
            }

            $wishlistProduct = $wishlistService->getWishlistProduct($wishlist->getId(), $productId);

            if ($wishlistProduct === null) {
                $wishlistProduct = $wishlistService->addProductToWishlist($wishlist, $productId);

                $em->flush();

                $wishlistProductId = $wishlistProduct->getId();
            } else {
                if ($wishlistProduct->getStatus() == WishlistProduct::STATUS_DISABLED) {
                    $wishlistProduct->setStatus(WishlistProduct::STATUS_ENABLED);
                    $em->persist($wishlistProduct);
                    $em->flush();

                    $wishlistProductId = $wishlistProduct->getId();
                }
            }
        }

        return new JsonResponse([
            'success' => true,
            'data'    => [
                'request'  => [
                    'productId'  => $productId,
                    'deviceId'   => $deviceKey,
                    'deviceName' => $deviceName,
                ],
                'response' => [
                    'id'   => $wishlistProductId,
                ],
            ]
        ]);
    }

    /**
     * @Route("/remove_product")
     * @Method({"POST"})
     */
    public function removeProductAction(Request $request)
    {
        $logger     = $this->get('vewa_api.logger');
        $productId  = $request->request->get('productId', null);
        $deviceKey  = $request->request->get('deviceId', null);
        $deviceName = $request->request->get('device', null);

        $wishlistProductId = null;

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine')->getManager();

        if (!is_null($deviceKey)) {
            /** @var DeviceService $deviceService */
            $deviceService = $this->get('vewa_base.device');

            // check if the device is already registered, otherwise we register it
            /** @var Device $currentDevice */
            $currentDevice = $deviceService->getDevice($deviceKey);
            if (!$currentDevice) {
                $currentDevice = $deviceService->registerNewDevice($deviceKey, $deviceName);
                $logger->info("[API][WISHLIST] Registering a new device with ID:{$deviceKey} and NAME:{$deviceName}");
            }

            /** @var WishlistService $wishlistService */
            $wishlistService = $this->get('vewa_base.wishlist');

            $wishlist = $wishlistService->findWishlist($currentDevice->getId());

            if ($wishlist !== null) {
                $product = $wishlistService->getWishlistProduct($wishlist->getId(), $productId);
                $product->setStatus(WishlistProduct::STATUS_DISABLED);
                $em->persist($product);

                $wishlistProductId = $product->getId();

                $em->flush();
            }
        }

        return new JsonResponse([
            'success' => true,
            'data'    => [
                'request'  => [
                    'productId'  => $productId,
                    'deviceId'   => $deviceKey,
                    'deviceName' => $deviceName,
                ],
                'response' => [
                    'id'   => $wishlistProductId,
                ],
            ]
        ]);
    }
}
