<?php

namespace Modules\Stylerstaxonomy\Http\Controllers;

use App\Exceptions\UserException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Modules\Stylerstaxonomy\Manipulators\TaxonomySetter;
use Nwidart\Modules\Routing\Controller;

/**
 * @resource Stylerstaxonomy/TaxonomyController
 */
class TaxonomyController extends Controller
{

    protected $additions = ['attributes', 'translations'];

    /**
     * Store a new Taxonomy
     * @param Request $request
     * @return JsonResponse
     * @throws UserException
     */
    public function store(Request $request): JsonResponse
    {
        $translations = $request->get('translations', []);
        $translations['en'] = $request->get('name');
        $taxonomy = (new TaxonomySetter($translations, $request->id, $request->parent_id, null,
            $request->all()))->set();
        return response()->json([
            'success' => true,
            'data' => (new TaxonomyEntity($taxonomy))->getFrontendData($this->additions)
        ]);
    }

    /**
     * Update a specified Taxonomy
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws UserException
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $translations = $request->get('translations', []);
        $translations['en'] = $request->get('name');
        $taxonomy = (new TaxonomySetter($translations, $id, $request->parent_id, null,
            $request->except(['id'])))->set();
        return response()->json([
            'success' => true,
            'data' => (new TaxonomyEntity($taxonomy))->getFrontendData($this->additions)
        ]);
    }

    /**
     * Display a specified Taxonomy
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $taxonomy = Taxonomy::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => (new TaxonomyEntity($taxonomy))->getFrontendData($this->additions)
        ]);
    }

    /**
     * Remove a specified Taxonomy
     * @param int $id
     * @return JsonResponse
     * @throws UserException
     */
    public function destroy(int $id): JsonResponse
    {
        $taxonomy = Taxonomy::findOrFail($id);
        try {
            $taxonomy->checkNotActiveRelationExistsOrFail();
        } catch (\Exception $e) {
            throw new UserException('You can\'t delete a taxonomy with active relation! ' . $e->getMessage());
        }
        $count = Taxonomy::destroy($id);
        return response()->json([
            'success' => (bool)$count,
            'data' => (new TaxonomyEntity(Taxonomy::withTrashed()->findOrFail($id)))->getFrontendData($this->additions)
        ]);
    }

    /**
     * List all Taxonomies
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => TaxonomyEntity::getCollection(Taxonomy::all(), $this->additions)
        ]);
    }

    /**
     * List all descendant Taxonomies of the specified Taxonomy
     * @param int $id
     * @return JsonResponse
     */
    public function getDescendants(int $id): JsonResponse
    {
        $taxonomy = Taxonomy::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => TaxonomyEntity::getCollection($taxonomy->getDescendants(), $this->additions)
        ]);
    }

    /**
     * List all child Taxonomies of the specified Taxonomy
     * @param int $id
     * @return JsonResponse
     */
    public function getChildren(int $id = null): JsonResponse
    {
        if (is_null($id)) {
            return response()->json([
                'success' => true,
                'data' => TaxonomyEntity::getCollection(Taxonomy::getRoots(), $this->additions)
            ]);
        }
        $taxonomy = Taxonomy::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => TaxonomyEntity::getCollection($taxonomy->getOrderedChildren(), $this->additions)
        ]);
    }

    /**
     * List all leave Taxonomies of the specified Taxonomy
     * @param int $id
     * @return JsonResponse
     */
    public function getLeaves(int $id): JsonResponse
    {
        $taxonomy = Taxonomy::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => TaxonomyEntity::getCollection($taxonomy->getLeaves(), $this->additions)
        ]);
    }

    /**
     * List all ancestor Taxonomies of the specified Taxonomy
     * @param int $id
     * @return JsonResponse
     */
    public function getAncestors(int $id): JsonResponse
    {
        $taxonomy = Taxonomy::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => TaxonomyEntity::getCollection($taxonomy->getAncestors(), $this->additions)
        ]);
    }

    /**
     * List all ancestor Taxonomies of the specified Taxonomy and the specified Taxonomy itself
     * @param int $id
     * @return JsonResponse
     */
    public function getAncestorsAndSelf(int $id): JsonResponse
    {
        $taxonomy = Taxonomy::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => TaxonomyEntity::getCollection($taxonomy->getAncestorsAndSelf(), $this->additions)
        ]);
    }

    /**
     * List all sibling Taxonomies of the specified Taxonomy
     * @param int $id
     * @return JsonResponse
     */
    public function getSiblings(int $id): JsonResponse
    {
        $taxonomy = Taxonomy::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => TaxonomyEntity::getCollection($taxonomy->getSiblings(), $this->additions)
        ]);
    }

    /**
     * Sets priority of all specified Taxonomies by their order in request
     * @param Request $request
     * @return JsonResponse
     */
    public function setPriorities(Request $request): JsonResponse
    {
        $taxonomyIds = $request->get('taxonomy_ids');
        $i = 1;
        foreach ($taxonomyIds as $taxonomyId) {
            $taxonomy = Taxonomy::findOrFail($taxonomyId);
            $taxonomy->priority = $i++;
            $taxonomy->save();
        }
        $orderedTaxonomies = Taxonomy::whereIn('id',
            $taxonomyIds)->orderBy((new Taxonomy())->getQualifiedOrderColumnName())->get();
        return response()->json([
            'success' => true,
            'data' => TaxonomyEntity::getCollection($orderedTaxonomies, $this->additions)
        ]);
    }

}
