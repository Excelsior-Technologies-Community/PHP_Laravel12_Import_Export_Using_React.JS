<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This method is executed when you run: php artisan migrate
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {

            // Primary key (Auto Increment ID)
            $table->id();

            // Product name
            $table->string('name');

            // Product description (optional)
            $table->text('description')->nullable();

            // Product price with 2 decimal precision
            $table->decimal('price', 10, 2);

            // Product status
            // active   → product is available
            // inactive → product is disabled
            // deleted  → product is soft deleted
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active');

            // User ID who created the product
            $table->integer('created_by')->nullable();

            // User ID who last updated the product
            $table->integer('updated_by')->nullable();

            // Soft delete column (deleted_at)
            // Allows deleting data without removing it from database
            $table->softDeletes();

            // created_at and updated_at timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * This method is executed when you run: php artisan migrate:rollback
     */
    public function down(): void
    {
        // Drop products table
        Schema::dropIfExists('products');
    }
};
    