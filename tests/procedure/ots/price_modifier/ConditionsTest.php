<?php
namespace Tests\Procedure\Ots\PriceModifier;

use Tests\Procedure\ProcedureTestCase;

class ConditionsTest extends ProcedureTestCase
{

    static public $setupMode = self::SETUPMODE_NEVER;
    static public $imports = 'from ots.price_modifier.conditions import *' . PHP_EOL . 'from datetime import datetime' . PHP_EOL;

    /* properties, search
     * def cond_booking_dates_should_be_contained(valid_from, valid_to, from_time, to_time):
      return not (valid_from > from_time or valid_to < to_time)
     */

    /**
     * @test
     */
    public function booking_dates_should_be_contained_works_correctly()
    {
        //in
        $result = $this->runPythonScript(self::$imports . "print(cond_booking_dates_should_be_contained('2027-06-01', '2027-07-01', '2027-06-29', '2027-06-30'))");
        $this->assertEquals('True', $result);

        //out at beginning
        $result2 = $this->runPythonScript(self::$imports . "print(cond_booking_dates_should_be_contained('2027-06-01', '2027-07-01', '2027-05-29', '2027-06-30'))");
        $this->assertEquals('False', $result2);

        //out at end
        $result3 = $this->runPythonScript(self::$imports . "print(cond_booking_dates_should_be_contained('2027-06-01', '2027-07-01', '2027-06-29', '2027-07-03'))");
        $this->assertEquals('False', $result3);

        //overlaps
        $result4 = $this->runPythonScript(self::$imports . "print(cond_booking_dates_should_be_contained('2027-06-01', '2027-07-01', '2027-05-29', '2027-07-03'))");
        $this->assertEquals('False', $result4);
    }

    /**
     * @test
     */
    public function cond_restricted_to_device_ids_works_correctly()
    {
        //jsonerror
        $result = $this->runPythonScript(self::$imports . "print(cond_restricted_to_device_ids(1, 'Alma'))");
        $this->assertRegExp('/^ValueError: No JSON object could be decoded/m', $result);

        //in
        $result2 = $this->runPythonScript(self::$imports . "print(cond_restricted_to_device_ids(1, '1,2,3'))");
        $this->assertEquals('True', $result2);

        //not in
        $result3 = $this->runPythonScript(self::$imports . "print(cond_restricted_to_device_ids(999, '1,2,3'))");
        $this->assertEquals('False', $result3);
    }

    /**
     * @test
     */
    public function cond_minimum_nights_works_correctly()
    {
        //nights bigger than minimum
        $result = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-26', '%Y-%m-%d'), datetime.strptime('2027-06-30', '%Y-%m-%d'), '3'))");
        $this->assertEquals('True', $result);

        //nights equals minimum
        $result2 = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-27', '%Y-%m-%d'), datetime.strptime('2027-06-30', '%Y-%m-%d'), '3'))");
        $this->assertEquals('True', $result2);

        //nights smaller than minimum
        $result3 = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-28', '%Y-%m-%d'), datetime.strptime('2027-06-30', '%Y-%m-%d'), '3'))");
        $this->assertEquals('False', $result3);

        //nights is zero
        $result3 = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-28', '%Y-%m-%d'), datetime.strptime('2027-06-30', '%Y-%m-%d'), '0'))");
        $this->assertEquals('True', $result3);

        //nights is empty
        $result3 = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-28', '%Y-%m-%d'), datetime.strptime('2027-06-30', '%Y-%m-%d'), ''))");
        $this->assertEquals('False', $result3);

        //nights is a bad data
        $result3 = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-28', '%Y-%m-%d'), datetime.strptime('2027-06-30', '%Y-%m-%d'), '13%'))");
        $this->assertEquals('False', $result3);

        //one day periods
        $result3 = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-28', '%Y-%m-%d'), datetime.strptime('2027-07-02', '%Y-%m-%d'), '3'))");
        $this->assertEquals('True', $result3);

        $result3 = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-30', '%Y-%m-%d'), datetime.strptime('2027-07-02', '%Y-%m-%d'), '3'))");
        $this->assertEquals('False', $result3);

        $result3 = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-07-02', '%Y-%m-%d'), datetime.strptime('2027-07-12', '%Y-%m-%d'), '3'))");
        $this->assertEquals('False', $result3);

        $result3 = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), '1'))");
        $this->assertEquals('True', $result3);

        //year leap
        $result3 = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-12-31', '%Y-%m-%d'), datetime.strptime('2027-12-31', '%Y-%m-%d'), datetime.strptime('2027-12-28', '%Y-%m-%d'), datetime.strptime('2028-01-02', '%Y-%m-%d'), '3'))");
        $this->assertEquals('True', $result3);

        $result3 = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-12-31', '%Y-%m-%d'), datetime.strptime('2027-12-31', '%Y-%m-%d'), datetime.strptime('2027-12-28', '%Y-%m-%d'), datetime.strptime('2028-01-02', '%Y-%m-%d'), '7'))");
        $this->assertEquals('False', $result3);

        //holiday range at the end of discount range
        $result = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-03', '%Y-%m-%d'), datetime.strptime('2027-06-03', '%Y-%m-%d'), datetime.strptime('2027-06-05', '%Y-%m-%d'), '1'))");
        $this->assertEquals('True', $result);

        $result = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-02', '%Y-%m-%d'), datetime.strptime('2027-06-03', '%Y-%m-%d'), datetime.strptime('2027-06-05', '%Y-%m-%d'), '1'))");
        $this->assertEquals('False', $result);

    }

    /**
     * @test
     */
    public function cond_maximum_nights_works_correctly()
    {
        //nights bigger than maximum
        $result = $this->runPythonScript(self::$imports . "print(cond_maximum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-26', '%Y-%m-%d'), datetime.strptime('2027-06-30', '%Y-%m-%d'), '3'))");
        $this->assertEquals('False', $result);

        //nights equals maximum
        $result2 = $this->runPythonScript(self::$imports . "print(cond_maximum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-27', '%Y-%m-%d'), datetime.strptime('2027-06-30', '%Y-%m-%d'), '3'))");
        $this->assertEquals('True', $result2);

        //nights smaller than maximum
        $result3 = $this->runPythonScript(self::$imports . "print(cond_maximum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-28', '%Y-%m-%d'), datetime.strptime('2027-06-30', '%Y-%m-%d'), '3'))");
        $this->assertEquals('True', $result3);

        //nights is zero
        $result3 = $this->runPythonScript(self::$imports . "print(cond_maximum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-28', '%Y-%m-%d'), datetime.strptime('2027-06-30', '%Y-%m-%d'), '0'))");
        $this->assertEquals('False', $result3);

        //nights is empty
        $result3 = $this->runPythonScript(self::$imports . "print(cond_maximum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-28', '%Y-%m-%d'), datetime.strptime('2027-06-30', '%Y-%m-%d'), ''))");
        $this->assertEquals('False', $result3);

        //nights is a bad data
        $result3 = $this->runPythonScript(self::$imports . "print(cond_maximum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-28', '%Y-%m-%d'), datetime.strptime('2027-06-30', '%Y-%m-%d'), '13%'))");
        $this->assertEquals('False', $result3);

        //one day periods
        $result3 = $this->runPythonScript(self::$imports . "print(cond_maximum_nights(datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-29', '%Y-%m-%d'), datetime.strptime('2027-07-02', '%Y-%m-%d'), '3'))");
        $this->assertEquals('True', $result3);

        $result3 = $this->runPythonScript(self::$imports . "print(cond_maximum_nights(datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-06-28', '%Y-%m-%d'), datetime.strptime('2027-07-02', '%Y-%m-%d'), '3'))");
        $this->assertEquals('False', $result3);

        $result3 = $this->runPythonScript(self::$imports . "print(cond_maximum_nights(datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-07-02', '%Y-%m-%d'), datetime.strptime('2027-07-03', '%Y-%m-%d'), '3'))");
        $this->assertEquals('False', $result3);

        $result3 = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), datetime.strptime('2027-07-01', '%Y-%m-%d'), '1'))");
        $this->assertEquals('True', $result3);

        //year leap
        $result3 = $this->runPythonScript(self::$imports . "print(cond_maximum_nights(datetime.strptime('2027-12-31', '%Y-%m-%d'), datetime.strptime('2027-12-31', '%Y-%m-%d'), datetime.strptime('2027-12-28', '%Y-%m-%d'), datetime.strptime('2028-01-02', '%Y-%m-%d'), '7'))");
        $this->assertEquals('True', $result3);

        $result3 = $this->runPythonScript(self::$imports . "print(cond_maximum_nights(datetime.strptime('2027-12-31', '%Y-%m-%d'), datetime.strptime('2027-12-31', '%Y-%m-%d'), datetime.strptime('2027-12-28', '%Y-%m-%d'), datetime.strptime('2028-01-02', '%Y-%m-%d'), '3'))");
        $this->assertEquals('False', $result3);

        //holiday range at the end of discount range
        $result = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-03', '%Y-%m-%d'), datetime.strptime('2027-06-03', '%Y-%m-%d'), datetime.strptime('2027-06-05', '%Y-%m-%d'), '2'))");
        $this->assertEquals('True', $result);

        $result = $this->runPythonScript(self::$imports . "print(cond_minimum_nights(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-02', '%Y-%m-%d'), datetime.strptime('2027-06-03', '%Y-%m-%d'), datetime.strptime('2027-06-05', '%Y-%m-%d'), '2'))");
        $this->assertEquals('False', $result);
    }

    /**
     * @test
     */
    public function cond_booking_prior_minimum_days_works_correctly()
    {
        //nights bigger than minimum

        $result = $this->runPythonScript(self::$imports . "print(cond_booking_prior_minimum_days(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-05-01', '%Y-%m-%d'), 30))");
        $this->assertEquals('True', $result);

        //nights equals minimum
        $result2 = $this->runPythonScript(self::$imports . "print(cond_booking_prior_minimum_days(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-05-02', '%Y-%m-%d'), 30))");
        $this->assertEquals('True', $result2);

        //nights smaller than minimum
        $result3 = $this->runPythonScript(self::$imports . "print(cond_booking_prior_minimum_days(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-05-03', '%Y-%m-%d'), 30))");
        $this->assertEquals('False', $result3);
    }

    /**
     * @test
     */
    public function cond_booking_prior_maximum_days_works_correctly()
    {
        //nights bigger than maximum
        $result = $this->runPythonScript(self::$imports . "print(cond_booking_prior_maximum_days(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-05-01', '%Y-%m-%d'), 30))");
        $this->assertEquals('False', $result);

        //nights equals maximum
        $result2 = $this->runPythonScript(self::$imports . "print(cond_booking_prior_maximum_days(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-05-02', '%Y-%m-%d'), 30))");
        $this->assertEquals('True', $result2);

        //nights smaller than maximum
        $result3 = $this->runPythonScript(self::$imports . "print(cond_booking_prior_maximum_days(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-05-03', '%Y-%m-%d'), 30))");
        $this->assertEquals('True', $result3);
    }

    /**
     * @test
     */
    public function cond_wedding_in_less_than_days_works_correctly()
    {
        //out of range
        $result = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_days(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-02-01', '%Y-%m-%d'), 90))");
        $this->assertEquals('False', $result);

        //on range border - out
        $result2 = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_days(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-03-02', '%Y-%m-%d'), 90))");//91
        $this->assertEquals('False', $result2);

        //on range border - in
        $result2 = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_days(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-03-03', '%Y-%m-%d'), 90))");//91
        $this->assertEquals('True', $result2);

        //in range
        $result3 = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_days(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-05-01', '%Y-%m-%d'), 90))");
        $this->assertEquals('True', $result3);

        //other year
        $result4 = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_days(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2026-05-01', '%Y-%m-%d'), 90))");
        $this->assertEquals('False', $result4);

        //after holiday
        $result4 = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_days(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-05', '%Y-%m-%d'), 90))");
        $this->assertEquals('False', $result4);
    }

    /**
     * @test
     */
    public function cond_wedding_in_less_than_months_works_correctly()
    {
        //out of range
        $result = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_months(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-02-01', '%Y-%m-%d'), 3))");
        $this->assertEquals('False', $result);

        //on range border - border should match
        $result2 = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_months(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-03-01', '%Y-%m-%d'), 3))");
        $this->assertEquals('True', $result2);

        //in range
        $result3 = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_months(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-05-01', '%Y-%m-%d'), 3))");
        $this->assertEquals('True', $result3);

        //zero
        $result3 = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_months(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-01', '%Y-%m-%d'), 3))");
        $this->assertEquals('True', $result3);

        //other year
        $result4 = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_months(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2026-05-11', '%Y-%m-%d'), 3))");
        $this->assertEquals('False', $result4);

        //after holiday
        $result4 = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_months(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-05', '%Y-%m-%d'), 3))");
        $this->assertEquals('False', $result4);

        //after holiday
        $result4 = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_months(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-08-05', '%Y-%m-%d'), 3))");
        $this->assertEquals('False', $result4);

        //one year
        $result4 = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_months(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2026-06-01', '%Y-%m-%d'), 12))");
        $this->assertEquals('True', $result4);

        //bigger than one year
        $result4 = $this->runPythonScript(self::$imports . "print(cond_wedding_in_less_than_months(datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2026-05-31', '%Y-%m-%d'), 12))");
        $this->assertEquals('False', $result4);
    }

    /**
     * @test
     */
    public function cond_anniversary_in_range_days_works_correctly()
    {

        function condAnniversaryInRangeDays(
            $fromDate,
            $toDate,
            $weddingDate,
            $anniversaryYearPeriod,
            $anniversaryInRangeDays,
            $applicableOnce = false,
            $startYear = 0
        ) {
            $applicableOnceStr = $applicableOnce ? 'True' : 'False';
            return ProcedureTestCase::runPythonScript(ConditionsTest::$imports . "print(cond_anniversary_in_range_days(
                datetime.strptime('{$fromDate}', '%Y-%m-%d'),
                datetime.strptime('{$toDate}', '%Y-%m-%d'),
                datetime.strptime('{$weddingDate}', '%Y-%m-%d'),
                {$anniversaryYearPeriod},
                {$anniversaryInRangeDays},
                {$applicableOnceStr},
                {$startYear}
            ))");
        }
        // APPLIED IN EVERY YEAR
        // bigger
        $this->assertEquals('False', condAnniversaryInRangeDays('2027-06-01', '2027-06-02', '2026-01-01', 1, 30));

        // smaller
        $this->assertEquals('True', condAnniversaryInRangeDays('2027-06-01', '2027-06-02', '2026-05-02', 1, 31));

        // smaller with anniversary_year_start_from = 1
        $this->assertEquals('True', condAnniversaryInRangeDays('2027-06-01', '2027-06-02', '2026-05-02', 1, 31, false, 1));

        // smaller with anniversary_year_start_from = 2
        $this->assertEquals('False', condAnniversaryInRangeDays('2027-06-01', '2027-06-02', '2026-05-01', 1, 31, false, 2));

        // APPLIED IN 5x YEAR
        // bigger & year matches
        $this->assertEquals('False', condAnniversaryInRangeDays('2027-06-01', '2027-06-02', '2012-01-01', 5, 30));

        // smaller & year matches
        $this->assertEquals('True', condAnniversaryInRangeDays('2027-06-01', '2027-06-02', '2012-05-02', 5, 31));

        // bigger & year not matches
        $this->assertEquals('False', condAnniversaryInRangeDays('2027-06-01', '2027-06-02', '2011-01-01', 5, 30));

        // smaller & year not matches
        $this->assertEquals('False', condAnniversaryInRangeDays('2027-06-01', '2027-06-02', '2012-05-01', 5, 30));

        // applicable only once & first matches
        $this->assertEquals('True', condAnniversaryInRangeDays('2027-06-01', '2027-06-02', '2022-05-02', 5, 31, true));

        // applicable only once & 2nd anniversary matches ==> not applicable
        $this->assertEquals('False', condAnniversaryInRangeDays('2027-06-01', '2027-06-02', '2012-05-02', 5, 30, true));

        // limit is exact
        $this->assertEquals('True', condAnniversaryInRangeDays('2027-07-15', '2027-07-16', '2026-07-14', 1, 1));

        // ANNIVERSARY DURING VACATION
        $this->assertEquals('False', condAnniversaryInRangeDays('2027-07-05', '2027-07-10', '2026-07-06', 1, 0));

        // ANNIVERSARY AFTER VACATION
        // it can be after vacation
        $this->assertEquals('True', condAnniversaryInRangeDays('2027-07-05', '2027-07-10', '2026-07-15', 1, 10));

        // it counts from checkout date
        $this->assertEquals('True', condAnniversaryInRangeDays('2027-07-05', '2027-07-10', '2026-07-20', 1, 10));

        // limit is exact
        $this->assertEquals('False', condAnniversaryInRangeDays('2027-07-05', '2027-07-10', '2026-07-21', 1, 10));
    }

    /**
     * @test
     */
    public function cond_anniversary_in_range_months_works_correctly()
    {

        function condAnniversaryInRangeMonths(
            $fromDate,
            $toDate,
            $weddingDate,
            $anniversaryYearPeriod,
            $anniversaryInRangeMonths,
            $applicableOnce = false,
            $startYear = 0
        ) {
            $applicableOnceStr = $applicableOnce ? 'True' : 'False';
            return ProcedureTestCase::runPythonScript(ConditionsTest::$imports . "print(cond_anniversary_in_range_months(
                datetime.strptime('{$fromDate}', '%Y-%m-%d'),
                datetime.strptime('{$toDate}', '%Y-%m-%d'),
                datetime.strptime('{$weddingDate}', '%Y-%m-%d'),
                {$anniversaryYearPeriod},
                {$anniversaryInRangeMonths},
                {$applicableOnceStr},
                {$startYear}
            ))");
        }
        // APPLIED IN EVERY YEAR
        // out of range
        $this->assertEquals('False', condAnniversaryInRangeMonths('2027-06-01', '2027-06-02', '2026-01-01', 1, 1));

        // in range
        $this->assertEquals('True', condAnniversaryInRangeMonths('2027-06-01', '2027-06-02', '2026-05-02', 1, 1));

        // in range with anniversary_year_start_from = 1
        $this->assertEquals('True', condAnniversaryInRangeMonths('2027-06-01', '2027-06-02', '2026-05-02', 1, 1, false, 1));

        // in range with anniversary_year_start_from = 2
        $this->assertEquals('False', condAnniversaryInRangeMonths('2027-06-01', '2027-06-02', '2026-05-01', 1, 1, false, 2));

        // APPLIED IN 5x YEAR
        // out of range & year matches
        $this->assertEquals('False', condAnniversaryInRangeMonths('2027-06-01', '2027-06-02', '2012-01-01', 5, 1));

        // in range & year matches
        $this->assertEquals('True', condAnniversaryInRangeMonths('2027-06-01', '2027-06-02', '2012-05-02', 5, 1));

        // out of range & year not matches
        $this->assertEquals('False', condAnniversaryInRangeMonths('2027-06-01', '2027-06-02', '2011-01-01', 5, 1));

        // in range & year not matches
        $this->assertEquals('False', condAnniversaryInRangeMonths('2027-06-01', '2027-06-02', '2011-05-04', 5, 2));

        // applicable only once & first matches
        $this->assertEquals('True', condAnniversaryInRangeMonths('2027-06-01', '2027-06-02', '2022-05-02', 5, 1, true));

        // applicable only once & 2nd anniversary matches ==> not applicable
        $this->assertEquals('False', condAnniversaryInRangeMonths('2027-06-01', '2027-06-02', '2012-05-02', 5, 1, true));

        // limit is exact
        $this->assertEquals('True', condAnniversaryInRangeMonths('2027-07-15', '2027-07-16', '2026-07-14', 1, 1));


        // ANNIVERSARY AFTER VACATION
        // it can be after vacation
        $this->assertEquals('True', condAnniversaryInRangeMonths('2027-07-05', '2027-07-10', '2026-07-15', 1, 1));

        // it counts from checkout date
        $this->assertEquals('True', condAnniversaryInRangeMonths('2027-07-05', '2027-07-10', '2026-07-20', 1, 1));

        // limit is exact
        $this->assertEquals('False', condAnniversaryInRangeMonths('2027-07-05', '2027-07-10', '2026-05-05', 1, 1));
        $this->assertEquals('False', condAnniversaryInRangeMonths('2027-07-05', '2027-07-10', '2026-09-10', 1, 1));
    }

    /**
     * @test
     */
    public function cond_room_booked_in_range_of_ages()
    {
        $request = ['room' => [
                ['usage' => [
                        ['age' => 5, 'amount' => 2],
                        ['age' => 7, 'amount' => 1],
                        ['age' => 18, 'amount' => 1]
                    ]],
        ]];
        $requestJson = json_encode($request);
        $usageJson = json_encode($request['room'][0]['usage']);
        $result = $this->runPythonScript(self::$imports . "print(cond_room_booked_in_range_of_ages(0, 18, 0, 10, {$usageJson}))");
        $this->assertEquals('True', $result);

        $result = $this->runPythonScript(self::$imports . "print(cond_room_booked_in_range_of_ages(6, 18, 0, 10, {$usageJson}))"); // with min age
        $this->assertEquals('False', $result);

        $result = $this->runPythonScript(self::$imports . "print(cond_room_booked_in_range_of_ages(0, 18, 5, 10, {$usageJson}))");
        $this->assertEquals('False', $result);

        $request = ['room' => [
                ['usage' => [
                        ['age' => 5, 'amount' => 2],
                        ['age' => 7, 'amount' => 1],
                        ['age' => 19, 'amount' => 1]
                    ]],
        ]];
        $requestJson = json_encode($request);
        $usageJson = json_encode($request['room'][0]['usage']);
        $result = $this->runPythonScript(self::$imports . "print(cond_room_booked_in_range_of_ages(0, 18, 0, 10, {$usageJson}))");
        $this->assertEquals('False', $result);

        $request = ['room' => [
                ['usage' => [
                        ['age' => 5, 'amount' => 2],
                        ['age' => 7, 'amount' => 1],
                        ['age' => 18, 'amount' => 10]
                    ]],
        ]];
        $requestJson = json_encode($request);
        $usageJson = json_encode($request['room'][0]['usage']);
        $result = $this->runPythonScript(self::$imports . "print(cond_room_booked_in_range_of_ages(0, 18, 0, 10, {$usageJson}))");
        $this->assertEquals('False', $result);
    }

    /**
     * @test
     */
    function cond_nth_room()
    {
        $result = $this->runPythonScript(self::$imports . "print cond_nth_room('asdf', [0,1,3,4])");
        $this->assertEquals('[]', $result);

        $result = $this->runPythonScript(self::$imports . "print cond_nth_room(0, [0,1,3,4])");
        $this->assertEquals('[]', $result);

        $result = $this->runPythonScript(self::$imports . "print cond_nth_room(99, [0,1,3,4])");
        $this->assertEquals('[]', $result);

        $result = $this->runPythonScript(self::$imports . "print cond_nth_room(2, [0,1,3,4])");
        $this->assertEquals('[1]', $result);
    }

    /**
     * @test
     */
    function cond_child_room()
    {
        $request = '[{"usage": [{"age": 21, "amount": 2}]}]';
        $result = $this->runPythonScript(self::$imports . "print cond_child_room(0, 18, 0, 3, 2, {$request})");
        $this->assertEquals('[]', $result);

        $request = '[{"usage": [{"age": 21, "amount": 2}]}, {"usage": [{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}]';
        $result = $this->runPythonScript(self::$imports . "print cond_child_room(0, 18, 0, 3, 3, {$request})");
        $this->assertEquals('[]', $result);

        $request = '[{"usage": [{"age": 21, "amount": 2}]},{"usage": [{"age": 21, "amount": 2}]},{"usage": [{"age": 21, "amount": 2}]}, {"usage": [{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}]';
        $result = $this->runPythonScript(self::$imports . "print cond_child_room(0, 18, 0, 3, 4, {$request})");
        $this->assertEquals('[]', $result);

        $request = '[{"usage": [{"age": 21, "amount": 2}]},{"usage": [{"age": 21, "amount": 2}]},{"usage": [{"age": 21, "amount": 2}]}, {"usage": [{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}]';
        $result = $this->runPythonScript(self::$imports . "print cond_child_room(0, 18, 0, 3, 1, {$request})");
        $this->assertEquals('[3]', $result);
        $result = $this->runPythonScript(self::$imports . "print cond_child_room(5, 18, 0, 3, 1, {$request})");
        $this->assertEquals('[]', $result);
        $result = $this->runPythonScript(self::$imports . "print cond_child_room(0, 18, 3, 3, 1, {$request})");
        $this->assertEquals('[]', $result);

        $request = '[{"usage": [{"age": 21, "amount": 2}]}, {"usage": [{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}]';
        $result = $this->runPythonScript(self::$imports . "print cond_child_room(0, 18, 0, 3, 1, {$request})");
        $this->assertEquals('[1]', $result);
        $result = $this->runPythonScript(self::$imports . "print cond_child_room(5, 18, 0, 3, 1, {$request})");
        $this->assertEquals('[]', $result);
        $result = $this->runPythonScript(self::$imports . "print cond_child_room(0, 18, 3, 3, 1, {$request})");
        $this->assertEquals('[]', $result);

        $request = '[{"usage": [{"age": 21, "amount": 2}]}, {"usage": [{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}, {"usage": [{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}, {"usage": [{"age": 21, "amount": 2}]}]';
        $result = $this->runPythonScript(self::$imports . "print cond_child_room(0, 18, 0, 3, 2, {$request})");
        $this->assertEquals('[2]', str_replace(' ', '', $result));
        $result = $this->runPythonScript(self::$imports . "print cond_child_room(5, 18, 0, 3, 2, {$request})");
        $this->assertEquals('[]', str_replace(' ', '', $result));
        $result = $this->runPythonScript(self::$imports . "print cond_child_room(0, 18, 3, 3, 2, {$request})");
        $this->assertEquals('[]', str_replace(' ', '', $result));
    }

    /**
     * @test
     */
    public function cond_restricted_to_meal_plan_ids()
    {
        $result = $this->runPythonScript(self::$imports . "print(cond_restricted_to_meal_plan_ids(1, 'Alma'))");
        $this->assertRegExp('/^ValueError: No JSON object could be decoded/m', $result);

        $result2 = $this->runPythonScript(self::$imports . "print(cond_restricted_to_meal_plan_ids(1, '1,2,3'))");
        $this->assertEquals('True', $result2);

        $result3 = $this->runPythonScript(self::$imports . "print(cond_restricted_to_meal_plan_ids(999, '1,2,3'))");
        $this->assertEquals('False', $result3);
    }

    /**
     * @test
     */
    public function cond_cart_participating_organization_ids()
    {
        $cartSummary = '{"elements":[
            {
                "discountable_id":1,
                "device_id":2,
                "device_name": "Deluxe room",
                "meal_plan":"b/b",
                "interval":{"date_from":"2017-03-09","date_to":"2017-03-14"},
                "amount":1,
                "order_itemable_index":0,
                "usage_request":[{"age":21,"amount":2}]
            },
            {
                "discountable_id":10,
                "device_id":14,
                "device_name": "Double room",
                "meal_plan":"b/b",
                "interval":{"date_from":"2017-04-11","date_to":"2017-04-15"},
                "amount":1,
                "order_itemable_index":0,
                "usage_request":[{"age":21,"amount":2}]
            }
        ]}';

        $result1 = $this->runPythonScript(self::$imports . "print(cond_cart_participating_organization_ids({$cartSummary}, '1,10', '9'))");
        $this->assertEquals('True', $result1);

        $result2 = $this->runPythonScript(self::$imports . "print(cond_cart_participating_organization_ids({$cartSummary}, '1,10', '10'))");
        $this->assertEquals('False', $result2);

        $result3 = $this->runPythonScript(self::$imports . "print(cond_cart_participating_organization_ids({$cartSummary}, '1', '9'))");
        $this->assertEquals('False', $result3);

        $result4 = $this->runPythonScript(self::$imports . "print(cond_cart_participating_organization_ids({$cartSummary}, '1,10', '0'))");
        $this->assertEquals('True', $result4);

        $result5 = $this->runPythonScript(self::$imports . "print(cond_cart_participating_organization_ids({$cartSummary}, '', '0'))");
        $this->assertEquals('False', $result5);
    }

    /**
     * @test
     */
    public function cond_anniversary_in_same_month_works_correctly()
    {

        function condAnniversaryInSameMonth(
            $fromDate,
            $toDate,
            $weddingDate,
            $anniversaryYearPeriod,
            $applicableOnce = false,
            $startYear = 0
        ) {
            $applicableOnceStr = $applicableOnce ? 'True' : 'False';
            return ProcedureTestCase::runPythonScript(ConditionsTest::$imports . "print(cond_anniversary_in_same_month(
                datetime.strptime('{$fromDate}', '%Y-%m-%d'),
                datetime.strptime('{$toDate}', '%Y-%m-%d'),
                datetime.strptime('{$weddingDate}', '%Y-%m-%d'),
                {$anniversaryYearPeriod},
                {$applicableOnceStr},
                {$startYear}
            ))");
        }

        // APPLIED IN EVERY YEAR
        // other
        $this->assertEquals('False', condAnniversaryInSameMonth('2027-06-01', '2027-06-02', '2026-07-01', 1));

        // same
        $this->assertEquals('True', condAnniversaryInSameMonth('2027-06-01', '2027-06-02', '2026-06-05', 1));

        // same with anniversary_year_start_from = 1
        $this->assertEquals('True', condAnniversaryInSameMonth('2027-06-01', '2027-06-02', '2026-06-05', 1, false, 1));

        // same with anniversary_year_start_from = 2
        $this->assertEquals('False', condAnniversaryInSameMonth('2027-06-01', '2027-06-02', '2026-06-05', 1, false, 2));

        // APPLIED IN 5x YEAR
        // other & year matches
        $this->assertEquals('False', condAnniversaryInSameMonth('2027-06-01', '2027-06-02', '2012-01-01', 5));

        // same & year matches
        $this->assertEquals('True', condAnniversaryInSameMonth('2027-06-01', '2027-06-02', '2012-06-02', 5));

        // other & year does not match
        $this->assertEquals('False', condAnniversaryInSameMonth('2027-06-01', '2027-06-02', '2011-01-01', 5));

        // same & year does not match
        $this->assertEquals('False', condAnniversaryInSameMonth('2027-06-01', '2027-06-02', '2011-06-02', 5));

        // applicable only once & first matches
        $this->assertEquals('True', condAnniversaryInSameMonth('2027-06-01', '2027-06-02', '2022-06-02', 5, true));

        // applicable only once & 2nd anniversary matches ==> not applicable
        $this->assertEquals('False', condAnniversaryInSameMonth('2027-06-01', '2027-06-02', '2012-06-02', 5, true));

        // LONG VACATION
        // anniversary during 1st month
        $this->assertEquals('True', condAnniversaryInSameMonth('2027-06-15', '2027-08-15', '2026-06-05', 1));

        // anniversary during 2nd month
        $this->assertEquals('True', condAnniversaryInSameMonth('2027-06-15', '2027-08-15', '2026-07-15', 1));

        // anniversary during 3rd month
        $this->assertEquals('True', condAnniversaryInSameMonth('2027-06-15', '2027-08-15', '2026-08-25', 1));

        // LONG VACATION THROUGH NEW YEAR'S
        // anniversary during 1st month
        $this->assertEquals('True', condAnniversaryInSameMonth('2027-12-15', '2028-01-15', '2026-12-05', 1));

        // anniversary during 2nd month
        $this->assertEquals('True', condAnniversaryInSameMonth('2027-12-15', '2028-01-15', '2027-01-25', 1));
    }

    /**
     * @test
     */
    public function cond_anniversary_in_same_year_works_correctly()
    {

        function condAnniversaryInSameYear(
            $fromDate,
            $toDate,
            $weddingDate,
            $anniversaryYearPeriod,
            $applicableOnce = false,
            $startYear = 0
        ) {
            $applicableOnceStr = $applicableOnce ? 'True' : 'False';
            return ProcedureTestCase::runPythonScript(ConditionsTest::$imports . "print(cond_anniversary_in_same_year(
                datetime.strptime('{$fromDate}', '%Y-%m-%d'),
                datetime.strptime('{$toDate}', '%Y-%m-%d'),
                datetime.strptime('{$weddingDate}', '%Y-%m-%d'),
                {$anniversaryYearPeriod},
                {$applicableOnceStr},
                {$startYear}
            ))");
        }

        // APPLIED IN EVERY YEAR
        // anywhere
        $this->assertEquals('True', condAnniversaryInSameYear('2027-06-01', '2027-06-02', '2026-07-01', 1));
        $this->assertEquals('True', condAnniversaryInSameYear('2027-06-01', '2027-06-02', '2026-06-05', 1));

        // same with anniversary_year_start_from = 1
        $this->assertEquals('True', condAnniversaryInSameYear('2027-06-01', '2027-06-02', '2026-06-05', 1, false, 1));

        // same with anniversary_year_start_from = 2
        $this->assertEquals('False', condAnniversaryInSameYear('2027-06-01', '2027-06-02', '2026-06-05', 1, false, 2));

        // APPLIED IN 5x YEAR
        // year matches
        $this->assertEquals('True', condAnniversaryInSameYear('2027-06-01', '2027-06-02', '2012-06-02', 5));

        // year does not match
        $this->assertEquals('False', condAnniversaryInSameYear('2027-06-01', '2027-06-02', '2011-06-02', 5));

        // applicable only once & first matches
        $this->assertEquals('True', condAnniversaryInSameYear('2027-06-01', '2027-06-02', '2022-06-02', 5, true));

        // applicable only once & 2nd anniversary matches ==> not applicable
        $this->assertEquals('False', condAnniversaryInSameYear('2027-06-01', '2027-06-02', '2012-06-02', 5, true));

        // LONG VACATION THROUGH NEW YEAR'S
        // anniversary during 1st month
        $this->assertEquals('True', condAnniversaryInSameYear('2027-12-15', '2028-01-15', '2026-12-05', 1));

        // anniversary during 2nd month
        $this->assertEquals('True', condAnniversaryInSameYear('2027-12-15', '2028-01-15', '2027-01-25', 1));
    }

    /**
     * @test
     */
    public function cond_date_in_daterange_works_correctly()
    {

        //before
        $result = $this->runPythonScript(self::$imports . "print(cond_date_in_daterange(datetime.strptime('2012-08-01', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d'), datetime.strptime('2012-07-01', '%Y-%m-%d')))");
        $this->assertEquals('False', $result);

        //on check_in day
        $result = $this->runPythonScript(self::$imports . "print(cond_date_in_daterange(datetime.strptime('2012-08-01', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d'), datetime.strptime('2012-08-01', '%Y-%m-%d')))");
        $this->assertEquals('True', $result);

        //during holiday
        $result = $this->runPythonScript(self::$imports . "print(cond_date_in_daterange(datetime.strptime('2012-08-01', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d'), datetime.strptime('2012-08-05', '%Y-%m-%d')))");
        $this->assertEquals('True', $result);

        //on checkout day
        $result = $this->runPythonScript(self::$imports . "print(cond_date_in_daterange(datetime.strptime('2012-08-01', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d')))");
        $this->assertEquals('False', $result);

        //after
        $result = $this->runPythonScript(self::$imports . "print(cond_date_in_daterange(datetime.strptime('2012-08-01', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d'), datetime.strptime('2012-08-20', '%Y-%m-%d')))");
        $this->assertEquals('False', $result);
    }

    /**
     * @test
     */
    public function cond_wedding_or_anniversary_during_travel_works_correctly()
    {

        /**
         * WEDDING
         */
        //before
        $result = $this->runPythonScript(self::$imports . "print(cond_wedding_or_anniversary_during_travel(datetime.strptime('2012-08-01', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d'), datetime.strptime('2012-07-01', '%Y-%m-%d'),1))");
        $this->assertEquals('False', $result);

        //on check_in day
        $result = $this->runPythonScript(self::$imports . "print(cond_wedding_or_anniversary_during_travel(datetime.strptime('2012-08-01', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d'), datetime.strptime('2012-08-01', '%Y-%m-%d'),1))");
        $this->assertEquals('True', $result);

        //during holiday
        $result = $this->runPythonScript(self::$imports . "print(cond_wedding_or_anniversary_during_travel(datetime.strptime('2012-08-01', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d'), datetime.strptime('2012-08-05', '%Y-%m-%d'),1))");
        $this->assertEquals('True', $result);

        //on checkout day
        $result = $this->runPythonScript(self::$imports . "print(cond_wedding_or_anniversary_during_travel(datetime.strptime('2012-08-01', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d'),1))");
        $this->assertEquals('False', $result);

        //after
        $result = $this->runPythonScript(self::$imports . "print(cond_wedding_or_anniversary_during_travel(datetime.strptime('2012-08-01', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d'), datetime.strptime('2012-08-20', '%Y-%m-%d'),1))");
        $this->assertEquals('False', $result);

        /**
         * ANNIVERSARY
         */
        //APPLIED IN EVERY YEAR
        //not in range
        $result = $this->runPythonScript(self::$imports . "print cond_wedding_or_anniversary_during_travel("
            . "datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-10', '%Y-%m-%d'), datetime.strptime('2026-06-11', '%Y-%m-%d'), 1"
            . ")");
        $this->assertEquals('False', $result);

        //in range
        $result3 = $this->runPythonScript(self::$imports . "print cond_wedding_or_anniversary_during_travel("
            . "datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-10', '%Y-%m-%d'), datetime.strptime('2026-06-02', '%Y-%m-%d'), 1"
            . ")");
        $this->assertEquals('True', $result3);

        //with anniversary_year_start_from = 1
        $result3 = $this->runPythonScript(self::$imports . "print cond_wedding_or_anniversary_during_travel("
            . "datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-10', '%Y-%m-%d'), datetime.strptime('2026-06-02', '%Y-%m-%d'), 1, False, 1"
            . ")");
        $this->assertEquals('True', $result3);

        //with anniversary_year_start_from = 2
        $result3 = $this->runPythonScript(self::$imports . "print cond_wedding_or_anniversary_during_travel("
            . "datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-10', '%Y-%m-%d'), datetime.strptime('2026-06-02', '%Y-%m-%d'), 1, False, 2"
            . ")");
        $this->assertEquals('False', $result3);

        //APPLIED IN 5x YEAR
        //not in range & year matches
        $resultx = $this->runPythonScript(self::$imports . "print cond_wedding_or_anniversary_during_travel("
            . "datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-10', '%Y-%m-%d'), datetime.strptime('2012-06-11', '%Y-%m-%d'), 5"
            . ")");
        $this->assertEquals('False', $resultx);


        //in range & year matches
        $resultx3 = $this->runPythonScript(self::$imports . "print cond_wedding_or_anniversary_during_travel("
            . "datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-10', '%Y-%m-%d'), datetime.strptime('2012-06-02', '%Y-%m-%d'), 5"
            . ")");
        $this->assertEquals('True', $resultx3);

        //not in range & year not matches
        $resultxn = $this->runPythonScript(self::$imports . "print cond_wedding_or_anniversary_during_travel("
            . "datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-10', '%Y-%m-%d'), datetime.strptime('2011-06-11', '%Y-%m-%d'), 5"
            . ")");
        $this->assertEquals('False', $resultxn);

        //in range & year not matches
        $resultxn3 = $this->runPythonScript(self::$imports . "print cond_wedding_or_anniversary_during_travel("
            . "datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-10', '%Y-%m-%d'), datetime.strptime('2011-06-02', '%Y-%m-%d'), 5"
            . ")");
        $this->assertEquals('False', $resultxn3);


        //applicable only once & first matches
        $resultx4 = $this->runPythonScript(self::$imports . "print cond_wedding_or_anniversary_during_travel("
            . "datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-10', '%Y-%m-%d'), datetime.strptime('2022-06-02', '%Y-%m-%d'), 5, True"
            . ")");
        $this->assertEquals('True', $resultx4);

        //applicable only once & 2nd anniversary matches ==> not applicable
        $resultxn4 = $this->runPythonScript(self::$imports . "print cond_wedding_or_anniversary_during_travel("
            . "datetime.strptime('2027-06-01', '%Y-%m-%d'), datetime.strptime('2027-06-10', '%Y-%m-%d'), datetime.strptime('2012-06-02', '%Y-%m-%d'), 5, True"
            . ")");
        $this->assertEquals('False', $resultxn4);
    }

    /**
     * @test
     */
    function cond_room_sharing_usage_matching()
    {
        //adult_headcount_minimum, adult_headcount_maximum, child_headcount_minimum, child_headcount_maximum, child_age_minimum, child_age_maximum, usage
        $usage = '[{"age": 21, "amount": 2},{"age": 10, "amount": 1},{"age": 2, "amount": 1}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing_usage_matching(2, 0, 0, 2, 0, 11,{$usage})"
        );
        $this->assertEquals('True', $result);
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing_usage_matching(2, 0, 0, 2, 3, 11,{$usage})"
        );
        $this->assertEquals('False', $result);

        $usage = '[{"age": 21, "amount": 2},{"age": 10, "amount": 1},{"age": 2, "amount": 1}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing_usage_matching(2, 0, 0, 2, 0, 0,{$usage})"
        );
        $this->assertEquals('False', $result);

        $usage = '[{"age": 10, "amount": 1},{"age": 2, "amount": 1}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing_usage_matching(2, 0, 0, 2, 0, 11,{$usage})"
        );
        $this->assertEquals('False', $result);

        $usage = '[{"age": 21, "amount": 2}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing_usage_matching(2, 0, 0, 2, 0, 11,{$usage})"
        );
        $this->assertEquals('False', $result);

        $usage = '[{"age": 21, "amount": 2},{"age": 10, "amount": 1},{"age": 2, "amount": 1}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing_usage_matching(3, 0, 0, 2, 0, 11,{$usage})"
        );
        $this->assertEquals('False', $result);

        $usage = '[{"age": 21, "amount": 2},{"age": 10, "amount": 1},{"age": 2, "amount": 1}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing_usage_matching(2, 0, 0, 1, 0, 11,{$usage})"
        );
        $this->assertEquals('False', $result);

        $usage = '[{"age": 21, "amount": 2},{"age": 10, "amount": 1},{"age": 2, "amount": 1}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing_usage_matching(2, 0, 0, 2, 0, 22,{$usage})"
        );
        $this->assertEquals('False', $result);

        $usage = '[{"age": 21, "amount": 2},{"age": 10, "amount": 1},{"age": 2, "amount": 1}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing_usage_matching(2, 0, 0, 2, 0, 8,{$usage})"
        );
        $this->assertEquals('True', $result);

        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing_usage_matching(2, 0, 0, 2, 3, 8,{$usage})"
        );
        $this->assertEquals('False', $result);

        //child_headcount_minimum - match
        $usage = '[{"age": 21, "amount": 2},{"age": 10, "amount": 1},{"age": 2, "amount": 1}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing_usage_matching(2, 0, 2, 2, 0, 18,{$usage})"
        );
        $this->assertEquals('True', $result);

        //child_headcount_minimum - no match
        $usage = '[{"age": 21, "amount": 2},{"age": 10, "amount": 1},{"age": 2, "amount": 1}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing_usage_matching(2, 0, 3, 2, 0, 18,{$usage})"
        );
        $this->assertEquals('False', $result);

        //adult_headcount_maximum - match
        $usage = '[{"age": 21, "amount": 2},{"age": 10, "amount": 1},{"age": 2, "amount": 1}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing_usage_matching(2, 2, 2, 2, 0, 18,{$usage})"
        );
        $this->assertEquals('True', $result);

        //adult_headcount_maximum - no match
        $usage = '[{"age": 21, "amount": 2},{"age": 10, "amount": 1},{"age": 2, "amount": 1}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing_usage_matching(2, 1, 2, 2, 0, 18,{$usage})"
        );
        $this->assertEquals('False', $result);
    }

    /**
     * @test
     */
    function cond_room_sharing()
    {
        //adult_headcount_minimum,adult_headcount_maximum, child_headcount_minimum, child_headcount_maximum, child_age_minimum, child_age_maximum, request, indexes
        $request = '[{"usage": [{"age": 21, "amount": 2},{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing(2, 0, 0, 2, 0, 12, {$request}, [0])"
        );
        $this->assertEquals([0], \json_decode($result));
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing(2, 0, 0, 2, 5, 12, {$request}, [0])"
        );
        $this->assertEquals([], \json_decode($result));

        $request = '[{"usage": [{"age": 21, "amount": 2},{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}, {"usage": [{"age": 21, "amount": 2},{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing(2, 0, 0, 2, 0, 12, {$request}, [1])"
        );
        $this->assertEquals([1], \json_decode($result));
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing(2, 0, 0, 2, 5, 12, {$request}, [1])"
        );
        $this->assertEquals([], \json_decode($result));


        $request = '[{"usage": [{"age": 21, "amount": 2},{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}, {"usage": [{"age": 21, "amount": 2},{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing(2, 0, 0, 2, 0, 12, {$request}, [0,1])"
        );
        $this->assertEquals([0, 1], \json_decode($result));
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing(2, 0, 0, 2, 5, 12, {$request}, [0,1])"
        );
        $this->assertEquals([], \json_decode($result));

        $request = '[{"usage": [{"age": 21, "amount": 1},{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}, {"usage": [{"age": 21, "amount": 2},{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing(2, 0, 0, 2, 0, 12, {$request}, [0,1])"
        );
        $this->assertEquals([1], \json_decode($result));
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing(2, 0, 0, 2, 5, 12, {$request}, [0,1])"
        );
        $this->assertEquals([], \json_decode($result));

        $request = '[{"usage": [{"age": 21, "amount": 1},{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}, {"usage": [{"age": 21, "amount": 2},{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing(2, 0, 2, 2, 0, 12, {$request}, [0,1])"
        );
        $this->assertEquals([1], \json_decode($result));
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing(2, 0, 3, 2, 0, 12, {$request}, [0,1])"
        );
        $this->assertEquals([], \json_decode($result));

        //adult_headcount_maximum
        $request = '[{"usage": [{"age": 21, "amount": 1},{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}, {"usage": [{"age": 21, "amount": 2},{"age": 4, "amount": 1}, {"age": 3, "amount": 1}]}]';
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing(1, 1, 0, 0, 0, 12, {$request}, [0,1])"
        );
        $this->assertEquals([0], \json_decode($result));
        $result = $this->runPythonScript(self::$imports .
            "print cond_room_sharing(1, 3, 0, 0, 0, 12, {$request}, [0,1])"
        );
        $this->assertEquals([0, 1], \json_decode($result));
    }

    /**
     * @test
     */
    public function cond_booking_date_from()
    {

        //bad input type
        $result = $this->runPythonScript(self::$imports . "print cond_booking_date_from(datetime.strptime('2012-08-11', '%Y-%m-%d'), 'Errorous')");
        $this->assertEquals('False', $result);

        //wrongly formatted date
        $result = $this->runPythonScript(self::$imports . "print cond_booking_date_from(datetime.strptime('2012-08-11', '%Y-%m-%d'), '2020%10%10')");
        $this->assertEquals('False', $result);

        //invalid date
        $result = $this->runPythonScript(self::$imports . "print cond_booking_date_from(datetime.strptime('2012-08-11', '%Y-%m-%d'), '2012-13-39')");
        $this->assertEquals('False', $result);

        //positive match
        $result = $this->runPythonScript(self::$imports . "print cond_booking_date_from(datetime.strptime('2012-08-11', '%Y-%m-%d'), '2012-08-01')");
        $this->assertEquals('True', $result);

        //border
        $result = $this->runPythonScript(self::$imports . "print cond_booking_date_from(datetime.strptime('2012-08-11', '%Y-%m-%d'), '2012-08-11')");
        $this->assertEquals('True', $result);

        //negative match
        $result = $this->runPythonScript(self::$imports . "print cond_booking_date_from(datetime.strptime('2012-08-11', '%Y-%m-%d'), '2012-08-20')");
        $this->assertEquals('False', $result);
    }

    /**
     * @test
     */
    public function cond_booking_date_to()
    {

        //bad input type
        $result = $this->runPythonScript(self::$imports . "print cond_booking_date_to(datetime.strptime('2012-08-11', '%Y-%m-%d'), 'Errorous')");
        $this->assertEquals('False', $result);

        //wrongly formatted date
        $result = $this->runPythonScript(self::$imports . "print cond_booking_date_to(datetime.strptime('2012-08-11', '%Y-%m-%d'), '2020%10%10')");
        $this->assertEquals('False', $result);

        //invalid date
        $result = $this->runPythonScript(self::$imports . "print cond_booking_date_to(datetime.strptime('2012-08-11', '%Y-%m-%d'), '2012-13-39')");
        $this->assertEquals('False', $result);

        //positive match
        $result = $this->runPythonScript(self::$imports . "print cond_booking_date_to(datetime.strptime('2012-08-11', '%Y-%m-%d'), '2012-08-20')");
        $this->assertEquals('True', $result);

        //border
        $result = $this->runPythonScript(self::$imports . "print cond_booking_date_to(datetime.strptime('2012-08-11', '%Y-%m-%d'), '2012-08-11')");
        $this->assertEquals('True', $result);

        //negative match
        $result = $this->runPythonScript(self::$imports . "print cond_booking_date_to(datetime.strptime('2012-08-11', '%Y-%m-%d'), '2012-08-01')");
        $this->assertEquals('False', $result);
    }

    /**
     * @test
     */
    public function create_anniversary_range_works_correctly()
    {
        //positive match start = 1, period = 1
        $result = $this->runPythonAndDecodeJSON(self::$imports . "print create_anniversary_range(2015, datetime.strptime('2012-01-01', '%Y-%m-%d'), 1, 1, False)");
        $this->assertEquals([2013, 2014, 2015], $result);

        //positive match start = 2, period = 1
        $result = $this->runPythonAndDecodeJSON(self::$imports . "print create_anniversary_range(2015, datetime.strptime('2012-08-11', '%Y-%m-%d'), 2, 1, False)");
        $this->assertEquals([2014, 2015], $result);

        //positive match start = 1, period = 2
        $result = $this->runPythonAndDecodeJSON(self::$imports . "print create_anniversary_range(2015, datetime.strptime('2012-08-11', '%Y-%m-%d'), 1, 2, False)");
        $this->assertEquals([2013, 2015], $result);

        //positive match start = 2, period = 2
        $result = $this->runPythonAndDecodeJSON(self::$imports . "print create_anniversary_range(2015, datetime.strptime('2012-08-11', '%Y-%m-%d'), 2, 2, False)");
        $this->assertEquals([2014, 2016], $result);
    }

    /**
     * @test
     */
    public function create_anniversaries_works_correctly()
    {
        //positive match start = 1, period = 1
        $result = $this->runPythonAndDecodeJSON(self::$imports . "print create_anniversaries(datetime.strptime('2015-11-01', '%Y-%m-%d'), datetime.strptime('2012-01-01', '%Y-%m-%d'), 1, 1, False)", true, true);
        $this->assertEquals(['2013-01-01 00:00:00', '2014-01-01 00:00:00', '2015-01-01 00:00:00', '2016-01-01 00:00:00'], $result);

        //positive match start = 2, period = 1
        $result = $this->runPythonAndDecodeJSON(self::$imports . "print create_anniversaries(datetime.strptime('2015-11-01', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d'), 2, 1, False)", true, true);
        $this->assertEquals(['2014-08-11 00:00:00', '2015-08-11 00:00:00', '2016-08-11 00:00:00'], $result);

        //positive match start = 1, period = 2
        $result = $this->runPythonAndDecodeJSON(self::$imports . "print create_anniversaries(datetime.strptime('2015-11-01', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d'), 1, 2, False)", true, true);
        $this->assertEquals(['2013-08-11 00:00:00', '2015-08-11 00:00:00', '2017-08-11 00:00:00'], $result);

        //positive match start = 2, period = 2
        $result = $this->runPythonAndDecodeJSON(self::$imports . "print create_anniversaries(datetime.strptime('2015-11-01', '%Y-%m-%d'), datetime.strptime('2012-08-11', '%Y-%m-%d'), 2, 2, False)", true, true);
        $this->assertEquals(['2014-08-11 00:00:00', '2016-08-11 00:00:00'], $result);
    }

    /**
     * @test
     */
    public function cond_cart_family_room_combo()
    {
        $cartSummary = '{"elements":[
            {
                "organization_id":1,
                "device_id":2,
                "device_name": "Deluxe room",
                "meal_plan":"b/b",
                "interval":{"date_from":"2017-03-09","date_to":"2017-03-14"},
                "amount":1,
                "order_itemable_index":0,
                "usage_request":[{"age":21,"amount":2}]
            },
            {
                "organization_id":1,
                "device_id":2,
                "device_name": "Deluxe room",
                "meal_plan":"b/b",
                "interval":{"date_from":"2017-03-09","date_to":"2017-03-14"},
                "amount":1,
                "order_itemable_index":1,
                "usage_request":[{"age":2,"amount":1}]
            }
        ],
        "familyComboSelections": [
            { "parent_room":0, "child_room":1 }
        ]
        }';

        $request = '[{"usage": [{"age": 21, "amount": 2}]}, {"usage": [{"age": 2, "amount": 1}]}]';

        //positive match
        $result1 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_cart_family_room_combo(21, 2, 20, {$cartSummary}, {$request}))");
        $this->assertEquals([0, 1], $result1);

        //ages not match
        $result2 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_cart_family_room_combo(23, 2, 20, {$cartSummary}, {$request}))");
        $this->assertEquals([], $result2);
        $result3 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_cart_family_room_combo(21, 4, 20, {$cartSummary}, {$request}))");
        $this->assertEquals([], $result3);
        $result4 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_cart_family_room_combo(21, 1, 1, {$cartSummary}, {$request}))");
        $this->assertEquals([], $result4);

        $request1 = '[{"usage": [{"age": 21, "amount": 2}]}]';
        $result5 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_cart_family_room_combo(21, 1, 1, {$cartSummary}, {$request1}))");
        $this->assertEquals([], $result5);

        $summary = json_decode($cartSummary, true);
        unset($summary["familyComboSelections"][0]);
        $cartSummary2 = json_encode($summary);
        $result6 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_cart_family_room_combo(21, 1, 1, {$cartSummary2}, {$request}))");
        $this->assertEquals([], $result6);

        $summary2 = json_decode($cartSummary, true);
        $summary2["familyComboSelections"][0] = (object) ["parent_room" => 0, "child_room" => 3];
        $cartSummary3 = json_encode($summary2);
        $result7 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_cart_family_room_combo(21, 1, 1, {$cartSummary3}, {$request}))");
        $this->assertEquals([], $result7);
    }

    /**
     * @test
     */
    public function cond_suite_component_rooms()
    {
        $cartSummary = file_get_contents(__DIR__ . '/testdata/' . 'cond_suite_component_rooms.cart.json');
        $cartSummaryPythonized = str_replace([' null', 'true'], [' None', 'True'], $cartSummary);


        //positive match
        $suite_component_rooms_json = '{"Single Room":1, "Double Room":1}';
        $result1 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_suite_component_rooms('{$suite_component_rooms_json}', {$cartSummaryPythonized}, 1))");
        $this->assertEquals([0, 1, 2, 3], $result1);

        $suite_component_rooms_json = '{"Single Room":2, "Double Room":2}';
        $result2 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_suite_component_rooms('{$suite_component_rooms_json}', {$cartSummaryPythonized}, 1))");
        $this->assertEquals([0, 1, 2, 3], $result2);

        $suite_component_rooms_json = '{"Single Room":1}';
        $result3 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_suite_component_rooms('{$suite_component_rooms_json}', {$cartSummaryPythonized}, 1))");
        $this->assertEquals([2, 3], $result3);

        //partial positive match
        $suite_component_rooms_json = '{"Single Room":2, "Double Room":1}';
        $result4 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_suite_component_rooms('{$suite_component_rooms_json}', {$cartSummaryPythonized}, 1))");
        $this->assertEquals([0, 2, 3], $result4);

        //bad input data
        $badInputs = ['{','{}','[]', '{"Single Room": "asdf"}' , '{"Single Room": 0}' , '{"Single Room": -1}'];
        foreach ($badInputs as $suite_component_rooms_json){
            $resultBad = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_suite_component_rooms('{$suite_component_rooms_json}', {$cartSummaryPythonized}, 1))");
            $this->assertEquals([], $resultBad);
        }

        //negative match - not enough room in cart
        $suite_component_rooms_json = '{"Single Room":3, "Double Room":3}';
        $result4 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_suite_component_rooms('{$suite_component_rooms_json}', {$cartSummaryPythonized}, 1))");
        $this->assertEquals([], $result4);

    }

    /**
     * @test
     */
    public function cond_group_price_modifier()
    {
        $cartSummary = file_get_contents(__DIR__ . '/testdata/' . 'cond_group_discount.cart.json');
        $cartSummaryPythonized = str_replace([' null', 'true'], [' None', 'True'], $cartSummary);
        //group_headcount_minimum, group_headcount_maximum, cart_summary, price_modifiable_type, price_modifiable_id

        //positive match
        $result1 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_group_price_modifier('6', '8', {$cartSummaryPythonized}, 'App\\OrganizationGroup', 1))");
        $this->assertEquals([0, 1], $result1);

        $result2 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_group_price_modifier('6', 0, {$cartSummaryPythonized}, 'App\\OrganizationGroup', 1))");
        $this->assertEquals([0, 1], $result2);

        //partial positive match
        $result4 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_group_price_modifier('6', '6', {$cartSummaryPythonized}, 'App\\OrganizationGroup', 1))");
        $this->assertEquals([0], $result4);

        //bad input data
        $badInputParams = [
            "'eeee', '8', {$cartSummaryPythonized}, 'App\\OrganizationGroup', 1",
            "'6', 'e!', {$cartSummaryPythonized}, 'App\\OrganizationGroup', 1",
            "'6', '8', {$cartSummaryPythonized}, 'App\\OGroup', 1",
            "'6', '8', {$cartSummaryPythonized}, None, None",
            "'6', '8', '{', 'App\\OrganizationGroup', 1",
            "'8', '6', {$cartSummaryPythonized}, 'App\\OrganizationGroup', 1",
        ];
        foreach ($badInputParams as $badParams) {
            $resultBad = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_group_price_modifier({$badParams}))");
            $this->assertEquals([], $resultBad);
        }

        //negative match - headcount not in [min,max] range
        $result5 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_group_price_modifier('8', 0, {$cartSummaryPythonized}, 'App\\OrganizationGroup', 1))");
        $this->assertEquals([], $result5);

        $result5 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_group_price_modifier('2', '2', {$cartSummaryPythonized}, 'App\\OrganizationGroup', 1))");
        $this->assertEquals([], $result5);

        //partial positive match with subclass
        $result4 = $this->runPythonAndDecodeJSON(self::$imports . "print(cond_group_price_modifier('6', '6', {$cartSummaryPythonized}, 'App\\ShipGroup', 1))");
        $this->assertEquals([0], $result4);

    }

    /**
     * @test
     */
    public function cond_returning_client()
    {

        //positive match
        $result1 = $this->runPythonScript(self::$imports . "print(cond_returning_client(True))");
        $this->assertEquals('True', $result1);

        $result2 = $this->runPythonScript(self::$imports . "print(cond_returning_client(False))");
        $this->assertEquals('False', $result2);

    }
}
