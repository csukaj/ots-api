<?php

namespace App\Entities\Search;

use App\Entities\Entity;
use App\Exceptions\UserException;
use DateTime;

class AbstractSearchEntity extends Entity
{

    protected $parameters = [
        'interval' => null,
        'usages' => null,
        'booking_date' => null,
        'wedding_date' => null,
        'cart_summary' => null,
        'returning_client' => null,
    ];
    protected $hasValidInterval = false;
    protected $displayMargins = false;
    protected $showInactive = false;

    public function isRequestParametersValid($parameters): bool
    {
        try {
            $this->setParameters($parameters);
            return true;
        } catch (UserException $userException) {
            return false;
        }
    }

    /**
     * @param $parameters
     * @return $this
     * @throws UserException
     */
    public function setParameters($parameters)
    {
        if (!is_array($parameters) || empty($parameters)) {
            return $this;
        }

        if (!empty($parameters['interval'])) {
            $this->validateInterval($parameters['interval'], $parameters['booking_date'] ?? null);
            $this->parameters['interval'] = $parameters['interval'];
        }

        foreach ($parameters['usages'] as $index => &$roomParameters) {
            $this->validateRoomParameters($roomParameters, $index);
            foreach ($roomParameters['usage'] as &$usageParameters) {
                $this->filterUsageParameters($usageParameters);
            }
            $this->parameters['usages'] = $parameters['usages'];
        }

        if (!empty($parameters['booking_date'])) {
            $this->validateDate($parameters['booking_date']);
            $this->parameters['booking_date'] = $parameters['booking_date'];
        } else {
            $this->parameters['booking_date'] = date('Y-m-d');
        }

        if (!empty($parameters['wedding_date'])) {
            $this->validateDate($parameters['wedding_date']);
            $this->parameters['wedding_date'] = $parameters['wedding_date'];
        }

        $this->parameters['cart_summary'] = !empty($parameters['cart_summary']) ? $parameters['cart_summary'] : null;
        $this->parameters['returning_client'] = !empty($parameters['returning_client']);

        return $this;
    }

    public function calculateMargins()
    {
        $this->displayMargins = true;
        return $this;
    }

    /**
     * @param $dateInput
     * @return bool|DateTime
     * @throws UserException
     */
    protected function validateDate($dateInput)
    {
        $date = DateTime::createFromFormat('Y-m-d', $dateInput);
        if (count(DateTime::getLastErrors()['errors']) > 0) {
            throw new UserException('Invalid interval!');
        }
        $date->setTime(0, 0, 0);
        return $date;
    }

    /**
     * @param $interval
     * @param $booking_date
     * @return bool
     * @throws UserException
     */
    protected function validateInterval($interval, $booking_date = null)
    {
        if (empty($interval['date_from']) && empty($interval['date_to'])) {
            return true; // we can search without specified dates
        }

        if (empty($interval['date_from']) || empty($interval['date_to'])) {
            throw new UserException('Invalid interval!');
        }

        $fromDate = $this->validateDate($interval['date_from']);
        $toDate = $this->validateDate($interval['date_to']);

        if ($fromDate >= $toDate) {
            throw new UserException('Invalid interval!');
        }

        $today = new DateTime();
        $today->setTime(0, 0, 0);

        if ($fromDate < $today && empty($booking_date)) {
            throw new UserException('search.error.fromDateIsBeforeToday');
        }

        $this->hasValidInterval = true;
        return true;
    }

    /**
     * @param $usageParameters
     * @param $index
     * @throws UserException
     */
    protected function validateUsageParameters($usageParameters, $index)
    {
        if (!is_numeric($usageParameters['age']) || $usageParameters['age'] < 0 || strpos($usageParameters['age'],
                '.') !== false) {
            throw new UserException('Invalid age range!');
        }

        if (!is_numeric($usageParameters['amount']) || $usageParameters['amount'] <= 0 || strpos($usageParameters['amount'],
                '.') !== false) {
            throw new UserException('Invalid amount!');
        }

        if ($index === 0 && $usageParameters['age'] >= 18 && $usageParameters['amount'] == 0) {
            throw new UserException('Must be at least one adult in the first room!');
        }
    }

    /**
     * @param $roomParameters
     * @param $index
     * @throws UserException
     */
    protected function validateRoomParameters($roomParameters, $index)
    {
        if (empty($roomParameters['usage'])) {
            throw new UserException("One of the usages is empty! Please fill your room request!");
        }

        $firstRoomHasAtLeastOneAdult = false;
        foreach ($roomParameters['usage'] as &$usageParameters) {
            $this->validateUsageParameters($usageParameters, $index);
            if ($index === 0 && $usageParameters['age'] >= 18 && $usageParameters['amount'] > 0) {
                $firstRoomHasAtLeastOneAdult = true;
            }
        }

        if ($index == 0 && !$firstRoomHasAtLeastOneAdult) {
            throw new UserException('First room has no adults');
        }
    }

    protected function filterUsageParameters(&$usageParameters)
    {
        $usageParameters['age'] = (int)$usageParameters['age'];
        $usageParameters['amount'] = (int)$usageParameters['amount'];
    }


    /**
     * @param bool $showInactive
     * @return $this
     */
    public function setShowInactive($showInactive = false)
    {
        $this->showInactive = (bool)$showInactive;
        return $this;
    }

    protected function buildParametersJSON()
    {
        $usageJson = isset($this->parameters['usages']) ? $this->parameters['usages'] : [];

        $parameters = [
            'request' => $usageJson,
        ];
        if ($this->hasValidInterval) {
            $parameters = [
                'request' => $usageJson,
                'interval' => $this->parameters['interval'],
                'booking_date' => $this->parameters['booking_date'],
                'wedding_date' => !empty($this->parameters['wedding_date']) ? $this->parameters['wedding_date'] : null,
                'cart_summary' => $this->parameters['cart_summary'],
                'returning_client' => $this->parameters['returning_client']
            ];
            if ($this->displayMargins) {
                $parameters['display_margin'] = true;
            }
        }

        if ($this->showInactive) {
            $parameters['show_inactive'] = true;
        }

        return json_encode($parameters);
    }
}
