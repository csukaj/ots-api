<?php

namespace App\Entities;

use App\Availability;
use App\Facades\Config;
use App\Manipulators\AvailabilitySetter;
use DateInterval;
use DateTime;

class AvailabilityEntity
{

    public $availableType;
    public $availableId;

    public function __construct(string $availableType, int $availableId)
    {
        $this->availableType = $availableType;
        $this->availableId = $availableId;
    }

    /**
     * Gets all availabilities of a model between two dates, returns a daily breakdown
     * @param string $fromDate
     * @param string $toDate
     * @return array
     * @throws \Exception
     */
    public function get(string $fromDate, string $toDate): array
    {
        $dailyAvailabilities = [];

        $availabilities = Availability::getAvailabilitiesInInterval($this->availableType, $this->availableId, $fromDate,
            $toDate);
        $toTime = new DateTime($toDate . ' ' . Config::getOrFail('ots.midday_separation_time'));

        $iterationTime = new DateTime($fromDate . ' ' . Config::getOrFail('ots.midday_separation_time'));
        $availability = null;
        $availabilityFromTime = null;
        $availabilityToTime = null;
        $availabilityAmount = null;

        do {
            if (is_null($availabilityFromTime) || (!is_null($availabilityToTime) && $availabilityToTime <= $iterationTime)) {
                $availability = $availabilities->shift();
                $availabilityFromTime = is_null($availability) ? null : new DateTime($availability->from_time);
                $availabilityToTime = (is_null($availability) || is_null($availability->to_time)) ? null : new DateTime($availability->to_time);
                $availabilityAmount = is_null($availability) ? 0 : $availability->amount;
            }

            $amount = ($availabilityFromTime <= $iterationTime && (is_null($availabilityToTime) || $availabilityToTime > $iterationTime)) ? $availabilityAmount : 0;
            $dailyAvailabilities[] = [
                'year' => (int)$iterationTime->format('Y'),
                'month' => (int)$iterationTime->format('m'),
                'day' => (int)$iterationTime->format('d'),
                'amount' => (int)$amount
            ];

            $iterationTime->add(new DateInterval('P1D'));
        } while ($iterationTime <= $toTime);

        return $dailyAvailabilities;
    }

    /**
     * Sets new availabilities using a request of daily breakdowns
     * @param array $dailyAvailabilitiesRequest
     * @return bool
     * @throws \Throwable
     */
    public function set(array $dailyAvailabilitiesRequest): bool
    {
        foreach ($this->parseAndMergeDailyAvailabilitiesRequest($dailyAvailabilitiesRequest) as $availabilityData) {
            (new AvailabilitySetter($availabilityData))->set();
        }
        return true;
    }

    /**
     * Merges daily breakdowns into long intervals and parses request data into setter-ready format
     * @param array $dailyAvailabilitiesRequest
     * @return array
     * @throws \Exception
     */
    private function parseAndMergeDailyAvailabilitiesRequest(array $dailyAvailabilitiesRequest): array
    {
        $availabilitiesData = [];
        foreach ($dailyAvailabilitiesRequest as $dailyAvailabilityRequest) {
            $dailyAvailabilityData = $this->parseAvailabilityRequest($dailyAvailabilityRequest);

            if (empty($availabilitiesData)) {
                $availabilitiesData[] = $dailyAvailabilityData;
                continue;
            }

            $lastAvailabilityData = $availabilitiesData[count($availabilitiesData) - 1];
            if ($dailyAvailabilityData['fromDate'] == $lastAvailabilityData['toDate'] && $dailyAvailabilityData['amount'] == $lastAvailabilityData['amount']) {
                $lastAvailabilityData['toDate'] = $dailyAvailabilityData['toDate'];
                $availabilitiesData[count($availabilitiesData) - 1] = $lastAvailabilityData;
            } else {
                $availabilitiesData[] = $dailyAvailabilityData;
            }
        }
        return $availabilitiesData;
    }

    /**
     * Parses request data into setter-ready format
     * @param array $availabilityRequest
     * @return array
     * @throws \Exception
     */
    private function parseAvailabilityRequest(array $availabilityRequest): array
    {
        $date = (new DateTime())->setDate($availabilityRequest['year'], $availabilityRequest['month'],
            $availabilityRequest['day']);
        return [
            'availableType' => $this->availableType,
            'availableId' => $this->availableId,
            'fromDate' => $date->format('Y-m-d'),
            'toDate' => $date->add(new DateInterval('P1D'))->format('Y-m-d'),
            'amount' => $availabilityRequest['amount']
        ];
    }

}
