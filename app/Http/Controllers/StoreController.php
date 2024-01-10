<?php

namespace App\Http\Controllers;

use App\Helpers\UploadFileFromBase64;
use App\Http\Requests\CreateStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class StoreController extends Controller
{

    use UploadFileFromBase64;

    public function index(Request $request)
    {
        $stores = Store::paginate($request->get('per_page', 15))->withQueryString();

        return response()->json($stores, Response::HTTP_OK);
    }

    public function store(CreateStoreRequest $request)
    {
        $storeData = $request->validated();

        $store = Store::create($storeData);
        if ($request->has('logo')) {
            $store->logo = $this->storeFile($request, $store, 'logo');
            $store->save();
        }

        if ($request->has('cover')) {
            $store->cover = $this->storeFile($request, $store, 'cover');
            $store->save();
        }

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

        if ($request->has('logo')) {
            $storeData['logo'] = $this->storeFile($request, $store, 'logo');
        }

        if ($request->has('cover')) {
            $storeData['cover'] = $this->storeFile($request, $store, 'cover');
        }

        $store->update($storeData);

        return response()->json($store, Response::HTTP_OK);
    }

    public function delete(int $id)
    {
        $store = Store::findOrFail($id);

        $this->removeFileIfExists($store, 'cover');
        $this->removeFileIfExists($store, 'logo');

        $store->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function storeFile(Request $request, Store $store, string $field)
    {
        $file = $this->uploadFile($request->get($field));
        $extension = Arr::last(explode('/', $file->getMimeType()));
        $fileName = $store->id . "-{$field}" . time() . ".{$extension}";

        $file->storeAs('public/store/' . $store->id, $fileName);
        if ($store->$field) {
            $this->removeFileIfExists($store, $field);
        }
        return $fileName;
    }

    private function removeFileIfExists(Store $store, string $field)
    {
        $executablePath = "{$field}Path";
        if (file_exists($store->$executablePath())) {
            unlink($store->$executablePath());
        }
    }
}
