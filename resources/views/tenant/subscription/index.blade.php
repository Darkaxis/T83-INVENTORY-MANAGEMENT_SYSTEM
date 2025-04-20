@extends('layouts.tenant')

@section('content')
<div class="container">
    <h1>Subscription & Plan</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Current Plan: {{ $store->pricingTier->name ?? 'Free' }}</h2>
        </div>
        <div class="card-body">
            @if($store->pricingTier)
                <div class="row">
                    <div class="col-md-6">
                        <h5>Plan Details</h5>
                        <p>{!! $store->pricingTier->description !!}</p>
                        <p><strong>Billing Cycle:</strong> {{ ucfirst($store->billing_cycle) }}</p>
                        <p><strong>Price:</strong> 
                            ${{ $store->billing_cycle == 'monthly' ? 
                                $store->pricingTier->monthly_price : 
                                $store->pricingTier->annual_price }}
                            / {{ $store->billing_cycle }}
                        </p>
                        <p><strong>Subscription Period:</strong> 
                            {{ $store->subscription_start_date->format('M d, Y') }} - 
                            {{ $store->subscription_end_date->format('M d, Y') }}
                        </p>
                        <p><strong>Auto-renew:</strong> {{ $store->auto_renew ? 'Yes' : 'No' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Usage</h5>
                        <div class="progress mb-2">
                            @php
                                $productCount = \App\Models\Product::where('store_id', $store->id)->count();
                                $productLimit = $store->pricingTier->product_limit ?? 0;
                                $productPercentage = $productLimit > 0 ? min(100, ($productCount / $productLimit) * 100) : 0;
                            @endphp
                            <div class="progress-bar" role="progressbar" style="width: {{ $productPercentage }}%">
                                {{ $productCount }} / {{ $productLimit > 0 ? $productLimit : '∞' }} Products
                            </div>
                        </div>
                        
                        <div class="progress">
                            @php
                                $userCount = \App\Models\User::where('store_id', $store->id)->count();
                                $userLimit = $store->pricingTier->user_limit ?? 0;
                                $userPercentage = $userLimit > 0 ? min(100, ($userCount / $userLimit) * 100) : 0;
                            @endphp
                            <div class="progress-bar" role="progressbar" style="width: {{ $userPercentage }}%">
                                {{ $userCount }} / {{ $userLimit > 0 ? $userLimit : '∞' }} Users
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <p>You are currently on a free plan with limited features.</p>
            @endif
        </div>
    </div>
    
    <h3>Available Plans</h3>
    <div class="row">
        @foreach($availableTiers as $tier)
            <div class="col-md-4 mb-4">
                <div class="card h-100 {{ $store->pricing_tier_id == $tier->id ? 'border-primary' : '' }}">
                    <div class="card-header">
                        <h5>{{ $tier->name }}</h5>
                    </div>
                    <div class="card-body">
                        <p>{!! $tier->description !!}</p>
                        <ul>
                            <li>{{ $tier->product_limit > 0 ? $tier->product_limit . ' products' : 'Unlimited products' }}</li>
                            <li>{{ $tier->user_limit > 0 ? $tier->user_limit . ' users' : 'Unlimited users' }}</li>
                            @foreach($tier->features_json ?? [] as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-footer">
                        <p class="card-text">
                            <strong>Monthly:</strong> ${{ $tier->monthly_price }}<br>
                            <strong>Annual:</strong> ${{ $tier->annual_price ?? 'N/A' }}
                        </p>
                        
                        @if($store->pricing_tier_id == $tier->id)
                            <button class="btn btn-primary" disabled>Current Plan</button>
                        @else
                            <form action="{{ route('tenant.subscription.upgrade', ['subdomain' => $store->slug]) }}" method="POST">
                                @csrf
                                <input type="hidden" name="pricing_tier_id" value="{{ $tier->id }}">
                                <button type="submit" class="btn btn-success">Upgrade</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection