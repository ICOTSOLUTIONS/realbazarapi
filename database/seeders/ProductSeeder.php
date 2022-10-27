<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category = new Category();
        $subcategory = new SubCategory();

        $category->create(['name'=>'Mens Collection','url'=>'menscollection','image'=>'category/category1.jpg']);
        $category->create(['name'=>'Womens Collection','url'=>'womenscollection','image'=>'category/category2.jpg']);
        // $category->create(['name'=>'Kids Collection','url'=>'kidscollection','image'=>'category/category3.jpg']);
        // $category->create(['name'=>'Health & Beauty','url'=>'health&beauty']);

        $subcategory->create(['category_id'=>1,'name'=>'Shoes','url'=>'menscollection/shoes','image'=>'subcategory/subcategory1.jpg']);
        // $subcategory->create(['category_id'=>1,'name'=>'Shirts','url'=>'womenscollection/shirts','image'=>'subcategory/subcategory6.jpg']);
        // $subcategory->create(['category_id'=>1,'name'=>'T-Shirts','url'=>'womenscollection/t-shirts','image'=>'subcategory/subcategory7.jpg']);
        // $subcategory->create(['category_id'=>1,'name'=>'Jeans','url'=>'womenscollection/jeans','image'=>'subcategory/subcategory8.jpg']);

        $subcategory->create(['category_id'=>2,'name'=>'Clothes','url'=>'womenscollection/clothes','image'=>'subcategory/subcategory2.jpg']);
        $subcategory->create(['category_id'=>2,'name'=>'Purse','url'=>'womenscollection/purse','image'=>'subcategory/subcategory3.jpg']);
        $subcategory->create(['category_id'=>2,'name'=>'Jewellery','url'=>'womenscollection/jewellery','image'=>'subcategory/subcategory4.jpg']);
        // $subcategory->create(['category_id'=>1,'name'=>'Jeans','url'=>'menscollection/jeans','image'=>'subcategory/subcategory4.jpg']);

        // $subcategory->create(['category_id'=>3,'name'=>'Watch','url'=>'kidscollection/watch','image'=>'subcategory/subcategory9.jpg']);
        // $subcategory->create(['category_id'=>3,'name'=>'Shirts','url'=>'kidscollection/shirts','image'=>'subcategory/subcategory10.jpg']);
        // $subcategory->create(['category_id'=>3,'name'=>'T-Shirts','url'=>'kidscollection/t-shirts','image'=>'subcategory/subcategory11.jpg']);
        // $subcategory->create(['category_id'=>3,'name'=>'Jeans','url'=>'kidscollection/jeans','image'=>'subcategory/subcategory12.jpg']);

        // $subcategory->create(['category_id'=>4,'name'=>'Bath & Body','url'=>'health&beauty/bath&body']);
        // $subcategory->create(['category_id'=>4,'name'=>'Beauty Tools','url'=>'health&beauty/beautytools']);
        // $subcategory->create(['category_id'=>4,'name'=>'Hair Care','url'=>'health&beauty/haircare']);
        // $subcategory->create(['category_id'=>4,'name'=>'Makeup','url'=>'health&beauty/makeup']);

        // for ($i=0; $i <  30; $i++) {
        //     $product = new Product();
        //     $product->user_id = 4;
        //     $product->sub_category_id = 1;
        //     $product->title = 'T-Shirt';
        //     $product->price = '5000';
        //     $product->discount_price = '1000';
        //     // $product->size = "small medium large xlarge";
        //     // $product->brand = 'V-Neck T-Shirt';
        //     // $product->type = 'New';
        //     $product->tags = 'Standard';
        //     $product->desc = "V-Neck T-Shirt";
        //     $product->save();
        // }
        // for ($j=1; $j <  30; $j++) {
        //     $products = new ProductImage();
        //     $products->product_id = $j;
        //     $products->image = 'product/image'.$j.'.jpeg';
        //     $products->save();
        // }

        // for ($j=1; $j <  30; $j++) {
        //     $products = new ProductImage();
        //     $products->product_id = $j;
        //     $products->image = 'product/image'.$j.'.jpeg';
        //     $products->save();
        // }

    }
}
