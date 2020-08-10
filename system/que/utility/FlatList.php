<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/19/2018
 * Time: 4:59 PM
 */

namespace que\utility;

class FlatList
{
    /**
     * @var FlatList
     */
    private static FlatList $instance;

    /**
     * @var array
     */
    private static $database_config;

    /**
     * FlatList constructor.
     */
    protected function __construct()
    {
        self::$database_config = config('database');
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    private function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @return FlatList
     */
    public static function getInstance(): FlatList
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @return array
     */
    public function countries(): array
    {
        return $this->getCountries();
    }

    /**
     * @param $countryID
     * @return array
     */
    public function states($countryID): array
    {
        return $this->getStates($countryID);
    }

    /**
     * @return array
     */
    public function languages(): array
    {
        return $this->getLanguages();
    }

    /**
     * @param $stateID
     * @return array
     */
    public function areas($stateID): array
    {
        return $this->getAreas($stateID);
    }

    /**
     * @return array
     */
    public function ageRange(): array
    {
        return [
            0 => '--- Please Select ---',
            AGE_RANGE_ALL => 'All age range',
            AGE_RANGE_13_20 => MAX_AGE . ' - 20',
            AGE_RANGE_21_40 => '21 - 40',
            AGE_RANGE_41_60 => '41 - 60',
            AGE_RANGE_61_80 => '61 - 80',
            AGE_RANGE_81_100 => '81 - 100',
            AGE_RANGE_101_INFINITE => '101 - above'
        ];
    }

    /**
     * @return array
     */
    public function gender(): array
    {
        return [
            0 => '--- Please Select ---',
            GENDER_MALE => 'Male',
            GENDER_FEMALE => 'Female'
        ];
    }

    /**
     * @return array
     */
    public function maritalStatus(): array
    {
        return [
            0 => "--- Please Select ---",
            1 => "Single",
            2 => "Married",
            3 => "Divorce",
            4 => "Not Specified"
        ];
    }

    /**
     * @return array
     */
    public function religion(): array
    {
        return [
            0 => "--- Please Select ---",
            1 => "Christian",
            2 => "Islam",
            3 => "Traditional Worshiper",
            4 => "Others",
            5 => "Not Specified"
        ];
    }

    /**
     * @return array
     */
    public function bloodGroup(): array
    {
        return [
            0 => "--- Please Select ---",
            1 => "O +",
            2 => "O -",
            3 => "A +",
            4 => "A -",
            5 => "B +",
            6 => "B -",
            7 => "AB +",
            8 => "AB -",
            9 => "Not Specified"
        ];
    }

    /**
     * @return array
     */
    public function genotype(): array
    {
        return [
            0 => "--- Please Select ---",
            1 => "AA",
            2 => "AS",
            3 => "SS",
            4 => "AC",
            5 => "SC",
            6 => "CC",
            7 => "Not Specified"
        ];
    }

    /**
     * @return array
     */
    public function educationLevel(): array
    {
        return [
            0 => "--- Please Select ---",
            1 => "Tertiary",
            2 => "Secondary",
            3 => "Primary",
            4 => "Others"
        ];
    }

    /**
     * @return array
     */
    public function relationship(): array
    {
        return [
            0 => "--- Please Select ---",
            1 => "Father",
            2 => "Mother",
            3 => "Brother",
            4 => "Sister",
            5 => "Son",
            6 => "Daughter",
            7 => "Husband",
            8 => "Wife",
            9 => "Guardian",
            10 => "Grand Parents",
            11 => "Cousin",
            12 => "Nephew",
            13 => "Niece",
            14 => "Aunt",
            15 => "Uncle",
            16 => "Other"
        ];
    }

    /**
     * @return array
     */
    public function jobTypes(): array
    {
        return [
            0 => "--- Please Select ---",
            1 => "Full Time",
            2 => "Part Time",
            3 => "Internship",
            4 => "Contract",
            5 => "Freelancer",
            6 => "Others"
        ];
    }

    /**
     * @return array
     */
    public function experience(): array
    {
        return [
            0 => "--- Please Select ---",
            1 => "Less than a year",
            2 => "1 - 2 Years",
            3 => "3 - 4 Years",
            4 => "5 - 6 Years",
            5 => "7 - 8 Years",
            6 => "9 - 10 Years",
            7 => "Above 10 Years"
        ];
    }

    public function qualification(): array {
        return [
            [
                'title' => '--- Please Select ---',
                'subtitle' => 'None'
            ],
            [
                'title' => "0'Level",
                'subtitle' => "Undergraduate"
            ],
            [
                'title' => "Diploma",
                'subtitle' => "Undergraduate"
            ],
            [
                'title' => "Associate's degree",
                'subtitle' => "Undergraduate"
            ],
            [
                'title' => "Bachelor's degree",
                'subtitle' => "Undergraduate"
            ],
            [
                'title' => "Master's degree",
                'subtitle' => "Graduate"
            ],
            [
                'title' => "Doctoral degree",
                'subtitle' => "Graduate"
            ],
            [
                'title' => "Professional degree",
                'subtitle' => "Graduate"
            ]
        ];
    }

    /**
     * @return array
     */
    public function getDays(): array
    {
        return [
            0 => '--- Please Select ---',
            1 => "1st",
            2 => "2nd",
            3 => "3rd",
            4 => "4th",
            5 => "5th",
            6 => "6th",
            7 => "7th",
            8 => "8th",
            9 => "8th",
            10 => "10th",
            11 => "11th",
            12 => "12th",
            13 => "13th",
            14 => "14th",
            15 => "15th",
            16 => "16th",
            17 => "17th",
            18 => "18th",
            19 => "19th",
            20 => "20th",
            21 => "21st",
            22 => "22nd",
            23 => "23rd",
            24 => "24th",
            25 => "25th",
            26 => "26th",
            27 => "27th",
            28 => "28th",
            29 => "29th",
            30 => "30th",
            31 => "31st"
        ];
    }

    /**
     * @return array
     */
    public function getMonths(): array
    {
        return [
            0 => '--- Please Select ---',
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }

    /**
     * @return array
     */
    public function getYears(): array
    {
        return [
            0 => '--- Please Select ---',
            2020 => '2020',
            2019 => '2019',
            2018 => '2018',
            2017 => '2017',
            2016 => '2016',
            2015 => '2015',
            2014 => '2014',
            2013 => '2013',
            2012 => '2012',
            2011 => '2011',
            2010 => '2010',
            2009 => '2009',
            2008 => '2008',
            2007 => '2007',
            2006 => '2006',
            2005 => '2005',
            2004 => '2004',
            2003 => '2003',
            2002 => '2002',
            2001 => '2001',
            2000 => '2000',
            1999 => '1999',
            1998 => '1998',
            1997 => '1997',
            1996 => '1996',
            1995 => '1995',
            1994 => '1994',
            1993 => '1993',
            1992 => '1992',
            1991 => '1991',
            1990 => '1990',
            1989 => '1989',
            1988 => '1988',
            1987 => '1987',
            1986 => '1986',
            1985 => '1985',
            1984 => '1984',
            1983 => '1983',
            1982 => '1982',
            1981 => '1981',
            1980 => '1980',
            1979 => '1979',
            1978 => '1978',
            1977 => '1977',
            1976 => '1976',
            1975 => '1975',
            1974 => '1974',
            1973 => '1973',
            1972 => '1972',
            1971 => '1971',
            1970 => '1970',
            1969 => '1969',
            1968 => '1968',
            1967 => '1967',
            1966 => '1966',
            1965 => '1965',
            1964 => '1964',
            1963 => '1963',
            1962 => '1962',
            1961 => '1961',
            1960 => '1960'
        ];
    }

    /**
     * @return array
     */
    private function getCountries()
    {
        $countries = db()->select()->table(
            (self::$database_config['tables']['country']['name'] ?? 'countries')
        )->where((self::$database_config['table_status_key'] ?? 'is_active'), STATE_ACTIVE)->exec();

        return ($countries->isSuccessful() ? $countries->getQueryResponseArray() : []);
    }

    /**
     * @param int $countryID
     * @return array
     */
    private function getStates(int $countryID)
    {
        $states = db()->select()->table(
            (self::$database_config['tables']['state']['name'] ?? 'states')
        )->where((self::$database_config['tables']['state']['primary_key'] ?? 'id'), $countryID)
        ->where((self::$database_config['table_status_key'] ?? 'is_active'), STATE_ACTIVE)->exec();

        return ($states->isSuccessful() ? $states->getQueryResponseArray() : []);
    }

    /**
     * @param int $stateID
     * @return array
     */
    private function getAreas(int $stateID)
    {
        $areas = db()->select()->table((self::$database_config['tables']['area']['name'] ?? 'states'))
            ->where((self::$database_config['tables']['area']['primary_key'] ?? 'id'), $stateID)
            ->where((self::$database_config['table_status_key'] ?? 'is_active'), STATE_ACTIVE)->exec();

        return ($areas->isSuccessful() ? $areas->getQueryResponseArray() : []);
    }

    /**
     * @return array
     */
    private function getLanguages()
    {
        $language = db()->select()->table((self::$database_config['tables']['language']['name'] ?? 'languages'))
            ->where((self::$database_config['table_status_key'] ?? 'is_active'), STATE_ACTIVE)->exec();

        return ($language->isSuccessful() ? $language->getQueryResponseArray() : []);
    }
}
