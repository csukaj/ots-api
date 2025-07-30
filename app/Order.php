<?php

namespace App;

use App\Facades\Config;
use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Order
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $nationality
 * @property string $email
 * @property string $telephone
 * @property string $remarks
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Collection|OrderItem[] $items
 * @mixin \Eloquent
 * @property int|null $status_taxonomy_id
 * @property string|null $site
 * @property string|null $status_log
 * @property string|null $token
 * @property string|null $token_created_at
 * @property bool $payment_closed
 * @property string|null $billing_country
 * @property string|null $billing_zip
 * @property string|null $billing_settlement
 * @property string|null $billing_address
 * @property string|null $invoice
 * @property int|null $order_type_taxonomy_id
 * @property string|null $company_name
 * @property string|null $tax_number
 * @property int $billing_type_taxonomy_id
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $billingType
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $orderType
 * @property-read \App\Payment $payment
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $statusTx
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Order onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereBillingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereBillingCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereBillingSettlement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereBillingTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereBillingZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereNationality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereOrderTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order wherePaymentClosed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereSite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereStatusLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereStatusTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereTaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereTokenCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Order withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Order withoutTrashed()
 */
class Order extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'tax_number',
        'nationality',
        'email',
        'telephone',
        'remarks',
        'json',
        'status_taxonomy_id',
        'site',
        'status_log',
        'token',
        'token_created_at',
        'local_storage',
        'billing_country',
        'billing_zip',
        'billing_settlement',
        'billing_address',
        'billing_type_taxonomy_id'
    ];

    protected $cascadeDeletes = ['items'];

    /**
     * Relation to type taxonomy
     *
     * @return HasOne
     */
    public function statusTx(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'status_taxonomy_id');
    }

    /**
     * Relation to order type taxonomy
     *
     * @return HasOne
     */
    public function orderType(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'order_type_taxonomy_id');
    }

    /**
     * Relation to order type taxonomy
     *
     * @return HasOne
     */
    public function billingType(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'billing_type_taxonomy_id');
    }

    /**
     * Relation to price
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::Class)
            ->where('parent_id', '=', null)
            ->orderBy('created_at', 'desc');
    }

    public function billingName()
    {
        if ($this->billing_type_taxonomy_id == Config::getOrFail('taxonomies.billing_types.individual')) {
            $billingName = $this->fullName();
        } else {
            $billingName = $this->company_name;
        }

        return $billingName;
    }

    public function isCompanyOrder()
    {
        return $this->billing_type_taxonomy_id == Config::getOrFail('taxonomies.billing_types.company');
    }

    public function fullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function language()
    {
        return Config::getOrFail('ots.site_languages')[$this->site];
    }

    public function locale()
    {
        return Config::getOrFail('ots.site_locales')[$this->site];
    }

    /**
     * It calculate the order item's GROSS total
     *
     * (QUANTITY [named in project "amount"] * ITEM PRICE * TAX
     *
     * @return float
     */
    public function itemsTotalGross(): float
    {
        $total = 0;

        foreach ($this->items as $item) {
            $total += $item->sumGrossPrice();
        }

        return $total;
    }

    public function getDeviceListForAvailability(): array
    {
        $availabilityConfigs = [];
        foreach ($this->items as $item) {
            if (!(Device::find($item->order_itemable_id))) {
                continue;
            }
            if ($item->order_itemable_type == Device::class) {
                $availabilityConfigs[] = [
                    'from_date' => $item->from_date,
                    'to_date' => $item->to_date,
                    'order_itemable_type' => Device::class,
                    'order_itemable_id' => $item->order_itemable_id,
                    'amount' => $item->amount
                ];
            } else {
                $shipGroup = ShipGroup::findOrFail($item->order_itemable_id);
                foreach ($shipGroup->devices as $device) {
                    $availabilityConfigs[] = [
                        'from_date' => $item->from_date,
                        'to_date' => $item->to_date,
                        'order_itemable_type' => Device::class,
                        'order_itemable_id' => $device->id,
                        'amount' => ($device->amount * $item->amount)
                    ];
                }
            }
        }

        return $availabilityConfigs;
    }

    public static function setStatus($id, $status): self
    {
        $order = self::findOrFail($id);
        $order->status_taxonomy_id = $status;
        $order->save();
        return $order;
    }

    public static function setToken($id, $token): self
    {
        $order = self::findOrFail($id);
        $order->token = $token;
        $order->token_created_at = Carbon::now();
        $order->save();
        return $order;
    }

    /**
     * @todo Create unit tests!!
     * @param $token
     * @param array $options
     * @return mixed
     */
    public static function getOrderByToken($token, $options = [])
    {
        $defaults = [
            'checkPaymentClosed' => true,
            'checkTokenCreatedAt' => true
        ];

        $settings = array_merge($defaults, $options);

        $conditions = [];
        $conditions[] = ['token', '=', $token];

        if ($settings['checkPaymentClosed']) {
            $conditions[] = ['payment_closed', false];
        }

        if ($settings['checkTokenCreatedAt']) {
            $intervalStart = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . " -1 day"));
            $conditions[] = ['token_created_at', '>', $intervalStart];
        }

        return self::where($conditions)->first();
    }
}
