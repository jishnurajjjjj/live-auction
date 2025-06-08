<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Bid;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\BidPlaced;
use App\Events\NewChatMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;


class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
       
      $products = Product::with(['user', 'highestBid']);
       if (request()->is('live')) {
        $products->where('is_active', 1);
        }
      $products=$products->orderBy('is_active', 'desc')
    ->orderBy('created_at', 'desc');
    $p_count=$products->count();
    $products=$products->get();
        return view('products.index', compact('products','p_count'));
    }

    public function create()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('products.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'starting_price' => 'required|numeric|min:0',
            'auction_end_time' => 'required|date|after:now',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'youtube_live_url' => 'nullable',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'starting_price' => $validated['starting_price'],
            'current_price' => $validated['starting_price'],
            'auction_end_time' => $validated['auction_end_time'],
            'youtube_live_url' => $validated['youtube_live_url'],
            'image' => $imagePath,
        ]);

      return response()->json([
        'success' => true,
        'redirect' => route('products.show', $product->id) 
    ]);
    }

    public function show(Product $product)
    {
        $product->load(['bids.user', 'chatMessages.user']);

        $user = auth()->user();


    if ($user->isAdmin()) {
        return view('products.show', compact('product'));
    }

    if ($product->is_active) {
        return view('products.show', compact('product'));
    }

    if ($product->winner_id === $user->id) {
        return view('products.show', compact('product'));
    }

 
    abort(403, 'You are not authorized to view this auction.');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
       if (!Auth::user()->isAdmin() || $product->user_id !== Auth::id() || $product->trashed()||$product->is_active == 0 ) {
            abort(403, 'Unauthorized action.');
        }


        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        if (!Auth::user()->isAdmin() || $product->user_id !== Auth::id() || $product->trashed()||$product->is_active == 0 ) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            // 'starting_price' => 'required|numeric|min:0',
            'auction_end_time' => 'required|date|after:now +5 minutes',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'youtube_live_url' => 'nullable',
        ]);
  if ($request->has('remove_image') && $product->image) {
        Storage::disk('public')->delete($product->image);
        $product->image = null;
    }
        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }
 
        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            // 'starting_price' => $validated['starting_price'],
            'auction_end_time' => $validated['auction_end_time'],
             'youtube_live_url' => $validated['youtube_live_url'],
            'image' => $imagePath,
        ]);

         return response()->json([
        'success' => true,
        'redirect' => route('products.show', $product)
    ]);
    }

    public function destroy(Product $product)
    {
        if (!Auth::user()->isAdmin() || $product->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $product->delete();
        return redirect()->route('products.index');
    }

    public function placeBid(Request $request, Product $product)
    {
        if (Auth::user()->isAdmin()) {
            abort(403, 'Admins cannot place bids.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:' . ($product->current_price + 1),
        ]);

        if (!$product->is_active || $product->auction_end_time->isPast()) {
            return back()->with('error', 'Auction has ended.');
        }

        $bid = Bid::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'amount' => $validated['amount'],
        ]);

        $product->update([
            'current_price' => $validated['amount'],
        ]);
       $timeExtended = false;
        $now = Carbon::now('Asia/Kolkata');
        $diffInSeconds = $now->diffInSeconds($product->auction_end_time, false); // use signed diff
        if ($diffInSeconds >= 0 && $diffInSeconds <= 60) {
            $newEndTime = $product->auction_end_time->copy()->addMinute();
            $product->update(['auction_end_time' => $newEndTime]);
            $timeExtended = true;
        }



          event(new BidPlaced($bid, $timeExtended));

       
        return response()->json([
            'success' => true,
            'current_price' => (float)$product->current_price,
            'next_min_bid' => (float)$product->current_price + 1
        ]);

    }

    public function sendMessage(Request $request, Product $product)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $chatMessage = ChatMessage::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'message' => $validated['message'],
        ]);

        event(new NewChatMessage($chatMessage));

        return response()->json(['status' => 'Message sent']);
    }
public function bidderWins()
{
     if (Auth::user()->isAdmin()) {
            abort(403, 'Admins cannot have bids.');
        }
           $userId = Auth::id();
 $products = Product::where('winner_id', $userId)->with(['user', 'highestBid'])
     ->where('is_active', false)
    ->orderByDesc('updated_at');
   $p_count=$products->count();
    $products=$products->get();
        return view('products.index', compact('products','p_count'));
}

public function search(Request $request)
{
    $query = Product::with(['user', 'highestBid.user','bids']);

    if ($request->page_type == 'live') {
        $query->where('is_active', true);
    } elseif ($request->page_type == 'my-wins') {
        $query->where('winner_id', Auth::id())
              ->where('is_active', false);
    }

    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%$search%");
        });
    }

    if ($request->page_type == 'my-wins') {
        $products = $query->orderByDesc('updated_at')->get();
    } else {
        $products = $query->orderBy('is_active', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->get();
    }

   return response()->json([
        'products' => $products,
        'filtered_count' => $products->count(),
    ]);
}
}