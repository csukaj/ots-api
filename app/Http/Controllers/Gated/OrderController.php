<?php

namespace App\Http\Controllers\Gated;

use App\Entities\OrderEntity;
use App\Facades\Config;
use App\Http\Controllers\ResourceController;
use App\Http\Requests\EmbeddedOrderSendRequest;
use App\Order;
use App\Services\OrderStatusHandlerService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * @resource Admin/OrderController
 */
class OrderController extends ResourceController
{
    private $enabledSites = [];
    private $showAllSite = false;

    /**
     * index
     * Display a listing of Orders
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws JWTException
     */
    public function index(): JsonResponse
    {
        if (!$this->gateAllows('access-order')) {
            throw new AuthorizationException('Permission denied.');
        }
        $this->configAllowedSites();
        $query = ($this->showAllSite) ? Order::orderBy('created_at', 'desc') : Order::whereIn('site', $this->enabledSites);
        return response()->json([
            'success' => true,
            'data' => OrderEntity::getCollection($query->orderBy('created_at', 'desc')->get())
        ]);
    }

    /**
     * show
     * Display the specified Order
     * @param  int $id
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws JWTException
     */
    public function show($id): JsonResponse
    {

        $this->configAllowedSites();
        $order = Order::findOrFail($id);

        if (!$this->gateAllows('access-order') || !$this->isOrderAllowed($order)) {
            throw new AuthorizationException('Permission denied.');
        }

        $orderStatuses = [];
        foreach (Config::get('taxonomies.order_statuses') as $status) {
            $orderStatuses[] = $status;
        }

        return response()->json([
            'success' => true,
            'data' => (new OrderEntity($order))->getFrontendData(),
            'order_statuses' => $orderStatuses
        ]);
    }

    /**
     * destroy
     * Remove the specified Order
     * @param  int $id
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws JWTException
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        $this->configAllowedSites();
        $order = Order::findOrFail($id);

        if (!$this->gateAllows('access-order') || !$this->isOrderAllowed($order)) {
            throw new AuthorizationException('Permission denied.');
        }

        // increase availability
        //availability is set manually (client request - industry standard) - G 180806


        return response()->json([
            'success' => $order->delete(),
            'data' => (new OrderEntity(Order::withTrashed()->findOrFail($id)))->getFrontendData()
        ]);
    }

    /**
     * @throws JWTException
     */
    private function configAllowedSites()
    {
        $user = $this->getAuthUser();
        $this->showAllSite = $user->hasRole('admin');
        $this->enabledSites = ($user->sites) ? $user->sites->pluck('site') : [];
    }

    private function isOrderAllowed(Order $order): bool
    {
        return $this->showAllSite || $this->enabledSites->contains($order->site);
    }

    /**
     * @param EmbeddedOrderSendRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function setStatus(EmbeddedOrderSendRequest $request)
    {
        $txName = Taxonomy::findOrFail($request->targetStatus)->name;
        $targetStatusName = str_replace(' ', '_', strtoupper($txName));
        $service = (new OrderStatusHandlerService($request));
        $model = $request->input('model');
        if ($model && !empty($model['id'])) {
            $order = Order::findOrFail($model['id']);
            $service->setOrder($order);
        }
        $service->stepStatus($targetStatusName);

        return response()->json(['success' => true]);
    }

}
