<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Create posts table
        Schema::create('posts', function (Blueprint $table) {
            $table->id();  // Auto-incremental ID as primary key
            $table->text('slug');  // Slug for URL
            $table->text('title');  // Post title
            $table->text('summary')->nullable();  // Optional summary
            $table->text('body')->nullable();  // Optional body text
            $table->dateTime('published_at')->nullable();  // Optional publish date
            $table->string('featured_image')->nullable();  // Optional featured image
            $table->string('featured_image_caption')->nullable();  // Optional image caption
            $table->unsignedBigInteger('created_by')->index()->constrained('users')->onDelete('cascade');  // Reference to users table
            $table->json('meta')->nullable();  // Optional meta information
            $table->timestamps();  // Created at and Updated at timestamps
        });

        // Create tags table
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->timestamps();
        });

        // Create categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->timestamps();
        });

        // Create post_tag relationship table
        Schema::create('post_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id')->constrained('posts')->onDelete('cascade');
            $table->unsignedBigInteger('tag_id')->constrained('tags')->onDelete('cascade');
        });

        // Create post_category relationship table
        Schema::create('post_category', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id')->constrained('posts')->onDelete('cascade');
            $table->unsignedBigInteger('category_id')->constrained('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_category');
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('posts');
    }
};
