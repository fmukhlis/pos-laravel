<?php

namespace App\Console\Commands;

use App\Models\ProductModifier;
use App\Models\ProductModifierCategory;
use App\Models\ProductOption;
use App\Models\ProductOptionCategory;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ClearUnusedProductRelatedData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear-unused:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear unused product-related data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ProductModifier::where('status', 'Inactive')
            ->doesntHave('orderProductVariants')
            ->delete();

        ProductModifierCategory::where('status', 'Inactive')
            ->doesntHave('productModifiers')
            ->delete();

        ProductVariant::where('status', 'Inactive')
            ->doesntHave('orderProductVariants')
            ->delete();

        ProductOption::where('status', 'Inactive')
            ->doesntHave('productVariants')
            ->delete();

        ProductOptionCategory::where('status', 'Inactive')
            ->doesntHave('productOptions')
            ->delete();
    }
}
