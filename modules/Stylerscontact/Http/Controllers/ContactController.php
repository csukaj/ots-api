<?php

namespace Modules\Stylerscontact\Http\Controllers;


use App\Exceptions\UserException;
use App\Facades\Config;
use App\Http\Controllers\ResourceController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Stylerscontact\Entities\Contact;
use Modules\Stylerscontact\Entities\ContactEntity;
use Modules\Stylerscontact\Manipulators\ContactSetter;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class ContactController extends ResourceController
{
    public function __construct()
    {

    }

    /**
     * Display a listing of the resource.
     *
     * @param Request|null $request
     * @return JsonResponse
     */
    public function index(Request $request = null): JsonResponse
    {
        $contacts = Contact::forContactable($request->get('contactable_type'), $request->get('contactable_id'))->get();
        return response()->json([
            'success' => true,
            'data' => ContactEntity::getCollection($contacts, ['contactable']),
            'contact_types' => $this->getContactTypeTaxonomies()
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return JsonResponse
     * @throws UserException
     */
    public function store(Request $request): JsonResponse
    {
        $contact = (new ContactSetter($request->all()))->set();
        return response()->json([
            'success' => true,
            'data' => (new ContactEntity($contact))->getFrontendData(['contactable']),
            'contact_types' => $this->getContactTypeTaxonomies()
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new ContactEntity(Contact::findOrFail($id)))->getFrontendData(['contactable']),
            'contact_types' => $this->getContactTypeTaxonomies()
        ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     * @throws UserException
     */
    public function update(Request $request, $id): JsonResponse
    {
        $contact = (new ContactSetter($request->all()))->set();
        return response()->json([
            'success' => true,
            'data' => (new ContactEntity($contact))->getFrontendData(['contactable']),
            'contact_types' => $this->getContactTypeTaxonomies()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        return response()->json([
            'success' => Contact::findOrFail($id)->delete(),
            'data' => [],
            'contact_types' => $this->getContactTypeTaxonomies()
        ]);
    }

    /**
     * @return array
     */
    private function getContactTypeTaxonomies(): array
    {
        return TaxonomyEntity::getCollection(Taxonomy::findOrFail(Config::get('taxonomies.contact_type'))->getChildren(),
            ['translations_with_plurals']);
    }
}

