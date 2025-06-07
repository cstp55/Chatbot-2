<?php

namespace Aprilo\Chatbot\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class DataExporter
{
    protected $productRepository;
    protected $orderRepository;
    protected $searchCriteriaBuilder;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function getProducts()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $products = $this->productRepository->getList($searchCriteria);
        return $products->getItems();
    }

    public function getOrders()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $orders = $this->orderRepository->getList($searchCriteria);
        return $orders->getItems();
    }
    public function exportProductsAsJson()
    {
        $products = $this->getProducts();
        $productData = [];

        foreach ($products as $product) {
            $productData[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'sku' => $product->getSku(),
            ];
        }

        return json_encode($productData);
    }
    public function exportOrdersAsJson()
    {
        $orders = $this->getOrders();
        $productData = [];
        foreach ($orders as $order) {
            $productData[] = [
                'id'=> $order->getId(),
                'name'=> $order->getName(),
                'price'=> $order->getPrice(),
                'sku'=> $order->getSku()
            ];
        }
        return json_encode($productData);
    }
}

