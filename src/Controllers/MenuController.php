<?php

namespace App\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemOption;
use App\Models\MenuItemOptionValue;
use App\Models\Restaurant;
use App\Services\Validator;

class MenuController extends Controller
{
    private $categoryModel;
    private $itemModel;
    private $optionModel;
    private $optionValueModel;
    private $restaurantModel;

    public function __construct()
    {
        $this->categoryModel = new MenuCategory();
        $this->itemModel = new MenuItem();
        $this->optionModel = new MenuItemOption();
        $this->optionValueModel = new MenuItemOptionValue();
        $this->restaurantModel = new Restaurant();
    }

    /**
     * Show public menu
     */
    public function showPublicMenu($restaurantId)
    {
        $restaurant = $this->restaurantModel->find($restaurantId);
        if (!$restaurant || !$restaurant['is_active']) {
            $this->setFlash('error', 'Restaurante no encontrado.');
            return $this->redirect('/grg/restaurants');
        }

        $categories = $this->categoryModel->getByRestaurant($restaurantId, true);
        
        // Get items for each category
        foreach ($categories as &$category) {
            $category['items'] = $this->itemModel->getByCategory($category['id'], true);
        }

        return $this->view('menu.public', [
            'title' => 'Menú - ' . $restaurant['name'],
            'restaurant' => $restaurant,
            'categories' => $categories
        ]);
    }

    /**
     * Show item details (public)
     */
    public function showItem($itemId)
    {
        $item = $this->itemModel->getWithDetails($itemId);
        
        if (!$item || !$item['is_active']) {
            $this->setFlash('error', 'Producto no disponible.');
            return $this->back();
        }

        return $this->view('menu.item-detail', [
            'title' => $item['name'],
            'item' => $item
        ]);
    }

    /**
     * Manage menu (owner)
     */
    public function manageMenu($restaurantId)
    {
        // TODO: Verify owner owns restaurant
        $categories = $this->categoryModel->getByRestaurant($restaurantId);
        $items = $this->itemModel->getByRestaurant($restaurantId);

        return $this->view('owner.menu.index', [
            'title' => 'Gestionar Menú',
            'restaurant_id' => $restaurantId,
            'categories' => $categories,
            'items' => $items
        ]);
    }

    /**
     * Store category
     */
    public function storeCategory()
    {
        $restaurantId = $this->input('restaurant_id');
        $name = $this->sanitize($this->input('name'));
        $description = $this->sanitize($this->input('description', ''));

        $sortOrder = $this->categoryModel->getNextSortOrder($restaurantId);

        $categoryId = $this->categoryModel->create([
            'restaurant_id' => $restaurantId,
            'name' => $name,
            'description' => $description,
            'is_active' => 1,
            'sort_order' => $sortOrder
        ]);

        $this->setFlash('success', 'Categoría creada.');
        return $this->back();
    }

    /**
     * Store menu item
     */
    public function storeItem()
    {
        $categoryId = $this->input('category_id');
        $restaurantId = $this->input('restaurant_id');
        $name = $this->sanitize($this->input('name'));
        $description = $this->sanitize($this->input('description', ''));
        $price = max(0, (float)$this->input('price', 0));

        // Handle image upload
        $imageUrl = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageUrl = $this->uploadImage($_FILES['image'], $restaurantId);
        }

        $sortOrder = $this->itemModel->getNextSortOrder($categoryId);

        $itemId = $this->itemModel->create([
            'category_id' => $categoryId,
            'restaurant_id' => $restaurantId,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'image_url' => $imageUrl,
            'is_active' => 1,
            'sort_order' => $sortOrder
        ]);

        $this->setFlash('success', 'Producto creado.');
        return $this->back();
    }

    /**
     * Toggle category active
     */
    public function toggleCategory()
    {
        $categoryId = $this->input('category_id');
        $this->categoryModel->toggleActive($categoryId);
        $this->setFlash('success', 'Estado actualizado.');
        return $this->back();
    }

    /**
     * Toggle item active
     */
    public function toggleItem()
    {
        $itemId = $this->input('item_id');
        $this->itemModel->toggleActive($itemId);
        $this->setFlash('success', 'Estado actualizado.');
        return $this->back();
    }

    /**
     * Delete category
     */
    public function deleteCategory()
    {
        $categoryId = $this->input('category_id');
        $this->categoryModel->delete($categoryId);
        $this->setFlash('success', 'Categoría eliminada.');
        return $this->back();
    }

    /**
     * Delete item
     */
    public function deleteItem()
    {
        $itemId = $this->input('item_id');
        $this->itemModel->delete($itemId);
        $this->setFlash('success', 'Producto eliminado.');
        return $this->back();
    }

    /**
     * Upload image helper
     */
    private function uploadImage($file, $restaurantId)
    {
        $uploadDir = __DIR__ . '/../../public/uploads/menu/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'menu_' . $restaurantId . '_' . time() . '_' . uniqid() . '.' . $extension;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return '/grg/public/uploads/menu/' . $filename;
        }

        return null;
    }
}
