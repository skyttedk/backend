<?php

namespace GFUnit\valgshop\categories;
use GFBiz\units\UnitController;
use Exception;

class Controller extends UnitController
{
    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function show()
    {
        $shopID = isset($_GET["shopID"]) ? $_GET["shopID"] : null;
        $this->view("categories_view", array("shopID" => $shopID));
    }

    public function create()
    {
        try {
            // Get shopID from POST data
            $shopID = isset($_POST['shopID']) ? $_POST['shopID'] : null;

            // Validate shopID
            if (!is_numeric($shopID) || filter_var($shopID, FILTER_VALIDATE_INT) === false) {
                throw new Exception("Shop ID must be an integer");
            }

            // Get names from POST data
            if (!isset($_POST['names']) || !is_array($_POST['names'])) {
                throw new Exception("Category names are required");
            }

            $names = $_POST['names'];

     

            // Create new category record
            $category = new \ShopPresentCategory();
            $category->shop_id = $shopID;
            $category->active = 1; // Set as active by default

            // Map frontend field names to database field names
            $fieldMap = [
                'da' => 'name_dk',
                'no' => 'name_no',
                'sv' => 'name_se',
                'en' => 'name_en',
                'de' => 'name_de'
            ];

            // Set category names
            foreach ($fieldMap as $frontendKey => $dbField) {
                $category->$dbField = isset($names[$frontendKey]) ? $names[$frontendKey] : null;
            }

            // Save category
            if ($category->save()) {
                \response::success(json_encode($category));
            } else {
                throw new Exception("Failed to save category");
            }
        } catch (Exception $e) {
            \response::error("Error creating category: " . $e->getMessage());
        }
    }

    public function list()
    {
        try {
            // Get shopID from POST data
            $shopID = isset($_POST['shopID']) ? $_POST['shopID'] : null;

            // Validate shopID
            if (!is_numeric($shopID) || filter_var($shopID, FILTER_VALIDATE_INT) === false) {
                throw new Exception("Shop ID must be an integer");
            }

            // Find all categories for this shop
            $allCategories = \ShopPresentCategory::find_all_by_shop_id($shopID);

            // If no categories found, return empty array
            if (!$allCategories) {
                \response::success(json_encode([]));
                return;
            }

            // Filter to only include active categories
            $activeCategories = array_filter($allCategories, function($category) {
                return $category->active == 1;
            });

            // Format categories for frontend
            $formattedCategories = [];

            foreach ($activeCategories as $category) {
                $formattedCategory = [
                    'id' => $category->id,
                    'names' => [
                        'da' => $category->name_dk,
                        'no' => $category->name_no,
                        'sv' => $category->name_se,
                        'en' => $category->name_en,
                        'de' => $category->name_de
                    ],
                    'active' => $category->active
                ];

                $formattedCategories[] = $formattedCategory;
            }

            \response::success(json_encode(array_values($formattedCategories)));
        } catch (Exception $e) {
            \response::error("Error fetching categories: " . $e->getMessage());
        }
    }

    public function update()
    {
        try {
            // Get category ID from POST data
            if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
                throw new Exception("Valid category ID is required");
            }

            $categoryId = $_POST['id'];

            // Get names from POST data
            if (!isset($_POST['names']) || !is_array($_POST['names'])) {
                throw new Exception("Category names are required");
            }

            $names = $_POST['names'];

            // Validate that Danish name is provided
            if (!isset($names['da']) || empty($names['da'])) {
                throw new Exception("Danish name is required");
            }

            // Find existing category
            $category = \ShopPresentCategory::find_by_id($categoryId);

            if (!$category) {
                throw new Exception("Category not found");
            }

            // Map frontend field names to database field names
            $fieldMap = [
                'da' => 'name_dk',
                'no' => 'name_no',
                'sv' => 'name_se',
                'en' => 'name_en',
                'de' => 'name_de'
            ];

            // Update category names
            foreach ($fieldMap as $frontendKey => $dbField) {
                $category->$dbField = isset($names[$frontendKey]) ? $names[$frontendKey] : null;
            }

            // Save updated category
            if ($category->save()) {
                \response::success(json_encode($category));
            } else {
                throw new Exception("Failed to update category");
            }
        } catch (Exception $e) {
            \response::error("Error updating category: " . $e->getMessage());
        }
    }

    public function delete()
    {


        $shopPresentPategory =  \Present::find_by_shop_present_category_id($_POST['id']);
        if($shopPresentPategory){
            \response::error("Category in use");
            return;
        }

        try {
            // Get category ID from POST data
            if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
                throw new Exception("Valid category ID is required");
            }

            $categoryId = $_POST['id'];

            // Find existing category
            $category = \ShopPresentCategory::find_by_id($categoryId);

            if (!$category) {
                throw new Exception("Category not found");
            }

            // Soft delete - set active to 0 instead of actually deleting
            $category->active = 0;

            // Save the updated category
            if ($category->save()) {

                \response::success(json_encode([]));
            } else {
                throw new Exception("Failed to delete category");
            }
        } catch (Exception $e) {
            \response::error("Error deleting category: " . $e->getMessage());
        }
    }
}