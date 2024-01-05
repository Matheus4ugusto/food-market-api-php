<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class StoreController extends Controller
{
    public function index(Request $request)
    {
        $stores = Store::paginate($request->get('per_page', 15))->withQueryString();

        return response()->json($stores, Response::HTTP_OK);
    }

    public function store(CreateUserRequest $request)
    {
        $storeData = $request->validated();

        unset($soteData['logo']);
        unset($storeData['cover']);

        $store = Store::create($storeData);

        return response()->json($store, Response::HTTP_CREATED);
    }

    public function show(int $id)
    {
        $store = Store::findOrFail($id);

        return response()->json($store, Response::HTTP_OK);
    }

    public function update(int $id, UpdateStoreRequest $request)
    {
        $store = Store::findOrFail($id);
        $storeData = $request->validated();

        unset($storeData['logo']);
        unset($storeData['cover']);

        $store->update($storeData);

        return response()->json($store, Response::HTTP_OK);
    }

    public function delete(int $id)
    {
        $store = Store::findOrFail($id);

        $store->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
