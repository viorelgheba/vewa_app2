<?php

namespace VEWA\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use VEWA\ApiBundle\Exception\ApiException;
use VEWA\BaseBundle\Service\DeviceService;
use VEWA\BaseBundle\Service\ProductService;

class SearchController extends Controller
{
    /**
     * @Route("/search")
     * @Method({"POST"})
     */
    public function indexAction(Request $request)
    {
        $logger     = $this->get('vewa_api.logger');
        $term       = $request->request->get('term', false);
        $limit      = $request->request->get('max', 1);
        $deviceKey  = $request->request->get('deviceId', null);
        $deviceName = $request->request->get('device', null);

        try {
            if (!$term) {
                throw new ApiException('No search term specified', ApiException::API_NO_PARAMS_ERROR);
            }

            if (!is_null($deviceKey)) {
                /** @var DeviceService $deviceService */
                $deviceService = $this->get('vewa_base.device');

                // check if the device is already registered, otherwise we register it
                if (!$deviceService->getDevice($deviceKey)) {

                    $deviceService->registerNewDevice($deviceKey, $deviceName);
                    $logger->info("[API][SEARCH] Registering a new device with ID:{$deviceKey} and NAME:{$deviceName}");
                }
            }

            $logger->info('[API][SEARCH] Receiving a new request: ' . implode(',', $request->request->all()));

            /** @var ProductService $productService */
            $productService = $this->get('vewa_base.product');
            $entries        = $productService->getApiMatches($term, $limit);
            //$entries = $productService->getSoundexMatches($term, $limit);

            if (!empty($entries)) {
                $productService->saveFoundEntries($entries);
            }

            $logger->info('[API][SEARCH] Found item/s: ' . json_encode($entries) . ' | Total item/s: ' . count($entries));

            return new JsonResponse([
                'success' => true,
                'data'    => [
                    'request'  => [
                        'term'       => $term,
                        'limit'      => $limit,
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
}
