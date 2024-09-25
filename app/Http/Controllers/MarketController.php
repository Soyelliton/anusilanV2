<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Market;
use App\Models\Daily;
use Illuminate\Support\Facades\Validator;

class MarketController extends Controller
{
    // GET /api/market
    public function index()
    {
        // if (auth()->guest()) {
        //     return response()->json(['message' => 'Unauthorized'], 401);
        // }

        $markets = Market::all();
        return response()->json(['title' => 'Market Management', 'market' => $markets], 200);
    }

    // POST /api/market
    public function addMarket(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'mname' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Create Market
        $market = Market::create([
            'mname' => $request->mname,
        ]);

        // Update daily table
        $currentData = Daily::where('date', now()->format('Y-m-d'))->first();

        if ($currentData) {
            $currentData->increment('market_in');
            $currentData->increment('market_exist');
            $currentData->save();
        }

        if ($market) {
            return response()->json(['message' => 'Market has been created!'], 201);
        } else {
            return response()->json(['message' => 'Something went wrong. Market cannot be created!'], 500);
        }
    }

    // DELETE /api/market/{id}
    public function delete($id)
    {
        $market = Market::find($id);

        if (!$market) {
            return response()->json(['message' => 'Market not found!'], 404);
        }

        $market->delete();

        // Update daily table
        $currentData = Daily::where('date', now()->format('Y-m-d'))->first();

        if ($currentData) {
            $currentData->decrement('market_in');
            $currentData->increment('market_out');
            $currentData->decrement('market_exist');
            $currentData->save();
        }

        return response()->json(['message' => 'Market has been deleted!'], 200);
    }
}
