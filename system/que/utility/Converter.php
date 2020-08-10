<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/16/2017
 * Time: 2:55 AM
 */

namespace que\utility;

class Converter
{
    /**
     * @var Converter
     */
    private static ?Converter $instance = null;

    /**
     * @var array
     */
    private static $database_config;

    /**
     * Converter constructor.
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
     * @return Converter
     */
    public static function getInstance(): Converter
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param int $gender
     * @param int $genderType
     * @param string|null $default
     * @return string|null
     */
    public function convertGender(int $gender, int $genderType,
                                  string $default = null): ?string
    {
        switch ($genderType) {
            case GENDER_TYPE_MALE_FEMALE:
                $gender = $this->genderMaleFemale($gender, $default);
                break;
            case GENDER_TYPE_HIM_HER:
                $gender = $this->genderHimHer($gender, $default);
                break;
            case GENDER_TYPE_HIS_HER:
                $gender = $this->genderHisHer($gender, $default);
                break;
            case GENDER_TYPE_HE_SHE:
                $gender = $this->genderHeShe($gender, $default);
                break;
            default:
                $gender = $default;
                break;
        }
        return $gender;
    }

    /**
     * @param int $countryID
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function convertCountry(int $countryID, string $key, string $default = null): ?string
    {
        $country = db()->select()->table(
            (self::$database_config['tables']['country']['name'] ?? 'countries')
        )->where((self::$database_config['tables']['country']['primary_key'] ?? 'id'), $countryID)
        ->where((self::$database_config['table_status_key'] ?? 'is_active'), STATE_ACTIVE)->exec();

        if ($country->isSuccessful()) {
            $country = $country->getQueryResponseArray(0);
            return $country[$key] ?? $default;
        }

        return $default;
    }

    /**
     * @param int $stateID
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function convertState(int $stateID, string $key, string $default = null): ?string
    {
        $state = db()->select()->table(
            (self::$database_config['tables']['state']['name'] ?? 'states')
        )->where((self::$database_config['tables']['state']['primary_key'] ?? 'id'), $stateID)
            ->where((self::$database_config['table_status_key'] ?? 'is_active'), STATE_ACTIVE)->exec();

        if ($state->isSuccessful()) {
            $state = $state->getQueryResponseArray(0);
            return $state[$key] ?? $default;
        }

        return $default;
    }

    /**
     * @param int $languageID
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function convertLanguage(int $languageID, string $key, string $default = null): ?string
    {
        $language = db()->select()->table(
            (self::$database_config['tables']['language']['name'] ?? 'languages')
        )->where((self::$database_config['tables']['language']['primary_key'] ?? 'id'), $languageID)
            ->where((self::$database_config['table_status_key'] ?? 'is_active'), STATE_ACTIVE)->exec();

        if ($language->isSuccessful()) {
            $language = $language->getQueryResponseArray(0);
            return $language[$key] ?? $default;
        }

        return $default;
    }

    /**
     * @param int $areaID
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function convertArea(int $areaID, string $key, string $default = null): ?string
    {
        $area = db()->select()->table(
            (self::$database_config['tables']['area']['name'] ?? 'areas')
        )->where((self::$database_config['tables']['area']['primary_key'] ?? 'id'), $areaID)
            ->where((self::$database_config['table_status_key'] ?? 'is_active'), STATE_ACTIVE)->exec();

        if ($area->isSuccessful()) {
            $area = $area->getQueryResponseArray(0);
            return $area[$key] ?? $default;
        }

        return $default;
    }

    /**
     * @param int $ageRangeID
     * @param string|null $default
     * @return string|null
     */
    public function convertAgeRange(int $ageRangeID, string $default = null): ?string
    {
        $flatList = $this->getFlatList();
        return $ageRangeID != 0 && array_key_exists($ageRangeID, $flatList->ageRange()) ?
            $flatList->ageRange()[$ageRangeID] : $default;
    }

    /**
     * @param int $maritalStatusID
     * @param string|null $default
     * @return string|null
     */
    public function convertMaritalStatus(int $maritalStatusID, string $default = null): ?string
    {
        $flatList = $this->getFlatList();
        return $maritalStatusID != 0 && array_key_exists($maritalStatusID, $flatList->maritalStatus()) ?
            $flatList->maritalStatus()[$maritalStatusID] : $default;
    }

    /**
     * @param int $religionID
     * @param string|null $default
     * @return string|null
     */
    public function convertReligion(int $religionID, string $default = null): ?string
    {
        $flatList = $this->getFlatList();
        return $religionID != 0 && array_key_exists($religionID, $flatList->religion()) ?
            $flatList->religion()[$religionID] : $default;
    }

    /**
     * @param int $bloodGroupID
     * @param string|null $default
     * @return string|null
     */
    public function convertBloodGroup(int $bloodGroupID, string $default = null): ?string
    {
        $flatList = $this->getFlatList();
        return $bloodGroupID != 0 && array_key_exists($bloodGroupID, $flatList->bloodGroup()) ?
            $flatList->bloodGroup()[$bloodGroupID] : $default;
    }

    /**
     * @param int $genotypeID
     * @param string|null $default
     * @return string|null
     */
    public function convertGenotype(int $genotypeID, string $default = null): ?string
    {
        $flatList = $this->getFlatList();
        return $genotypeID != 0 && array_key_exists($genotypeID, $flatList->genotype()) ?
            $flatList->genotype()[$genotypeID] : $default;
    }

    /**
     * @param int $educationID
     * @param string|null $default
     * @return string|null
     */
    public function convertEducationLevel(int $educationID, string $default = null): ?string
    {
        $flatList = $this->getFlatList();
        return $educationID != 0 && array_key_exists($educationID, $flatList->educationLevel()) ?
            $flatList->educationLevel()[$educationID] : $default;
    }

    /**
     * @param int $relationshipID
     * @param string $default
     * @return string
     */
    public function convertRelationship(int $relationshipID, string $default = 'None'): string
    {
        $flatList = $this->getFlatList();
        return $relationshipID != 0 && array_key_exists($relationshipID, $flatList->relationship()) ?
            $flatList->relationship()[$relationshipID] : $default;
    }

    /**
     * @param int $jobTypeID
     * @param string|null $default
     * @return string|null
     */
    public function convertJobType(int $jobTypeID, string $default = null): ?string
    {
        $flatList = $this->getFlatList();
        return $jobTypeID != 0 && array_key_exists($jobTypeID, $flatList->jobTypes()) ?
            $flatList->jobTypes()[$jobTypeID] : $default;
    }

    /**
     * @param int $experienceID
     * @param string|null $default
     * @return string|null
     */
    public function convertExperience(int $experienceID, string $default = null): ?string
    {
        $flatList = $this->getFlatList();
        return $experienceID != 0 && array_key_exists($experienceID, $flatList->experience()) ?
            $flatList->experience()[$experienceID] : $default;
    }

    /**
     * @param int $qualificationID
     * @param array $default
     * @return array
     */
    public function convertQualification(int $qualificationID, array $default = [
        'title' => null,
        'subtitle' => null
    ]): array
    {
        $flatList = $this->getFlatList();
        return $qualificationID != 0 && array_key_exists($qualificationID, $flatList->qualification()) ?
            $flatList->qualification()[$qualificationID] : $default;
    }

    /**
     * @param int $dayID
     * @param string|null $default
     * @return string|null
     */
    public function convertDay(int $dayID, string $default = null): ?string
    {
        $flatList = $this->getFlatList();
        return $dayID != 0 && array_key_exists($dayID, $flatList->getDays()) ?
            $flatList->getDays()[$dayID] : $default;
    }

    /**
     * @param int $monthID
     * @param string|null $default
     * @return string|null
     */
    public function convertMonth(int $monthID, string $default = null): ?string
    {
        $flatList = $this->getFlatList();
        return $monthID != 0 && array_key_exists($monthID, $flatList->getMonths()) ?
            $flatList->getMonths()[$monthID] : $default;
    }

    /**
     * @param int $yearID
     * @param string|null $default
     * @return string|null
     */
    public function convertYear(int $yearID, string $default = null): ?string
    {
        $flatList = $this->getFlatList();
        return $yearID != 0 && array_key_exists($yearID, $flatList->getYears()) ?
            $flatList->getYears()[$yearID] : $default;
    }

    /**
     * @param int $gender
     * @param string|null $default
     * @return string|null
     */
    private function genderMaleFemale(int $gender, string $default = null): ?string
    {
        switch ($gender) {
            case GENDER_MALE:
                $gender = 'Male';
                break;
            case GENDER_FEMALE:
                $gender = 'Female';
                break;
            default:
                $gender = $default;
                break;
        }
        return $gender;
    }

    /**
     * @param int $gender
     * @param string|null $default
     * @return string|null
     */
    private function genderHimHer(int $gender, string $default = null): ?string
    {
        switch ($gender) {
            case GENDER_MALE:
                $gender = 'Him';
                break;
            case GENDER_FEMALE:
                $gender = 'Her';
                break;
            default:
                $gender = $default;
                break;
        }
        return $gender;
    }

    /**
     * @param int $gender
     * @param string|null $default
     * @return string|null
     */
    private function genderHisHer(int $gender, string $default = null): ?string
    {
        switch ($gender) {
            case GENDER_MALE:
                $gender = 'His';
                break;
            case GENDER_FEMALE:
                $gender = 'Her';
                break;
            default:
                $gender = $default;
                break;
        }
        return $gender;
    }

    /**
     * @param int $gender
     * @param string|null $default
     * @return string|null
     */
    private function genderHeShe(int $gender, string $default = null): ?string
    {
        switch ($gender) {
            case GENDER_MALE:
                $gender = 'He';
                break;
            case GENDER_FEMALE:
                $gender = 'She';
                break;
            default:
                $gender = $default;
                break;
        }
        return $gender;
    }

    /**
     * @return FlatList
     */
    private function getFlatList(): FlatList
    {
        return FlatList::getInstance();
    }

}
