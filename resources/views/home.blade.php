@extends('layouts.app')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <h1>Live Auction Platform</h1>
            <p class="lead">Bid on amazing items in real-time!</p>
        </div>
    </div>

    <div class="row">
        @foreach($products as $product)
            <div class="col-md-4 mb-4">
                <div class="card product-card h-100">
                    <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}">
                    <div class="card-body">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="card-text">{{ Str::limit($product->description, 100) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Current Bid:</strong> ${{ number_format($product->current_price, 2) }}
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary">
                                    {{ $product->bids->count() }} bids
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="{{ route('products.show', $product) }}" class="btn btn-primary w-100">View Auction</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection