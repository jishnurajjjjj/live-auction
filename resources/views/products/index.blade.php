@extends('layouts.app')

@push('styles')
<style>
    .product-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.1);
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .status-info {
        border: 1px dashed #dee2e6;
        margin-top: -10px;
        margin-bottom: -10px;
    }
    .card-footer {
        border-radius: 0 0 10px 10px !important;
    }
    .badge.bg-success {
        box-shadow: 0 0 0 2px white;
    }
    .badge.bg-danger {
        box-shadow: 0 0 0 2px white;
    }
</style>
@endpush
@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>
                    @if(request()->is('live'))
                        Live Auctions
                    @elseif(request()->is('my-win'))
                        My Winning Auctions
                    @else
                        All Auctions
                    @endif
                </h1>
                
                <div class="search-container">
                    <div class="input-group">
                        <input type="text" id="auctionSearch" class="form-control" 
                               placeholder="Search auctions by name, description or seller...">
                        <button class="btn btn-primary" type="button" id="searchButton">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @if($products->isEmpty())
    <div class="col-12 text-center py-5">
        <h4 class="text-muted">
            @if(request()->is('live'))
                No live auctions available at the moment.
            @elseif(request()->is('my-win'))
                You haven't won any auctions yet.
            @else
                No auctions available at the moment.
            @endif
        </h4>
    </div>
@else
<div class="mb-3">
    <strong>Total Auctions:</strong><span id="auction_count"> {{ $p_count }} </span>
</div>
<div class="row" id="auctionResults">
        @foreach($products as $product)
            <div class="col-md-4 mb-4">
                <div class="card product-card h-100 position-relative">
                   
                    <div class="position-absolute top-0 end-0 m-2">
                        @if($product->is_active)
                            <span class="badge bg-success rounded-pill px-3 py-2">
                                <i class="bi bi-lightning-charge-fill me-1"></i> Live
                            </span>
                        @else
                            <span class="badge bg-danger rounded-pill px-3 py-2">
                                <i class="bi bi-check-circle-fill me-1"></i> Ended
                            </span>
                        @endif
                    </div>

                   
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" 
                             class="card-img-top" 
                               style="height: 200px; object-fit: cover;"
                             alt="{{ $product->name }}">
                    @else
                        <img src="{{ asset('storage/image/defult_product.avif') }}" 
                             class="card-img-top" 
                               style="height: 200px; object-fit: cover;"
                             alt="No image">
                    @endif

                   
                    <div class="card-body">
                        <h5 class="card-title">{{ Str::limit($product->name, 25) }}</h5>
                        <p class="card-text text-muted">{{ Str::limit($product->description, 80) }}</p>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-calendar-plus me-2 text-secondary"></i>
                                <small>{{ \Carbon\Carbon::parse($product->created_at)->format('d M Y, g:i A') }}</small>
                            </div>
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-calendar-check me-2 text-secondary"></i>
                                <small>{{ \Carbon\Carbon::parse($product->auction_end_time)->format('d M Y, g:i A') }}</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <span class="fw-bold">${{ number_format($product->current_price, 2) }}</span>
                                <span class="text-muted ms-1">Current Bid</span>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-hammer me-1"></i> {{ $product->bids->count() }}
                            </span>
                        </div>
                    </div>

                   
                    <div class="card-footer bg-light border-0">
                        <div class="status-info bg-white p-3 rounded text-center">
                            @if($product->is_active)
                               <div class="d-flex flex-column align-items-center justify-content-center">

                                <strong>   <small class="text-muted d-block">AUCTION STATUS</small> </strong>
    <div class="d-flex align-items-center mb-1">
        <i class="bi bi-hourglass-split text-info fs-4 me-2"></i>
        <strong>Ongoing</strong>
    </div>
   
    <span class="text-primary">Ends on {{ \Carbon\Carbon::parse($product->auction_end_time)->format('d F Y g:i A') }}</span>
</div>

                            @elseif($product->highestBid)
                              <div class="d-flex align-items-center justify-content-center">
                                <div>
                                     <strong><small class="text-muted d-block text-black">WINNER</small> </strong>
                                    <strong class="d-flex align-items-center gap-2 ml-2">
                                        <i class="bi bi-trophy-fill text-warning fs-4"></i>
                                        {{ $product->highestBid->user->name }}
                                    </strong>
                                    <span class="text-success fw-bold">${{ number_format($product->highestBid->amount, 2) }}</span>
                                </div>
                            </div>

                            @else
                                <div class="text-muted">
                                    <i class="bi bi-emoji-frown"></i> No winning bids
                                </div>
                            @endif
                        </div>
                    </div>

               
               
<div class="card-footer bg-white border-top">
    <a href="{{ route('products.show', $product) }}" class="btn btn-primary w-100">
        @if($product->is_active && $product->auction_end_time > now())
            @if(auth()->user()->isAdmin())
                <i class="bi bi-eye me-1"></i> View Auction
            @else
                <i class="bi bi-gem me-1"></i> Place Bid
            @endif
        @else
            <i class="bi bi-graph-up me-1"></i> View Results
        @endif
    </a>
</div>
                </div>
            </div>
        @endforeach
</div>
        @endif
    </div>
@push('scripts')
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <script>
$(document).ready(function() {
    let searchTimeout;
    const pageType = @json(request()->is('live') ? 'live' : (request()->is('my-win') ? 'my-wins' : 'all'));

    $('#auctionSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch($(this).val());
        }, 500);
    });

    $('#searchButton').on('click', function() {
        performSearch($('#auctionSearch').val());
    });

    function performSearch(searchTerm) {
        $.ajax({
            url: '{{ route("products.search") }}',
            type: 'GET',
            data: {
                search: searchTerm,
                page_type: pageType
            },
            success: function(response) {
                    $('#auctionResults').html('');
                let products = response.products;
                let html = '';

                if (products.length === 0) {
                    html = `
                        <div class="col-12 text-center py-5">
                            <h4 class="text-muted">No auctions found.</h4>
                        </div>
                    `;
                } else {
                    products.forEach(product => {
                        const createdAt = new Date(product.created_at);
                        const endTime = new Date(product.auction_end_time);

                        const createdAtFormatted = createdAt.toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                        const endTimeFormatted = endTime.toLocaleString('en-GB', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });

                        let imageUrl = product.image ? `/storage/${product.image}` : `/storage/defult_product.avif`;


                        let statusBadge = product.is_active 
                            ? `<span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-lightning-charge-fill me-1"></i> Live</span>`
                            : `<span class="badge bg-danger rounded-pill px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i> Ended</span>`;

                        let winnerSection = '';
                        if (!product.is_active && product.highest_bid) {
                            winnerSection = `
                                <div class="d-flex align-items-center justify-content-center">
                                    <div>
                                        <strong><small class="text-muted d-block text-black">WINNER</small></strong>
                                        <strong class="d-flex align-items-center gap-2 ml-2">
                                            <i class="bi bi-trophy-fill text-warning fs-4"></i>
                                            ${product.highest_bid.user.name}
                                        </strong>
                                        <span class="text-success fw-bold">$${Number(product.highest_bid.amount).toFixed(2)}</span>
                                    </div>
                                </div>
                            `;
                        } else if (product.is_active) {
                            winnerSection = `
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <strong><small class="text-muted d-block">AUCTION STATUS</small></strong>
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-hourglass-split text-info fs-4 me-2"></i>
                                        <strong>Ongoing</strong>
                                    </div>
                                    <span class="text-primary">Ends on ${endTimeFormatted}</span>
                                </div>
                            `;
                        } else {
                            winnerSection = `<div class="text-muted"><i class="bi bi-emoji-frown"></i> No winning bids</div>`;
                        }

                        let buttonText = '';
                        if (product.is_active && endTime > new Date()) {
                           buttonText = `{!! auth()->user()->isAdmin() ? '<i class="bi bi-eye me-1"></i> View Auction' : '<i class="bi bi-gem me-1"></i> Place Bid' !!}`;
                        } else {
                            buttonText = `<i class="bi bi-graph-up me-1"></i> View Results`;
                        }

                        html += `
                        <div class="col-md-4 mb-4">
                            <div class="card product-card h-100 position-relative">
                                <div class="position-absolute top-0 end-0 m-2">${statusBadge}</div>
                                <img src="${imageUrl}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="${product.name}">
                                <div class="card-body">
                                    <h5 class="card-title">${product.name.length > 25 ? product.name.substring(0, 25) + '...' : product.name}</h5>
                                    <p class="card-text text-muted">${product.description.length > 80 ? product.description.substring(0, 80) + '...' : product.description}</p>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div class="d-flex align-items-center text-muted">
                                            <i class="bi bi-calendar-plus me-2 text-secondary"></i>
                                            <small>${createdAtFormatted}</small>
                                        </div>
                                        <div class="d-flex align-items-center text-muted">
                                            <i class="bi bi-calendar-check me-2 text-secondary"></i>
                                            <small>${endTimeFormatted}</small>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <span class="fw-bold">$${Number(product.current_price).toFixed(2)}</span>
                                            <span class="text-muted ms-1">Current Bid</span>
                                        </div>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-hammer me-1"></i> ${product.bids.length}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-footer bg-light border-0">
                                    <div class="status-info bg-white p-3 rounded text-center">
                                        ${winnerSection}
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top">
                                    <a href="/products/${product.id}" class="btn btn-primary w-100">
                                        ${buttonText}
                                    </a>
                                </div>
                            </div>
                        </div>
                        `;
                    });
                }
                $('#auction_count').text(response.filtered_count);
                $('#auctionResults').html(html);
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    alert(xhr.responseJSON.error);
                } else {
                    alert('An error occurred during search');
                }
            }
        });
    }
});
 </script>
@endpush
@endsection
