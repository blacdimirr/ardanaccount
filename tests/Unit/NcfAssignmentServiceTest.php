<?php

namespace Tests\Unit;

use App\Exceptions\NcfException;
use App\Models\NcfSeries;
use App\Models\NcfType;
use App\Services\NcfAssignmentService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NcfAssignmentServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('ncf_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->integer('created_by')->default(0);
            $table->timestamps();
        });

        Schema::create('ncf_series', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ncf_type_id');
            $table->string('series')->nullable();
            $table->unsignedBigInteger('start_number');
            $table->unsignedBigInteger('end_number');
            $table->unsignedBigInteger('current_number')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->enum('status', ['activo', 'vencido', 'agotado'])->default('activo');
            $table->integer('created_by')->default(0);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('ncf_series');
        Schema::dropIfExists('ncf_types');

        parent::tearDown();
    }

    public function test_assigns_next_number_within_range(): void
    {
        $type = NcfType::create([
            'code' => 'B01',
            'description' => 'Credito fiscal',
            'created_by' => 1,
        ]);

        $series = NcfSeries::create([
            'ncf_type_id' => $type->id,
            'series' => 'A',
            'start_number' => 1,
            'end_number' => 5,
            'current_number' => 1,
            'valid_from' => Carbon::today()->subDay(),
            'valid_to' => Carbon::today()->addDays(5),
            'status' => 'activo',
            'created_by' => 1,
        ]);

        $service = app(NcfAssignmentService::class);
        $result = $service->assignNextNumber($series->id, $type->id);

        $this->assertSame((string) 2, $result['number']);
        $this->assertEquals(2, $series->fresh()->current_number);
        $this->assertEquals('activo', $series->fresh()->status);
        $this->assertEquals($type->id, $result['type_id']);
    }

    public function test_range_exhausted_marks_series_and_fails(): void
    {
        $type = NcfType::create([
            'code' => 'B02',
            'description' => 'Consumidor final',
            'created_by' => 1,
        ]);

        $series = NcfSeries::create([
            'ncf_type_id' => $type->id,
            'series' => 'B',
            'start_number' => 1,
            'end_number' => 1,
            'current_number' => 1,
            'valid_from' => Carbon::today()->subDays(2),
            'valid_to' => Carbon::today()->addDays(2),
            'status' => 'activo',
            'created_by' => 1,
        ]);

        $this->expectException(NcfException::class);
        $this->expectExceptionMessage('agotado');

        try {
            app(NcfAssignmentService::class)->assignNextNumber($series->id, $type->id);
        } finally {
            $this->assertEquals('agotado', $series->fresh()->status);
            $this->assertEquals(1, $series->fresh()->current_number);
        }
    }

    public function test_range_expired_marks_series_and_fails(): void
    {
        $type = NcfType::create([
            'code' => 'B14',
            'description' => 'Gubernamental',
            'created_by' => 1,
        ]);

        $series = NcfSeries::create([
            'ncf_type_id' => $type->id,
            'series' => 'C',
            'start_number' => 10,
            'end_number' => 20,
            'current_number' => 10,
            'valid_from' => Carbon::today()->subDays(10),
            'valid_to' => Carbon::today()->subDay(),
            'status' => 'activo',
            'created_by' => 1,
        ]);

        $this->expectException(NcfException::class);
        $this->expectExceptionMessage('vencido');

        try {
            app(NcfAssignmentService::class)->assignNextNumber($series->id, $type->id);
        } finally {
            $this->assertEquals('vencido', $series->fresh()->status);
            $this->assertEquals(10, $series->fresh()->current_number);
        }
    }
}
