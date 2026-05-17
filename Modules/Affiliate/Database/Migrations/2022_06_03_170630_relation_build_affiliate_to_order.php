<?php

use App\Models\Order;
use App\Models\OrderPackageDetail;
use App\Models\OrderProductDetail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Affiliate\Entities\AffiliateReferralPayment;
use Modules\Affiliate\Entities\AffiliateReferralUser;
use Modules\Seller\Entities\SellerProductSKU;

class RelationBuildAffiliateToOrder extends Migration
{
    
    public function up()
    {
        if(Schema::hasTable('affiliate_referral_payments')){
            if(!Schema::hasColumn('affiliate_referral_payments', 'order_id')){
                Schema::table('affiliate_referral_payments', function (Blueprint $table) {
                    $table->unsignedBigInteger('order_id')->nullable()->after('status');
                    $table->string('type')->nullable()->after('order_id');
                    $table->dropForeign('affiliate_referral_payments_affiliate_link_id_foreign');
                    $table->unsignedBigInteger('affiliate_link_id')->nullable()->onDelete('SET NULL')->change();
                });
            }
            $referral_products = AffiliateReferralPayment::all();
            foreach($referral_products as $referral_product){
                if($referral_product->payment_from){
                    $orders = Order::where('customer_id', $referral_product->payment_from)->where('created_at', $referral_product->created_at)->get();
                }else{
                    $orders = Order::whereNull('customer_id')->where('created_at', $referral_product->created_at)->get();
                }
                $seller_product_type = 'product';
                $order_id = null;
                foreach($orders as $order){
                    $order_packages = OrderPackageDetail::where('order_id', $order->id)->pluck('id')->toArray();
                    $order_products = OrderProductDetail::whereIn('package_id', $order_packages)->get();
                    foreach($order_products as $product){
                        if($product->type == 'product'){
                            $seller_product = SellerProductSKU::where('id', $product->product_sku_id)->first();
                            if($seller_product){
                                $seller_product = $seller_product->product_id; 
                            }
                            if($seller_product == $referral_product->item_id){
                                $seller_product_type = 'product';
                                $order_id = $order->id;  
                            }
                        }else{
                            if($product->prosuct_sku_id == $referral_product->item_id){
                                $seller_product_type = 'gift_card';
                                $order_id = $order->id;  
                            }
                        }
                    }
                }
                $referral_product->update([
                    'type' => $seller_product_type,
                    'order_id' => $order_id
                ]);

            }
        }

        if(Schema::hasTable('affiliate_referral_users')){
            Schema::table('affiliate_referral_users', function (Blueprint $table) {
                $table->dropForeign('affiliate_referral_users_affiliate_link_id_foreign');
                $table->unsignedBigInteger('affiliate_link_id')->nullable()->references('id')->on('affiliate_links')->onDelete('SET NULL')->change();
                $table->foreignId('reffered_by')->after('affiliate_link_id')->nullable()->constrained('users')->onDelete('cascade');
            });
            $affliate_users = AffiliateReferralUser::with('affiliateLink')->get();
            
            foreach($affliate_users as $user){
                $user->update([
                    'reffered_by' => $user->affiliateLink->owner_id
                ]);
            }
        }
    }

    
    public function down()
    {
        //
    }
}
