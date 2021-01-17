<?php

class Check
{
    public int $id;
    public float $vat;
    public array $productIds = [];
    public array $productQuantities = [];

    public array $products = [];
    public float $total = 0;
}

class CheckRepository
{
    public function create(Check &$check)
    {
        // insert query
        $check->id = rand(10, 1000);
    }
}

class Product
{
    public int $productId;
    public float $priceNetto;

    public function __construct(int $productId, float $priceNetto)
    {
        $this->productId = $productId;
        $this->priceNetto = $priceNetto;
    }
}

class ProductRepository
{
    public function getProduct(int $productId): Product
    {
        // query

        return new Product($productId, rand(1,100) / 10);
    }
}

class CheckHandler
{
    private CheckRepository $checkRepository;
    private ProductRepository $productRepository;
    private array $params;
    public Check $check;

    const DEFAULT_VAT = 19;
    const UKRAINE_VAT = 21.5;
    const USA_VAT = 0;

    public function __construct(
        CheckRepository $checkRepository,
        ProductRepository $productRepository
    )
    {
        $this->checkRepository = $checkRepository;
        $this->productRepository = $productRepository;
    }

    public function setParams(array $params): CheckHandler
    {
        $this->params = $params;

        return $this;
    }

    public function handle(): CheckHandler
    {
        // vat - ндс/налог
        $vat = self::DEFAULT_VAT;
        if (!empty($this->params['country'])) {
            switch ($this->params['country']) {
                case 'UA':
                    $vat = self::UKRAINE_VAT;
                    break;
                case 'USA':
                    $vat = self::USA_VAT;
            }
        }

        $check = new Check();
        $check->vat = $vat;
        $check->productIds = $this->params['productIds'];
        $check->productQuantities = $this->params['productQuantities'];
        foreach ($check->productIds as $productId) {
            $product = $this->productRepository->getProduct($productId);
            $price = $product->priceNetto + $product->priceNetto * ($vat / 100);
            $check->total += $this->params['productQuantities'][$productId] * $price;
            $check->products[] = $product;
        }

        $this->checkRepository->create($check);
        $this->check = $check;

        return $this;
    }

}

$checkHandler = (new CheckHandler(new CheckRepository(), new ProductRepository()))
    ->setParams([
        'country' => 'UA',
        'productIds' => [1, 3, 4, 5],
        'productQuantities' => [
            1 => 3,
            3 => 2,
            4 => 1,
            5 => 1
        ],
    ])->handle();

var_dump( $checkHandler->check );

