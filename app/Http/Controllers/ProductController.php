<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ColorRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SizeRepositoryInterface;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productRepository;
    protected $categoryRepository;
    protected $colorRepository;
    protected $sizeRepository;

    /**
     * Tạo controller mới
     *
     * @return void
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        ColorRepositoryInterface $colorRepository,
        SizeRepositoryInterface $sizeRepository
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->colorRepository = $colorRepository;
        $this->sizeRepository = $sizeRepository;
    }

    /**
     * Hiển thị danh sách sản phẩm
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = $this->productRepository->all();

        return view('backend.product.index', compact('products'));
    }

    /**
     * Hiển thị form để tạo sản phẩm mới.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = $this->categoryRepository->all();
        $colors = $this->colorRepository->pluck('name', 'id');
        $sizes = $this->sizeRepository->pluck('name', 'id');

        return view('backend.product.create', compact([
            'categories', 'colors', 'sizes'
        ]));
    }

    /**
     * Lưu trữ một sản phẩm được tạo trong storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductStoreRequest $request)
    {
        $product = $request->only('name', 'description', 'gender', 'price', 'category_id', 'quantity');
        $product = $this->productRepository->uploadImage($request, $product);
        $color = $request->color;
        $size = $request->size;

        $this->productRepository->createProduct($product, $color, $size);

        return back()->with('status', trans('messages.created_success'));
    }

    /**
     * Hiển thị dữ liệu được chỉ định.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $productSelected = $this->productRepository->findOrFail($id);

        $productsSuggestion = $this->productRepository->getProductsSuggestion($productSelected);

        $categorySelected = $this->categoryRepository->findOrFail($productSelected->category_id);

        $reviews = $this->productRepository->getAllReviews($id);

        $averageRating = $this->productRepository->getAverageRating($id);

        return view('frontend.product.show', compact([
            'productSelected', 'productsSuggestion', 'categorySelected', 'reviews', 'averageRating',
        ]));
    }

    /**
     * Hiển thị form sửa sản phẩm
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = $this->productRepository->findOrFail($id);

        $categories = $this->categoryRepository->all();
        $selectedCategory = $this->categoryRepository->findOrFail($product->category_id)->id;

        $colors = $this->colorRepository->all();
        $selectedColors = $this->productRepository->getSelectedColors($product);

        $sizes = $this->sizeRepository->all();
        $selectedSizes = $this->productRepository->getSelectedSizes($product);

        return view('backend.product.edit', compact([
            'product', 'colors', 'selectedColors', 'sizes', 'selectedSizes', 'categories', 'selectedCategory'
        ]));
    }

    /**
     * Update thông tin trong storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductUpdateRequest $request, $id)
    {
        $product = $request->only('name', 'description', 'gender', 'price', 'category_id', 'quantity');
        $product = $this->productRepository->uploadImage($request, $product);

        $color = $request->color;
        $size = $request->size;

        $this->productRepository->updateProduct($product, $color, $size, $id);

        return back()->with('status', trans('messages.updated_success'));
    }

    /**
     * Xóa sản phẩm trong storage
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->productRepository->deleteProduct($id);

        return back()->with('status', trans('messages.deleted_success'));
    }
}
