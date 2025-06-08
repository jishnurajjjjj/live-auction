@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                @if($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" class="card-img-top" alt="{{ $product->name }}">
                @else
                    <img src="{{ asset('storage/defult_product.avif') }}" class="card-img-top" alt="No image">
                @endif
                <div class="card-body">
                    <h2 class="card-title">{{ $product->name }}</h2>
                    <p class="card-text">{{ $product->description }}</p>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-0">Current Bid: ₹<span id="current-price">{{ number_format($product->current_price, 2) }}</span></h4>
                           
                            <small class="text-muted d-block">
    <i class="bi bi-calendar-plus me-1 mt-1"></i> {{ \Carbon\Carbon::parse($product->created_at)->format('d M Y, g:i A') }}</small>
       <strong> <small class="text-muted">Starting Price: ${{ number_format($product->starting_price, 2) }}</small> </strong> 
                        </div>
                     
                        <div class="text-end">
                            <div class="countdown" id="countdown">
                                Ends in: <span id="time-remaining"></span>
                            </div>
                           
                            <small class="text-muted d-block">
    <i class="bi bi-calendar-check me-1 mt-1"></i>{{ \Carbon\Carbon::parse($product->auction_end_time)->format('d M Y, g:i A') }}
</small>
  <strong> <small class="text-muted">Listed by: {{ $product->user->name }}</small> </strong> 
                        </div>
                    </div>

                    @if($product->is_active)
                      <div id="bid-div">
                        @if(auth()->user()->isBidder())
                      
                            <form id="bid-form">
                                @csrf
                                <div class="input-group mb-3">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           name="amount" 
                                           id="bid-amount" 
                                           min="{{ $product->current_price + 1 }}" 
                                           step="1" 
                                           value="{{ $product->current_price + 1 }}" 
                                           required>
                                    <button class="btn btn-primary" type="submit">Place Bid</button>
                                </div>
                                <div id="bid-error" class="text-danger small mt-2"></div>
                            </form>
                        @elseif(auth()->user()->isAdmin())
                            <div class="alert alert-info" id="admin_bid_status">
                                Admins cannot place bids.
                            </div>
                        @endif
                    </div>
                    @else
                        <div class="alert alert-warning">
                            This auction has ended.
                           @if(!$product->is_active)
                              @if($product->winner_id)
                                <span id="winner-message">Winner: {{ $product->highestBid->user->name }} with ${{ number_format($product->highestBid->amount, 2) }}</span>
                               @else
                                <span id="winner-message">No bids were placed.</span>
                               @endif
                            @endif
                        </div>
                    @endif

                    @auth
                        @if(auth()->user()->id === $product->user_id)
                            <div class="mt-3">
                                @if($product->is_active)
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary">Edit</a>
                                @endif
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger delete-button" data-name="{{ $product->name }}">Delete</button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Bid History</h5>
                </div>
                <div class="card-body bid-history" id="bid-history">
                    @foreach($product->bids as $bid)
                        <div class="d-flex justify-content-between mb-2 bid-item">
                            <div>
                                <img src="{{ asset('storage/user.jpg') }}" width="30" height="30" class="rounded-circle me-2">
                                <strong>{{ $bid->user->name }}</strong>
                            </div>
                            <div>
                                ₹{{ number_format($bid->amount, 2) }}
                                <small class="text-muted ms-2">{{ $bid->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Live Stream</h5>
                </div>
                <div class="card-body">
                    <div class="stream-container">
                      <iframe 
                            src="https://www.youtube.com/embed/{{ $product->getYoutubeId() ?? 'TToO_5S3oV8' }}?autoplay=0" 
                            frameborder="0" 
                            allowfullscreen 
                            width="100%" 
                            height="315">
                        </iframe>

                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Auction Chat</h5>
                </div>
                <div class="card-body">
                    <div class="chat-container mb-3" id="chat-messages">
                     @foreach($product->chatMessages as $message)
                        @php
                            $isCurrentUser = $message->user_id === auth()->id();
                        @endphp
                        <div class="mb-2 chat-message d-flex {{ $isCurrentUser ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="d-flex {{ $isCurrentUser ? 'flex-row-reverse text-end' : '' }}">
                                <img src="{{ asset('storage/user.jpg') }}" width="30" height="30" class="rounded-circle {{ $isCurrentUser ? 'ms-2' : 'me-2' }}">
                                <div>
                                    <strong>{{ $message->user->name }}</strong>
                                    <small class="text-muted ms-2">{{ $message->created_at->diffForHumans() }}</small>
                                    <div class="p-2 rounded shadow-sm mt-1 {{ $isCurrentUser ? 'bg-primary text-white' : 'bg-light' }}">
                                        {{ $message->message }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    </div>
                    <form id="chat-form">
                        @csrf
                        <div class="input-group">
                            <input type="text" class="form-control" id="chat-message" placeholder="Type your message...">
                            <button class="btn btn-primary" type="submit">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            $(document).ready(function() {
         const currentUserId = {{ auth()->id() }};
           // Countdown timer
         let auctionEndTime = new Date('{{ $product->auction_end_time }}').getTime();
                const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
                    cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
                    forceTLS: true
                });
pusher.connection.bind('connected', function() {
    console.log('Pusher connected successfully!');
});

pusher.connection.bind('error', function(err) {
    console.error('Pusher error:', err);
});


                const channel = pusher.subscribe('product.{{ $product->id }}');

                $('#bid-form').submit(function(e) {
                    e.preventDefault();
                    
                    const form = $(this);
                    const formData = form.serialize();
                    const productId = {{ $product->id }};
                    
                    $('#bid-error').text('');
                    
                    $.ajax({
                        url: '/products/' + productId + '/bid',
                        method: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function(data) {
                            if (data.success) {
                                $('#bid-amount').val(data.next_min_bid).attr('min', data.next_min_bid);
                            } else {
                                $('#bid-error').text(data.message || 'Bid failed. Please try again.');
                            }
                        },
                        error: function(xhr) {
                            $('#bid-error').text('An error occurred. Please try again.');
                            console.error('Error:', xhr.responseText);
                        }
                    });
                });

                
                channel.bind('App\\Events\\BidPlaced', function(data) {

                     const currentPrice = parseFloat(data.product.current_price) || 0;
                      $('#current-price').text(currentPrice.toFixed(2));
                  const nextBid = currentPrice + 1;
                 $('#bid-amount').attr('min', nextBid).val(nextBid);

                    const bidItem = `
                        <div class="d-flex justify-content-between mb-2 bid-item">
                            <div>
                                <img src="{{ asset('storage/user.jpg') }}" width="30" height="30" class="rounded-circle me-2">
                                <strong>${data.bid.user.name}</strong>
                            </div>
                            <div>
                                ₹${parseFloat(data.bid.amount).toFixed(2)}
                                <small class="text-muted ms-2">just now</small>
                            </div>
                        </div>
                    `;
                    $('#bid-history').prepend(bidItem).scrollTop(0);

                    if (data.time_extended) {
                            auctionEndTime = new Date(data.product.auction_end_time).getTime();
                        }

                });

                channel.bind('App\\Events\\AuctionEnded', function(data) {
                    $('#bid-form').hide();
                    $('#admin_bid_status').hide();
                    
                  const winnerMessage = data.product.winner 
                    ? `Winner: ${data.product.winner.name} with ₹${parseFloat(data.product.winner.amount).toFixed(2)}`
                    : 'No bids were placed.';

                    
                    const alertDiv = $(`
                        <div class="alert alert-warning">
                            This auction has ended. <span id="winner-message">${winnerMessage}</span>
                        </div>
                    `);
                    
                    if ($('#bid-form').length) {
                        $('#bid-div').after(alertDiv);
                    } else {
                        $('.alert.alert-warning').html(`This auction has ended. <span id="winner-message">${winnerMessage}</span>`);
                    }

                    if ($('#admin_bid_status').length) {
                        $('#admin_bid_status').after(alertDiv);
                    } else {
                        $('.alert.alert-warning').html(`This auction has ended. <span id="winner-message">${winnerMessage}</span>`);
                    }
                    
                    $('#countdown').text('Auction has ended');
                });

               
               channel.bind('App\\Events\\NewChatMessage', function(data) {
                        const isCurrentUser = data.message.user.id === currentUserId;

                        const messageDiv = `
                            <div class="mb-2 chat-message d-flex ${isCurrentUser ? 'justify-content-end' : 'justify-content-start'}">
                                <div class="d-flex ${isCurrentUser ? 'flex-row-reverse text-end' : ''}">
                                    <img src="/storage/user.jpg" width="30" height="30" class="rounded-circle ${isCurrentUser ? 'ms-2' : 'me-2'}">
                                    <div>
                                        <strong>${data.message.user.name}</strong>
                                        <small class="text-muted ms-2">just now</small>
                                        <div class="p-2 rounded shadow-sm mt-1 bg-${isCurrentUser ? 'primary text-white' : 'light'}">
                                            ${data.message.text}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        $('#chat-messages').append(messageDiv).scrollTop($('#chat-messages')[0].scrollHeight);
                    });


               
                $('#chat-form').submit(function(e) {
                    e.preventDefault();
                    const message = $('#chat-message').val().trim();
                    
                    if (message) {
                        $.ajax({
                            url: '{{ route('products.message', $product) }}',
                            method: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                message: message
                            },
                            success: function() {
                                $('#chat-message').val('');
                            },
                            error: function(xhr) {
                                console.error('Error:', xhr.responseText);
                            }
                        });
                    }
                });

              

              function updateCountdown() {
                    const now = new Date().getTime();
                    const distance = auctionEndTime - now;

                    if (distance < 0) {
                        $('#time-remaining').text('Auction has ended');
                        return;
                    }

                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    let timeString = '';
                    if (days > 0) timeString += `${days}d `;
                    if (hours > 0 || days > 0) timeString += `${hours}h `;
                    if (minutes > 0 || hours > 0 || days > 0) timeString += `${minutes}m `;
                    timeString += `${seconds}s`;

                    $('#time-remaining').text(timeString);
                }

                
                updateCountdown();
                setInterval(updateCountdown, 1000);
            });

             document.addEventListener('DOMContentLoaded', function () {
        const deleteButtons = document.querySelectorAll('.delete-button');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                const form = this.closest('form');
                const productName = this.getAttribute('data-name');

                Swal.fire({
                    title: `Delete "${productName}"?`,
                    text: "This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
        </script>
    @endpush
@endsection