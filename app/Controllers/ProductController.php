<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\App;
use App\Models\Product;

class ProductController extends Controller
{
    private Product $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function index(): void
    {
        $search = $this->input('search', '');
        $companyId = $this->companyId();

        if ($search) {
            $products = $this->productModel->search($companyId, $search);
        } else {
            $products = $this->productModel->getWithCategory($companyId);
        }

        $categories = App::getInstance()->db()->fetchAll(
            "SELECT * FROM categories WHERE company_id = ? ORDER BY name",
            [$companyId]
        );

        $layout = 'app';
        $pageTitle = 'Produits & Services';
        $currentPage = 'products';
        $this->view('products.index', compact('layout', 'pageTitle', 'currentPage', 'products', 'categories', 'search'));
    }

    public function create(): void
    {
        $categories = App::getInstance()->db()->fetchAll(
            "SELECT * FROM categories WHERE company_id = ? ORDER BY name",
            [$this->companyId()]
        );

        $layout = 'app';
        $pageTitle = 'Nouveau produit/service';
        $currentPage = 'products';
        $this->view('products.form', compact('layout', 'pageTitle', 'currentPage', 'categories'));
    }

    public function store(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect('/products/create');
            return;
        }

        $data = [
            'company_id' => $this->companyId(),
            'type' => $this->input('type', 'product'),
            'name' => trim($this->input('name', '')),
            'description' => trim($this->input('description', '')),
            'sku' => trim($this->input('sku', '')),
            'unit' => trim($this->input('unit', 'unité')),
            'price' => (float) $this->input('price', 0),
            'tax_rate' => (float) $this->input('tax_rate', 19.25),
            'cost_price' => (float) $this->input('cost_price', 0),
            'stock_quantity' => (int) $this->input('stock_quantity', 0),
            'stock_alert_threshold' => (int) $this->input('stock_alert_threshold', 5),
            'category_id' => $this->input('category_id') ?: null,
        ];

        $errors = $this->validate($data, [
            'name' => 'required|min:2|max:255',
            'price' => 'required|numeric',
        ]);

        if (!empty($errors)) {
            $this->setFlash('error', implode(' ', $errors));
            $this->redirect('/products/create');
            return;
        }

        // Handle image upload
        if (!empty($_FILES['image']['tmp_name'])) {
            $data['image'] = $this->handleImageUpload('image');
        }

        $this->productModel->createProduct($data);
        $this->setFlash('success', 'Produit créé avec succès.');
        $this->redirect('/products');
    }

    public function edit(string $id): void
    {
        $product = $this->productModel->find((int) $id);
        if (!$product || $product['company_id'] !== $this->companyId()) {
            $this->setFlash('error', 'Produit introuvable.');
            $this->redirect('/products');
            return;
        }

        $categories = App::getInstance()->db()->fetchAll(
            "SELECT * FROM categories WHERE company_id = ? ORDER BY name",
            [$this->companyId()]
        );

        $layout = 'app';
        $pageTitle = 'Modifier ' . $product['name'];
        $currentPage = 'products';
        $this->view('products.form', compact('layout', 'pageTitle', 'currentPage', 'product', 'categories'));
    }

    public function update(string $id): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect("/products/{$id}/edit");
            return;
        }

        $product = $this->productModel->find((int) $id);
        if (!$product || $product['company_id'] !== $this->companyId()) {
            $this->setFlash('error', 'Produit introuvable.');
            $this->redirect('/products');
            return;
        }

        $data = [
            'type' => $this->input('type', 'product'),
            'name' => trim($this->input('name', '')),
            'description' => trim($this->input('description', '')),
            'sku' => trim($this->input('sku', '')),
            'unit' => trim($this->input('unit', 'unité')),
            'price' => (float) $this->input('price', 0),
            'tax_rate' => (float) $this->input('tax_rate', 19.25),
            'cost_price' => (float) $this->input('cost_price', 0),
            'stock_quantity' => (int) $this->input('stock_quantity', 0),
            'stock_alert_threshold' => (int) $this->input('stock_alert_threshold', 5),
            'category_id' => $this->input('category_id') ?: null,
        ];

        if (!empty($_FILES['image']['tmp_name'])) {
            $data['image'] = $this->handleImageUpload('image');
        }

        $this->productModel->update((int) $id, $data);
        $this->setFlash('success', 'Produit modifié avec succès.');
        $this->redirect('/products');
    }

    public function delete(string $id): void
    {
        $product = $this->productModel->find((int) $id);
        if (!$product || $product['company_id'] !== $this->companyId()) {
            $this->setFlash('error', 'Produit introuvable.');
            $this->redirect('/products');
            return;
        }

        $this->productModel->update((int) $id, ['is_active' => 0]);
        $this->setFlash('success', 'Produit supprimé.');
        $this->redirect('/products');
    }

    public function search(): void
    {
        $query = $this->input('q', '');
        $products = $this->productModel->search($this->companyId(), $query);
        $this->json(['products' => $products]);
    }

    private function handleImageUpload(string $field): ?string
    {
        if (empty($_FILES[$field]['tmp_name'])) {
            return null;
        }

        $file = $_FILES[$field];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed)) {
            return null;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        $filename = uniqid('prod_') . '.' . $ext;
        $path = ROOT_PATH . '/public/uploads/products/';

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        move_uploaded_file($file['tmp_name'], $path . $filename);
        return '/uploads/products/' . $filename;
    }
}
