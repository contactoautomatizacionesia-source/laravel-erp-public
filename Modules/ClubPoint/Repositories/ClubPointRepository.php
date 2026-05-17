<?php

namespace Modules\ClubPoint\Repositories;

use App\Models\Order;
use Modules\Product\Entities\Product;
use Modules\ClubPoint\Entities\ClubPointWallet;
use Modules\ClubPoint\Entities\ProductPointPriceHistory;
use Modules\Product\Entities\ProductSku;

class ClubPointRepository
{
    public function save($data)
    {
        $productSku = ProductSku::whereBetween('selling_price', [$data['min'], $data['max']])->pluck('product_id')->toArray();
        $products = Product::whereIn('id', $productSku)->get();

        foreach ($products as $product) {
            $previousPoints = (float) $product->club_point;
            $newPoints      = (float) $data['multiple'];
            $product->update(['club_point' => $newPoints, 'club_point_type' => 'multiple']);

            if ($previousPoints === $newPoints) {
                continue;
            }

            ProductPointPriceHistory::create([
                'product_id'      => $product->id,
                'product_sku'     => optional($product->skus->first())->sku,
                'previous_points' => $previousPoints,
                'new_points'      => $newPoints,
                'previous_price'  => null,
                'new_price'       => null,
            ]);
        }
    }

    public function storeSetPoint($request)
    {
        $newPoints = (float) $request;
        $products  = Product::where('is_approved', 1)->get();

        foreach ($products as $product) {
            $previousPoints = (float) $product->club_point;
            $product->update(['club_point' => $newPoints]);

            if ($previousPoints === $newPoints) {
                continue;
            }

            ProductPointPriceHistory::create([
                'product_id'      => $product->id,
                'product_sku'     => optional($product->skus->first())->sku,
                'previous_points' => $previousPoints,
                'new_points'      => $newPoints,
                'previous_price'  => null,
                'new_price'       => null,
            ]);
        }

        return true;
    }

    public function findClubPointProduct($id)
    {
        return Product::findOrFail($id);
    }

    public function updateclubpoint($request, $id)
    {
        $product        = Product::find($id);
        $previousPoints = (float) $product->club_point;
        $newPoints      = (float) gv($request, 'multiple');
        $sku            = $product->skus->first();
        $previousPrice  = $sku ? (float) $sku->selling_price : null;

        $priceFromPoints = gv($request, 'update_product_price') === 'on';
        $product->update(['club_point' => $newPoints, 'price_from_points' => $priceFromPoints]);

        $newPrice = null;
        if ($priceFromPoints) {
            $walletPoint = ClubPointWallet::first();
            if ($walletPoint && $walletPoint->wallet_point) {
                $newPrice = $newPoints * (float) $walletPoint->wallet_point;
                ProductSku::where('product_id', $id)->update(['selling_price' => $newPrice]);
            }
        }

        $pointsChanged = $previousPoints !== $newPoints;
        $priceChanged  = $newPrice !== null && $newPrice !== $previousPrice;

        if (!$pointsChanged && !$priceChanged) {
            return true;
        }

        ProductPointPriceHistory::create([
            'product_id'      => $product->id,
            'product_sku'     => optional($sku)->sku,
            'previous_points' => $pointsChanged ? $previousPoints : null,
            'new_points'      => $pointsChanged ? $newPoints : null,
            'previous_price'  => $priceChanged ? $previousPrice : null,
            'new_price'       => $priceChanged ? $newPrice : null,
        ]);

        return true;
    }

    public function myPurchaseOrderList()
    {
        return Order::with('customer', 'packages', 'packages.products')->where('customer_id', auth()->user()->id)->latest()->paginate(5, ['*'], 'myPurchaseOrderList');
    }

    public function create($data)
    {
        $point = ClubPointWallet::first();
        if ($point) {
            $point->update(['wallet_point' => gv($data, 'wallet_point')]);
            return true;
        } else {
            $point = ClubPointWallet::create([
                'wallet_point' => gv($data, 'wallet_point'),
            ]);
        }
    }

    public function getHistoryByProduct($productId, $type)
    {
        $query = ProductPointPriceHistory::where('product_id', $productId);

        if ($type === 'points') {
            $query->whereNotNull('new_points');
        } elseif ($type === 'price') {
            $query->whereNotNull('new_price');
        }

        return $query->orderBy('created_at', 'asc')->get();
    }
}
