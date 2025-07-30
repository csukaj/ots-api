<?php

namespace Modules\Stylersmedia\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Modules\Stylersmedia\Entities\Gallery;
use Modules\Stylersmedia\Entities\GalleryEntity;
use Modules\Stylersmedia\Entities\GalleryItem;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * @resource Stylersmedia/GalleryController
 */
class GalleryController extends Controller {

    /**
     * index
     * Display a listing of Galleries
     * @return Response
     */
    public function index(Request $request) {
        $organization = Organization::findOrFail($request->input('organization_id'));
        $devices = $organization->devices;

        $galleries = $organization->galleries;
        foreach ($devices as $device) {
            $galleries = $galleries->merge($device->galleries);
        }

        return [
            'success' => true,
            'data' => GalleryEntity::getCollection($galleries),
            'options' => GalleryEntity::getOptions()
        ];
    }

    /**
     * Show the form for creating a new resource.
     * @hideFromAPIDocumentation
     * @return Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @hideFromAPIDocumentation
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * show
     * Display the specified Gallery
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        return [
            'success' => true,
            'data' => (new GalleryEntity(Gallery::findOrFail($id)))->getFrontendData(),
            'options' => GalleryEntity::getOptions()
        ];
    }

    /**
     * Show the form for editing the specified resource.
     * @hideFromAPIDocumentation
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        //
    }

    /**
     * update
     * Update the specified Gallery
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id) {
        $gallery = Gallery::findOrFail($id);
        $gallery->fill($request->toArray());
        $gallery->name_description_id = $request->name ? (new DescriptionSetter($request->name, $gallery->name_description_id))->set()->id : null;
        $gallery->role_taxonomy_id = $request->role ? Taxonomy::getTaxonomy($request->role, Config::get('taxonomies.gallery_role'))->id : null;
        $gallery->saveOrFail();

        return [
            'success' => true,
            'data' => (new GalleryEntity($gallery))->getFrontendData(),
            'options' => GalleryEntity::getOptions()
        ];
    }

    /**
     * destroy
     * Remove the specified Gallery
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        GalleryItem::where('gallery_id', $id)->delete();
        $gallery = Gallery::findOrFail($id);
        return [
            'success' => (bool) $gallery->delete(),
            'data' => (new GalleryEntity(Gallery::withTrashed()->findOrFail($id)))->getFrontendData(),
            'options' => GalleryEntity::getOptions()
        ];
    }

    /**
     * updatePriority
     * Update the priorities in the specified Gallery
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function updatePriority(Request $request, $id) {
        foreach ($request->input('items') as $item) {
            $galleryItem = GalleryItem::findOrFail($item['id']);
            $galleryItem->priority = $item['priority'];
            $galleryItem->saveOrFail();
        }
        return [
            'success' => true,
            'data' => (new GalleryEntity(Gallery::findOrFail($id)))->getFrontendData(),
            'options' => GalleryEntity::getOptions()
        ];
    }

    /**
     * getOptions
     * Get role and type options
     * @return array
     */
    public function getOptions() {
        return [
            'success' => true,
            'data' => GalleryEntity::getOptions()
        ];
    }

}
