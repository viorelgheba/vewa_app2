<?php

namespace VEWA\BaseBundle\Service;

use Doctrine\ORM\Query;
use Sensio\Bundle\BuzzBundle\SensioBuzzBundle;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bridge\Monolog\Logger;
use VEWA\ApiBundle\Exception\ApiException;
use VEWA\BaseBundle\Entity\Product;

class ProductService
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

    public function getProductByEmagId($id)
    {
        return $this->getDoctrine()
            ->getRepository('VEWABaseBundle:Product')
            ->findOneBy(['emagId' => $id]);
    }

    protected function aes256Decrypt($key, $data)
    {
        if (32 !== strlen($key)) {
            $key = hash('SHA256', $key, true);
        }
        $data    = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, str_repeat("\0", 16));
        $padding = ord($data[strlen($data) - 1]);

        return substr($data, 0, -$padding);
    }

    public function getApiMatches($term = null, $limit = 0)
    {
        if (is_null($term)) {
            return [];
        }

        try {
            $buzz     = $this->container->get('buzz');
            $response = $buzz->get(Product::MOBILE_API_EMAG . urlencode($term));
            $content  = json_decode($this->aes256Decrypt($this->container->getParameter('mobile_api_key'), $response->getContent()), true);
        } catch (\Exception $e) {
            $this->logger->error(json_encode($e));
            $content = [
                'code'   => 500,
                'status' => 'not_ok',
                'data'   => [
                    'items' => [],
                ],
            ];
        }

        if (!empty($content['data']['items'])) {
            $list    = [];
            $counter = 0;
            foreach ($content['data']['items'] as $item) {
                if ($limit && $counter >= $limit) {
                    break;
                }
                $list[] = [
                    'id'    => $item['id'],
                    'title' => $item['name'],
                    'image' => $item['images'][0]['url'],
                    'link'  => $item['url'],
                    'price' => $item['price']['current'],
                ];
                $counter++;
            }

            return $list;
        }

        return [];
    }

    public function saveFoundEntries(array $entries)
    {
        $em = $this->getManager();
        $em->getConnection()->beginTransaction();

        try {
            foreach ($entries as $entry) {
                if (!is_null($this->getProductByEmagId($entry['id']))) {
                    continue;
                }

                $product = new Product();

                $product->setEmagId($entry['id']);
                $product->setName($entry['title']);
                $product->setLink($entry['link']);
                $product->setImage($entry['image']);
                $product->setPrice($entry['price']);
                $product->setStatus(Product::STATUS_ENABLED);

                $em->persist($product);
            }
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $em->close();

            $this->logger->error(json_encode($e));
        }
    }

    public function getSoundexMatches($term = null, $limit = 0)
    {
        if (is_null($term)) {
            return [];
        }

        $em = $this->getManager();

        $dql = "SELECT
                    product.id,
                    product.name,
                    product.image,
                    product.price,
                    product.link,
                    product.status,
                    product.created,
                    product.modified
                FROM
                    VEWABaseBundle:Product product
                WHERE
                    SOUNDEX(product.name) = SOUNDEX(:term)
                ORDER BY product.modified DESC";

        /** @var Query $query */
        $query = $em->createQuery($dql)->setParameter('term', $term);

        return $query->getArrayResult();
    }
}
