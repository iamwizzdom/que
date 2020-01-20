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
    private static $instance;

    protected function __construct()
    {
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
     * @param string $default
     * @return string
     */
    public function convertGender(int $gender, int $genderType,
                                  string $default = 'Unknown'): string
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
     * @param string $default
     * @return string
     */
    public function convertCountry(int $countryID, string $default = 'All countries'): string
    {
        $country = db()->select(CONFIG['db_table']['country']['name'], '*', [
            'AND' => [
                CONFIG['db_table']['country']['primary_key'] => $countryID,
                CONFIG['db_table']['country']['status_key'] => STATE_ACTIVE
            ]
        ]);
        return ($country->isSuccessful() ?
            $country->getQueryResponseWithModel(0)->get('countryName')->getValue() : $default);
    }

    /**
     * @param int $countryID
     * @param int $stateID
     * @param string $default
     * @return string
     */
    public function convertState(int $countryID, int $stateID,
                                 string $default = 'All states'): string
    {
        $state = db()->select(CONFIG['db_table']['state']['name'], '*', [
            'AND' => [
                CONFIG['db_table']['country']['primary_key'] => $countryID,
                CONFIG['db_table']['state']['primary_key'] => $stateID,
                CONFIG['db_table']['state']['status_key'] => STATE_ACTIVE
            ]
        ]);
        return ($state->isSuccessful() ?
            $state->getQueryResponseWithModel(0)->get('stateName')->getValue() : $default);
    }

    /**
     * @param int $languageID
     * @return array
     */
    public function convertLanguage(int $languageID): array
    {
        $language = db()->select(CONFIG['db_table']['language']['name'], '*', [
            'AND' => [
                CONFIG['db_table']['language']['primary_key'] => $languageID,
                CONFIG['db_table']['language']['status_key'] => STATE_ACTIVE
            ]
        ]);

        return (
        $language->isSuccessful() ?
            $language->getQueryResponseArray(0) :
            []
        );
    }

    /**
     * @param int $countryID
     * @param int $stateID
     * @param int $areaID
     * @return array
     */
    public function convertArea(int $countryID, int $stateID,
                                int $areaID): array
    {
        $area = db()->select(CONFIG['db_table']['area']['name'], '*', [
            'AND' => [
                CONFIG['db_table']['country']['primary_key'] => $countryID,
                CONFIG['db_table']['state']['primary_key'] => $stateID,
                CONFIG['db_table']['area']['primary_key'] => $areaID,
                CONFIG['db_table']['area']['status_key'] => STATE_ACTIVE
            ]
        ]);
        return ($area->isSuccessful() ? $area->getQueryResponseArray(0) : []);
    }

    /**
     * @param int $ageRangeID
     * @param string $default
     * @return string
     */
    public function convertAgeRange(int $ageRangeID, string $default = 'None'): string
    {
        $flatList = $this->getFlatList();
        return $ageRangeID != 0 && array_key_exists($ageRangeID, $flatList->ageRange()) ?
            $flatList->ageRange()[$ageRangeID] : $default;
    }

    /**
     * @param int $maritalStatusID
     * @param string $default
     * @return string
     */
    public function convertMaritalStatus(int $maritalStatusID, string $default = 'None'): string
    {
        $flatList = $this->getFlatList();
        return $maritalStatusID != 0 && array_key_exists($maritalStatusID, $flatList->maritalStatus()) ?
            $flatList->maritalStatus()[$maritalStatusID] : $default;
    }

    /**
     * @param int $religionID
     * @param string $default
     * @return string
     */
    public function convertReligion(int $religionID, string $default = 'None'): string
    {
        $flatList = $this->getFlatList();
        return $religionID != 0 && array_key_exists($religionID, $flatList->religion()) ?
            $flatList->religion()[$religionID] : $default;
    }

    /**
     * @param int $bloodGroupID
     * @param string $default
     * @return string
     */
    public function convertBloodGroup(int $bloodGroupID, string $default = 'None'): string
    {
        $flatList = $this->getFlatList();
        return $bloodGroupID != 0 && array_key_exists($bloodGroupID, $flatList->bloodGroup()) ?
            $flatList->bloodGroup()[$bloodGroupID] : $default;
    }

    /**
     * @param int $genotypeID
     * @param string $default
     * @return string
     */
    public function convertGenotype(int $genotypeID, string $default = 'None'): string
    {
        $flatList = $this->getFlatList();
        return $genotypeID != 0 && array_key_exists($genotypeID, $flatList->genotype()) ?
            $flatList->genotype()[$genotypeID] : $default;
    }

    /**
     * @param int $educationID
     * @param string $default
     * @return string
     */
    public function convertEducationLevel(int $educationID, string $default = 'None'): string
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
     * @param string $default
     * @return string
     */
    public function convertJobType(int $jobTypeID, string $default = 'None'): string
    {
        $flatList = $this->getFlatList();
        return $jobTypeID != 0 && array_key_exists($jobTypeID, $flatList->jobTypes()) ?
            $flatList->jobTypes()[$jobTypeID] : $default;
    }

    /**
     * @param int $experienceID
     * @param string $default
     * @return string
     */
    public function convertExperience(int $experienceID, string $default = 'None'): string
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
        'title' => 'None',
        'subtitle' => 'None'
    ]): array
    {
        $flatList = $this->getFlatList();
        return $qualificationID != 0 && array_key_exists($qualificationID, $flatList->qualification()) ?
            $flatList->qualification()[$qualificationID] : $default;
    }

    /**
     * @param int $dayID
     * @param string $default
     * @return string
     */
    public function convertDay(int $dayID, string $default = 'None'): string
    {
        $flatList = $this->getFlatList();
        return $dayID != 0 && array_key_exists($dayID, $flatList->getDays()) ?
            $flatList->getDays()[$dayID] : $default;
    }

    /**
     * @param int $monthID
     * @param string $default
     * @return string
     */
    public function convertMonth(int $monthID, string $default = 'None'): string
    {
        $flatList = $this->getFlatList();
        return $monthID != 0 && array_key_exists($monthID, $flatList->getMonths()) ?
            $flatList->getMonths()[$monthID] : $default;
    }

    /**
     * @param int $yearID
     * @param string $default
     * @return string
     */
    public function convertYear(int $yearID, string $default = 'None'): string
    {
        $flatList = $this->getFlatList();
        return $yearID != 0 && array_key_exists($yearID, $flatList->getYears()) ?
            $flatList->getYears()[$yearID] : $default;
    }

    /**
     * @param int $gender
     * @param string $default
     * @return string
     */
    private function genderMaleFemale(int $gender, string $default): string
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
     * @param string $default
     * @return string
     */
    private function genderHimHer(int $gender, string $default): string
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
     * @param string $default
     * @return string
     */
    private function genderHisHer(int $gender, string $default): string
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
     * @param string $default
     * @return string
     */
    private function genderHeShe(int $gender, string $default): string
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