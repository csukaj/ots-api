<?php

namespace App\Manipulators;

use App\Availability;
use App\Device;
use App\Exceptions\UserException;
use App\Organization;
use App\ShipGroup;
use Exception;

/**
 * Manipulator to create a new Availability
 * instance after the supplied data passes validation
 */
class AvailabilitySetter
{

    /**
     * Attributes that can be set from input
     */

    protected $availableType;
    protected $availableId;
    protected $fromDate;
    protected $toDate;
    private $amount;
    private $fromTime;
    private $toTime;
    private $mainAvailability;
    private $availableModel;
    private $maxAmount;

    /**
     * AvailabilitySetter constructor.
     * @param array $attributes
     * @throws Exception
     */
    public function __construct(array $attributes = [])
    {
        //TODO: use $properties array and extend BaseSetter
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        $this->fromTime = Availability::getSeparationTime($this->fromDate);
        $this->toTime = Availability::getSeparationTime($this->toDate);
        $this->availableModel = $this->availableType::findOrFail($this->availableId);
        $this->maxAmount = $this->getAvailableModelMaxAmount($this->availableModel);
        if (isset($attributes['amount'])) {
            $this->amount = $this->getCheckedAmount(intval($this->amount));
        }
    }

    /**
     * Creates new availability model and modifies all overlapping availabilities accordingly
     * @throws \Throwable
     */
    public function set(): Availability
    {
        if (!is_null($this->toTime) && $this->fromTime >= $this->toTime) {
            throw new UserException('Availability could not be set (fromDate is later than toDate): ' . print_r(get_object_vars($this), true));
        }
        $this->hasNoneOrCreateInfinite();
        if ($this->hasIdentical() || $this->hasInfiniteInterval() || $this->hasNormalIntervals()) {
            $this->mergeAvailabilities();
            return $this->mainAvailability;
        }
        throw new Exception('Availability could not be set: ' . print_r(get_object_vars($this), true));
    }

    /**
     * Modifies many availability interval by any number given
     * @param int $modification
     * @return boolean
     * @throws Exception
     * @throws \Throwable
     */
    public function modify(int $modification): bool
    {
        $overLappingAvailability = null;
        if (!is_null($this->toTime)) {
            $availabilities = Availability::getAvailabilitiesInInterval($this->availableType, $this->availableId,
                $this->fromDate, $this->toDate);
        } else {
            $availabilities = Availability::getAvailabilitiesToInfinity($this->availableType, $this->availableId,
                $this->fromDate);
            $overLappingAvailabilities = Availability::getAvailabilitiesToInfinity($this->availableType, $this->availableId,
                $this->fromDate, true);
            $overLappingAvailability = $overLappingAvailabilities->diff($availabilities)->first();
        }
        if ($availabilities->isEmpty() && !$overLappingAvailability) {
            return false;
        }
        foreach ($availabilities as $availability) {
            $availability->amount = $this->getCheckedAmount($availability->amount + $modification);
            $availability->saveOrFail();
        }
        if ($overLappingAvailability) {
            (new self([
                'availableType' => $this->availableType,
                'availableId' => $this->availableId,
                'fromDate' => $this->fromDate,
                'toDate' => $overLappingAvailability->to_time,
                'amount' => $this->getCheckedAmount($overLappingAvailability->amount + $modification)
            ]))->set();
        }
        return true;
    }

    /**
     * @param Availability $availability
     * @throws \Throwable
     */
    protected function setMainAvailability(Availability $availability)
    {
        $availability->saveOrFail();
        $this->mainAvailability = $availability;
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    private function hasNoneOrCreateInfinite(): bool
    {
        if (Availability::hasAvailabilities($this->availableType, $this->availableId)) {
            return false;
        }

        $this->setMainAvailability($this->createInfiniteInterval(Availability::getSeparationTime(date('Y-m-d')), 0));
        return true;
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    private function hasIdentical(): bool
    {
        $availability = Availability
            ::forAvailable($this->availableType, $this->availableId)
            ->where('from_time', '=', $this->fromTime)
            ->where('to_time', '=', $this->toTime)
            ->first();

        if (!$availability) {
            return false;
        }

        $availability->amount = $this->getCheckedAmount($this->amount);
        $this->setMainAvailability($availability);

        return true;
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    private function hasInfiniteInterval(): bool
    {
        $infiniteAvailability = Availability::getOverallInfiniteInterval($this->availableType, $this->availableId,
            $this->fromTime);

        if (!$infiniteAvailability) {
            return false;
        }

        if ($infiniteAvailability->from_time == $this->fromDate) {
            // update infinity to main
            $availability = Availability::findOrFail($infiniteAvailability->id);
            $availability->to_time = $this->toTime;
            $availability->amount = $this->getCheckedAmount($this->amount);
            $this->setMainAvailability($availability);
        } else {
            // update infinity
            $partAvailability = Availability::findOrFail($infiniteAvailability->id);
            $partAvailability->to_time = $this->fromTime;
            $partAvailability->saveOrFail();

            // create main
            $this->setMainAvailability(new Availability([
                'available_type' => $this->availableType,
                'available_id' => $this->availableId,
                'from_time' => $this->fromTime,
                'to_time' => $this->toTime,
                'amount' => $this->getCheckedAmount($this->amount)
            ]));
        }

        // create infinity
        if (!is_null($this->mainAvailability->to_time)) {
            $this->createInfiniteInterval($this->mainAvailability->to_time, $infiniteAvailability->amount);
        }

        return true;
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    private function hasNormalIntervals(): bool
    {
        // get start point's interval
        $overallStartPointInterval = Availability::getStartInterval(
            $this->availableType,
            $this->availableId,
            $this->fromTime
        );

        // get end point's interval
        $overallEndPointInterval = Availability::getEndInterval(
            $this->availableType,
            $this->availableId,
            $this->toTime
        );

        if (!($overallStartPointInterval && $overallEndPointInterval)) {
            return false;
        }

        if ($overallStartPointInterval->id == $overallEndPointInterval->id) {
            if ($overallStartPointInterval->from_time < $this->fromDate) {
                $beforeStartPoint = new Availability([
                    'from_time' => $overallStartPointInterval->from_time,
                    'to_time' => $this->fromTime,
                    'amount' => $this->getCheckedAmount($overallStartPointInterval->amount),
                    'available_type' => $this->availableType,
                    'available_id' => $this->availableId
                ]);
                $beforeStartPoint->saveOrFail();
            }

            if ($overallEndPointInterval->to_time > $this->toDate) {
                $afterEndPoint = new Availability([
                    'from_time' => $this->toTime,
                    'to_time' => $overallEndPointInterval->to_time,
                    'amount' => $this->getCheckedAmount($overallEndPointInterval->amount),
                    'available_type' => $this->availableType,
                    'available_id' => $this->availableId
                ]);
                $afterEndPoint->saveOrFail();
            }

            $this->setMainAvailability(new Availability([
                'from_time' => $this->fromTime,
                'to_time' => $this->toTime,
                'amount' => $this->getCheckedAmount($this->amount),
                'available_type' => $this->availableType,
                'available_id' => $this->availableId
            ]));

            Availability::find($overallStartPointInterval->id)->delete();

        } elseif ($overallStartPointInterval->from_time == $this->fromDate && $overallEndPointInterval->to_time != $this->toDate) {
            $availability = Availability::find($overallStartPointInterval->id);
            $availability->to_time = $this->toTime;
            $availability->amount = $this->getCheckedAmount($this->amount);
            $this->setMainAvailability($availability);

            $afterEndPoint = Availability::find($overallEndPointInterval->id);
            $afterEndPoint->from_time = $this->toTime;
            $afterEndPoint->saveOrFail();

        } elseif ($overallStartPointInterval->from_time == $this->fromDate && $overallEndPointInterval->to_time == $this->toDate) {
            $availability = Availability::find($overallStartPointInterval->id);
            $availability->to_time = $this->toTime;
            $availability->amount = $this->getCheckedAmount($this->amount);
            $this->setMainAvailability($availability);

            Availability::find($overallEndPointInterval->id)->delete();

        } else {
            $beforeStartPoint = Availability::find($overallStartPointInterval->id);
            $beforeStartPoint->to_time = $this->fromTime;
            $beforeStartPoint->saveOrFail();

            $afterEndPoint = Availability::find($overallEndPointInterval->id);
            $afterEndPoint->from_time = $this->toTime;
            $afterEndPoint->saveOrFail();

            $this->setMainAvailability(new Availability([
                'from_time' => $this->fromTime,
                'to_time' => $this->toTime,
                'amount' => $this->getCheckedAmount($this->amount),
                'available_type' => $this->availableType,
                'available_id' => $this->availableId
            ]));

        }

        $this->deleteUnusedIntervals();

        return true;
    }

    /**
     * @throws Exception
     */
    private function deleteUnusedIntervals()
    {
        $allAvailabilities = Availability::getAvailabilitiesInInterval($this->availableType, $this->availableId,
            $this->fromDate, $this->toDate);
        foreach ($allAvailabilities as $availability) {
            if (
                $availability->from_time != $this->fromDate
                && $availability->from_time != $this->toDate
                && $availability->to_time != $this->fromDate
                && $availability->to_time != $this->toDate
            ) {
                $availability->delete();
            }
        }
    }

    /**
     * @param $from_time
     * @param $amount
     * @return Availability
     * @throws UserException
     * @throws \Throwable
     */
    private function createInfiniteInterval($from_time, $amount): Availability
    {
        $availability = new Availability([
            'available_type' => $this->availableType,
            'available_id' => $this->availableId,
            'from_time' => $from_time,
            'to_time' => null,
            'amount' => $this->getCheckedAmount($amount)
        ]);
        $availability->saveOrFail();
        return $availability;
    }

    private function mergeAvailabilities()
    {
        $beforeStartPoint = Availability
            ::forAvailable($this->availableType, $this->availableId)
            ->where('to_time', $this->fromTime)
            ->where('amount', $this->amount)
            ->first();

        $afterStartPoint = Availability
            ::forAvailable($this->availableType, $this->availableId)
            ->where('from_time', $this->toTime)
            ->where('amount', $this->amount)
            ->first();

        if ($beforeStartPoint && $afterStartPoint) {
            $beforeStartPoint->to_time = ($afterStartPoint->to_time) ? $afterStartPoint->to_time : null;
            $beforeStartPoint->save();
            $this->mainAvailability->delete();
            $afterStartPoint->delete();
            $this->mainAvailability = $beforeStartPoint;
        } elseif ($beforeStartPoint) {
            $beforeStartPoint->to_time = $this->toTime;
            $beforeStartPoint->save();
            $this->mainAvailability->delete();
            $this->mainAvailability = $beforeStartPoint;
        } elseif ($afterStartPoint) {
            $afterStartPoint->from_time = $this->fromTime;
            $afterStartPoint->save();
            $this->mainAvailability->delete();
            $this->mainAvailability = $afterStartPoint;
        }

    }

    /**
     * @param int $amount
     * @return mixed
     * @throws UserException
     */
    private function getCheckedAmount(int $amount)
    {
        if (is_null($amount)) {
            throw new UserException('Amount is null');
        }
        return max(min($this->maxAmount, $amount), 0);
    }

    private function getAvailableModelMaxAmount(Device $availableModel): int
    {
        $maximumAmount = 0;
        switch ($availableModel->deviceable_type) {
            case Organization::class:
                $maximumAmount = $availableModel->amount;
                break;
            case ShipGroup::class:
                $shipGroup = ShipGroup::find($availableModel->deviceable_id);
                $maximumAmount = $availableModel->amount * $shipGroup->getShipCount();
                break;
        }
        return $maximumAmount;
    }

}
