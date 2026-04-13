<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 80)->unique();
            $table->timestamps();
        });

        $categoryNames = DB::table('products')
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->map(fn (string $name): string => trim($name))
            ->filter()
            ->unique()
            ->values();

        $timestamp = now();

        if ($categoryNames->isNotEmpty()) {
            DB::table('categories')->insert(
                $categoryNames
                    ->map(fn (string $name): array => [
                        'name' => $name,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ])
                    ->all()
            );
        }

        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('category_id')
                ->nullable()
                ->after('name')
                ->constrained()
                ->restrictOnDelete();
        });

        $categoryIdsByName = DB::table('categories')->pluck('id', 'name');

        DB::table('products')
            ->select('id', 'category')
            ->orderBy('id')
            ->get()
            ->each(function (object $product) use ($categoryIdsByName): void {
                $categoryName = trim((string) $product->category);
                $categoryId = $categoryIdsByName[$categoryName] ?? null;

                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['category_id' => $categoryId]);
            });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('category', 80)->nullable()->after('name');
        });

        $categoryNamesById = DB::table('categories')->pluck('name', 'id');

        DB::table('products')
            ->select('id', 'category_id')
            ->orderBy('id')
            ->get()
            ->each(function (object $product) use ($categoryNamesById): void {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'category' => $categoryNamesById[$product->category_id] ?? null,
                    ]);
            });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::dropIfExists('categories');
    }
};
