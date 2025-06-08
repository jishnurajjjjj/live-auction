<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $users = User::withCount(['products', 'bids', 'wonProducts'])
                    ->where('role','!=','admin')
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);

        return view('users.index', compact('users'));
    }

    public function search(Request $request)
    {
        $users = User::withCount(['products', 'bids', 'wonProducts'])
                     ->where('role','!=','admin')
                    ->when($request->search, function($query, $search) {
                        $query->where(function($q) use ($search) {
                            $q->where('name', 'like', "%$search%")
                              ->orWhere('email', 'like', "%$search%");
                        });
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);

        return response()->json([
        'users' => $users->items(),  
        'currentPage' => $users->currentPage(),
        'perPage' => $users->perPage()
    ]);
    }

  
public function updateStatus(Request $request, User $user)
{
    try {
        $request->validate([
            'is_blocked' => 'required|boolean',
        ]);

        $user->update([
            'is_blocked' => $request->boolean('is_blocked'), 
        ]);


        return response()->json(['success' => true]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
      
        Log::error('User status update failed: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong while updating status.'
        ], 500);
    }
}

}