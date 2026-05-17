<div class="row student-details student-details_tab mt_0_sm m-0">
    <div class="col-lg-12 p-0">
        <ul class="nav nav-tabs no-bottom-border mt_0_sm mb-20 m-0 justify-content-start"
            role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="#affiliate_link_tab" role="tab" data-toggle="tab">{{__('affiliate.Affiliate Link')}}</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#income_tab" role="tab" data-toggle="tab">{{__('affiliate.Commissions')}}</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#transaction_tab" role="tab" data-toggle="tab">{{__('affiliate.Withdraw')}}</a>
            </li>

        </ul>

        <!-- Tab panes -->
        <div class="tab-content white_box_30px">
            @include('affiliate::affiliate.components.tableDataComponents._affiliate_link')
            @include('affiliate::affiliate.components.tableDataComponents._commissions')
            @include('affiliate::affiliate.components.tableDataComponents._transaction')
        </div>
    </div>
</div>
