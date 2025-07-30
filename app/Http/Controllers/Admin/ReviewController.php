<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ReviewEntity;
use App\Entities\UserEntity;
use App\Exceptions\UserException;
use App\Http\Controllers\ResourceController;
use App\Manipulators\ReviewSetter;
use App\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource Admin/ReviewController
 */
class ReviewController extends ResourceController
{

    private static $additions = ['admin'];

    /**
     * index
     * Display a listing of Review & Review names
     * @param Request|null $request
     * @return JsonResponse
     */
    public function index(Request $request = null): JsonResponse
    {
        $reviews = Review
            ::forSubject($request->input('review_subject_type'), $request->input('review_subject_id'))
            ->orderBy('id')->get();

        return response()->json([
            'success' => true,
            'data' => ReviewEntity::getCollection($reviews, self::$additions)
        ]);
    }

    /**
     * store
     * Store a newly created Review
     * @param  Request $request
     * @return JsonResponse
     * @throws UserException
     * @throws \Throwable
     */
    public function store(Request $request): JsonResponse
    {
        $user = $this->getAuthUser();
        $request->merge(['user' => (new UserEntity($user))->getFrontendData()]);
        $review = (new ReviewSetter($request->all()))->set();
        return response()->json([
            'success' => true,
            'data' => (new ReviewEntity($review))->getFrontendData(self::$additions)
        ]);
    }

    /**
     * show
     * Display the specified Review
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $review = Review::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => (new ReviewEntity($review))->getFrontendData(self::$additions)
        ]);
    }

    /**
     * destroy
     * Remove the specified Review
     * @param  int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        return response()->json([
            'success' => (bool)Review::destroy($id),
            'data' => []
        ]);
    }
}
