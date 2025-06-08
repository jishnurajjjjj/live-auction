@extends('layouts.app')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <h1>Edit Auction</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form id="editAuctionForm" action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $product->name }}" required>
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required>{{ $product->description }}</textarea>
                            <div class="invalid-feedback" id="description-error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="starting_price" class="form-label">Starting Price</label>
                            <input type="number" class="form-control" id="starting_price" name="starting_price" min="0" step="0.01" value="{{ $product->starting_price }}" required>
                            <div class="invalid-feedback" id="starting_price-error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="auction_end_time" class="form-label">Auction End Time</label>
                            <input type="datetime-local" class="form-control" id="auction_end_time" name="auction_end_time" value="{{ $product->auction_end_time->format('Y-m-d\TH:i') }}" required>
                            <div class="invalid-feedback" id="auction_end_time-error"></div>
                            <small class="text-muted">Auction must run for at least 5 minutes from now</small>
                        </div>
                        <div class="mb-3">
                            <label for="youtube_live_url" class="form-label">YouTube Live Stream URL</label>
                            <input type="url" class="form-control" id="youtube_live_url" name="youtube_live_url" 
                                value="{{ old('youtube_live_url', $product->youtube_live_url ?? '') }}"
                                placeholder="https://www.youtube.com/watch?v=...">
                            <small class="text-muted">Optional - paste the full YouTube live stream URL</small>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="invalid-feedback" id="image-error"></div>
                            @if($product->image)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/'.$product->image) }}" width="100" class="img-thumbnail">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image">
                                        <label class="form-check-label" for="remove_image">
                                            Remove current image
                                        </label>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Update Auction</button>
                        <div class="mt-3" id="formMessage"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
  
    const now = new Date();
    now.setMinutes(now.getMinutes() + 5);
    
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    
    const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
    $('#auction_end_time').attr('min', minDateTime);

    $('#editAuctionForm').on('submit', function(e) {
        e.preventDefault();
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#formMessage').text('');
        
        
        const endTime = new Date($('#auction_end_time').val());
        if (endTime <= now) {
            $('#auction_end_time').addClass('is-invalid');
            $('#auction_end_time-error').text('End time must be at least 5 minutes in the future');
            return;
        }
        
       
        const formData = new FormData(this);
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST', 
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.errors) {
                  
                    $.each(data.errors, function(field, messages) {
                        const input = $('#' + field);
                        const errorElement = $('#' + field + '-error');
                        input.addClass('is-invalid');
                        errorElement.text(messages[0]);
                    });
                } else if (data.success) {
                    
                    $('#formMessage').html('<div class="alert alert-success">Auction updated successfully! Redirecting...</div>');
                    setTimeout(function() {
                        window.location.href = data.redirect || '{{ route("products.show", $product) }}';
                    }, 1500);
                } else {
                    
                    $('#formMessage').html('<div class="alert alert-success">Auction updated successfully!</div>');
                }
            },
            error: function(xhr) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        $('#formMessage').html('<div class="alert alert-danger">' + response.message + '</div>');
                    } else {
                        $('#formMessage').html('<div class="alert alert-danger">An error occurred while updating the auction</div>');
                    }
                } catch (e) {
                    $('#formMessage').html('<div class="alert alert-danger">An error occurred while processing your request</div>');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                submitBtn.text('Update Auction');
            }
        });
    });
});
</script>
@endpush