<?php

namespace Tests\Unit;

use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\RetentionRule;
use App\Services\RetentionCalculator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RetentionCalculatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('product_service_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('product_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('retention_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('supplier_type', 100)->nullable();
            $table->unsignedBigInteger('service_category_id')->nullable();
            $table->decimal('itbis_retention_rate', 5, 2)->default(0);
            $table->decimal('isr_retention_rate', 5, 2)->default(0);
            $table->integer('created_by')->default(0);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('retention_rules');
        Schema::dropIfExists('product_services');
        Schema::dropIfExists('product_service_categories');

        parent::tearDown();
    }

    public function test_it_applies_category_and_supplier_rules(): void
    {
        $category = ProductServiceCategory::create(['name' => 'Servicios profesionales']);
        $product = ProductService::create(['category_id' => $category->id, 'purchase_price' => 100]);

        RetentionRule::create([
            'supplier_type' => 'profesional',
            'service_category_id' => $category->id,
            'itbis_retention_rate' => 30,
            'isr_retention_rate' => 10,
        ]);

        $calculator = app(RetentionCalculator::class);
        $result = $calculator->calculateForBill([
            [
                'item' => $product->id,
                'quantity' => 2,
                'price' => 100,
                'discount' => 0,
                'itemTaxPrice' => 36, // 18% ITBIS sobre 200
            ],
        ], 'profesional', RetentionRule::all());

        $this->assertEquals(36.00, $result['itbis_billed_total']);
        $this->assertEquals(10.80, $result['itbis_withheld_total']);
        $this->assertEquals(20.00, $result['isr_withheld_total']);
    }

    public function test_it_falls_back_to_supplier_rule_when_no_category(): void
    {
        $product = ProductService::create(['category_id' => null, 'purchase_price' => 50]);

        RetentionRule::create([
            'supplier_type' => 'general',
            'service_category_id' => null,
            'itbis_retention_rate' => 10,
            'isr_retention_rate' => 2,
        ]);

        $calculator = app(RetentionCalculator::class);
        $result = $calculator->calculateForBill([
            [
                'item' => $product->id,
                'quantity' => 1,
                'price' => 50,
                'discount' => 0,
                'itemTaxPrice' => 9, // 18% ITBIS sobre 50
            ],
        ], 'general', RetentionRule::all());

        $this->assertEquals(9.00, $result['itbis_billed_total']);
        $this->assertEquals(0.90, $result['itbis_withheld_total']);
        $this->assertEquals(1.00, $result['isr_withheld_total']);
    }
}
