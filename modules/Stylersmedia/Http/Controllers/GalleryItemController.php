<?php

namespace Modules\Stylersmedia\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Stylersmedia\Entities\File;
use Modules\Stylersmedia\Entities\FileEntity;
use Modules\Stylersmedia\Entities\GalleryItem;
use Modules\Stylersmedia\Manipulators\FileSetter;

/**
 * @resource Stylersmedia/GalleryController
 */
class GalleryItemController extends Controller
{

    /**
     * @hideFromAPIDocumentation
     */
    public function index()
    {
        //
    }

    /**
     * @hideFromAPIDocumentation
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * @hideFromAPIDocumentation
     */
    public function edit($id)
    {
        //
    }

    /**
     * store
     * Store a newly created GalleryItem
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $file = (new FileSetter($request->all()))->set();
        $galleryItem = new GalleryItem();
        $galleryItem->file_id = $file->id;
        $galleryItem->gallery_id = $request->input('gallery_id');
        $galleryItem->is_highlighted = $request->input('highlighted');
        $galleryItem->saveOrFail();
        return ['success' => true, 'data' => (new FileEntity($file))->getFrontendData(['gallery_item'])];
    }

    /**
     * show
     * Display the specified File of a GalleryItem
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        return [
            'success' => true,
            'data' => (new FileEntity(File::findOrFail($id)))->getFrontendData(['gallery_item'])
        ];
    }

    /**
     * update
     * Update the specified GalleryItem by File ID
     * @param  Request $request
     * @param  int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $attributes = $request->all();
        $attributes['id'] = $id;
        if ($request->exists('highlighted')) {
            $galleryItem = GalleryItem::where('file_id', $id)->where('gallery_id',
                $request->input('gallery_id'))->firstOrFail();
            $galleryItem->is_highlighted = $request->input('highlighted');
            $galleryItem->saveOrFail();
        }
        $file = (new FileSetter($attributes))->set();
        return ['success' => true, 'data' => (new FileEntity($file))->getFrontendData(['gallery_item'])];
    }

    /**
     * destroy
     * Remove the specified GalleryItem & File by File ID
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        GalleryItem::where('file_id', $id)->first()->delete();
        $file = File::findOrFail($id);
        return [
            'success' => (bool)$file->delete(),
            'data' => (new FileEntity(File::withTrashed()->findOrFail($id)))->getFrontendData(['gallery_item'])
        ];
    }

    /**
     * upload
     * Upload a new file & create a new GalleryItem in the specified Gallery
     * @param Request $request
     * @return array
     * @throws \App\Exceptions\UserException
     * @throws \Throwable
     */
    public function upload(Request $request)
    {
        $file = (new FileSetter($request->toArray()))->setBySymfonyFile($request->file('file'));

        $galleryItem = new GalleryItem();
        $galleryItem->file_id = $file->id;
        $galleryItem->gallery_id = $request->input('gallery_id');
        $galleryItem->saveOrFail();

        $file = $file->fresh();

        return ['success' => true, 'data' => (new FileEntity($file))->getFrontendData(['gallery_item'])];
    }

}
