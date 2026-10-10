<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_category_id')->constrained()->cascadeOnDelete();
            $table->string('question', 255);
            $table->text('answer');
            $table->string('source_url', 2048)->nullable();
            $table->string('status', 20)->default('published')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['faq_category_id', 'sort_order']);
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email', 254)->unique();
            $table->string('status', 20)->default('subscribed')->index();
            $table->string('source', 100)->nullable();
            $table->timestamp('consented_at');
            $table->timestamps();
        });

        $now = now();
        $categories = [
            ['name' => 'Orders & Delivery', 'slug' => 'orders-delivery', 'description' => 'Ordering, payment, delivery and tracking.', 'sort_order' => 1],
            ['name' => 'Returns', 'slug' => 'returns', 'description' => 'Returns and order changes.', 'sort_order' => 2],
            ['name' => 'Products & Care', 'slug' => 'products-care', 'description' => 'Product details, ingredients and safe use.', 'sort_order' => 3],
            ['name' => 'Account & Privacy', 'slug' => 'account-privacy', 'description' => 'Shopping accounts and personal information.', 'sort_order' => 4],
        ];
        foreach ($categories as &$category) {
            $category['is_active'] = true;
            $category['created_at'] = $now;
            $category['updated_at'] = $now;
        }
        unset($category);
        DB::table('faq_categories')->insert($categories);
        $ids = DB::table('faq_categories')->pluck('id', 'slug');
        $fda = 'https://www.fda.gov/cosmetics';
        $faqs = [
            ['orders-delivery', 'Which payment methods can I use?', 'The secure options available for your order are shown at checkout. Choose the option you prefer before placing your order.'],
            ['orders-delivery', 'How can I check my delivery estimate?', 'Review the delivery information shown at checkout and in your order confirmation. Delivery estimates can vary by destination and selected service.'],
            ['orders-delivery', 'Where can I find my order status?', 'Sign in and open your order history to see the latest order and shipment status. If you checked out as a guest, contact our support team with your order number.'],
            ['returns', 'How do I request a return?', 'Open the Returns page from your account and submit the item and reason. Requests are reviewed against the store returns policy; wait for approval and instructions before sending an item back.'],
            ['returns', 'Can I change or cancel an order?', 'Contact the support team as soon as possible with your order number. Orders already being prepared or shipped may no longer be changeable.'],
            ['products-care', 'Where can I find product ingredients?', 'Check the product details and packaging for the current ingredients and directions. Contact us before ordering if you need help locating information for a particular item.'],
            ['products-care', 'How should I try a new skincare product?', 'Follow the directions and warnings on the product label. If you have sensitive skin or a history of reactions, consider testing a small area first and ask a qualified healthcare professional for personal advice. Cosmetics are not a substitute for medical care.'],
            ['products-care', 'Are cosmetic products approved to treat skin conditions?', 'Cosmetic products are not a substitute for medical treatment. Read each product label carefully and consult a qualified healthcare professional about a skin condition. The FDA provides general information about cosmetics and their labeling.'],
            ['account-privacy', 'Do I need an account to place an order?', 'You can use the guest checkout option when it is offered. An account makes it easier to review your order history and manage your details.'],
            ['account-privacy', 'How do I update my account details?', 'Sign in and open your account settings to update the details available there. Contact support if you cannot access your account.'],
        ];
        foreach ($faqs as [$slug, $question, $answer]) {
            DB::table('faqs')->insert([
                'faq_category_id' => $ids[$slug],
                'question' => $question,
                'answer' => $answer,
                'source_url' => str_contains($question, 'cosmetic products') ? $fda : null,
                'status' => 'published',
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('faq_categories');
    }
};
