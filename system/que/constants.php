<?php

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/12/2018
 * Time: 7:23 PM
 */


/**
 * Alert constants
 */
const ALERT_ERROR = 0;
const ALERT_SUCCESS = 1;
const ALERT_WARNING = 2;
const ALERT_BUTTON_OPTION_DEFAULT = 0;
const ALERT_BUTTON_OPTION_NEW_TAB = 1;
const ALERT_BUTTON_OPTION_POP_UP = 2;

/**
 * Status code constants
 */
const ERROR = -1;
const WARNING = 0;
const SUCCESS = 1;
const PENDING = 2;
const INFO = 3;
const NOTHING = 4;

/**
 * function constants
 */
const FILTER_EMAIL_GET_NAME = 0;
const FILTER_EMAIL_GET_HOST = 1;
const STRPOS_IN_ARRAY_OPT_DEFAULT = -1;
const STRPOS_IN_ARRAY_OPT_ARRAY_INDEX = 0;
const STRPOS_IN_ARRAY_OPT_STR_POSITION = 1;

/**
 * Action constants
 */
const MAX_RETRY = 5;

/**
 * File constants
 */
const MAX_FILE = 999;


/**
 * System state constants
 */
const STATE_TERMINATION = -14;
const STATE_BLOCKED = -13;
const STATE_UNBLOCKED = -12;
const STATE_FRAUD = -11;
const STATE_DAMAGED = -10;
const STATE_DUPLICATE = -9;
const STATE_SUSPENDED = -8;
const STATE_CONFLICT = -7;
const STATE_ERROR = -6;
const STATE_EXPIRED = -5;
const STATE_CLOSED = -4;
const STATE_INVALID = -3;
const STATE_UNKNOWN = -2;
const STATE_FAILED = -1;
const STATE_INACTIVE = 0;
const STATE_ACTIVE = 1;
const STATE_PROCESSING = 2;
const STATE_CANCELED = 3;
const STATE_REJECTED = 4;
const STATE_REVERSED = 5;
const STATE_FREEZE = 6;
const STATE_RETRY = 7;
const STATE_OPEN = 8;
const STATE_AWAITING = 9;
const STATE_STALEMATE = 10;
const STATE_INVITATION = 11;
const STATE_UNVERIFIED = 12;
const STATE_VERIFIED = 13;
const STATE_PUBLIC = 14;
const STATE_PRIVATE = 15;
const STATE_SUCCESSFUL = 16;
const STATE_PENDING = 17;
const STATE_UNSEEN = 18;
const STATE_SEEN = 19;
const STATE_SEEN_CLEARED = 20;

/**
 * Age constants
 */
const MAX_AGE = 18;
const AGE_RANGE_ALL = 1;
const AGE_RANGE_13_20 = 2;
const AGE_RANGE_21_40 = 3;
const AGE_RANGE_41_60 = 4;
const AGE_RANGE_61_80 = 5;
const AGE_RANGE_81_100 = 6;
const AGE_RANGE_101_INFINITE = 7;

/**
 * Gender constants
 */
const GENDER_MALE = 1;
const GENDER_FEMALE = 2;

/**
 * Gender conversion constants
 */
const GENDER_TYPE_MALE_FEMALE = 1;
const GENDER_TYPE_HE_SHE = 2;
const GENDER_TYPE_HIM_HER = 3;
const GENDER_TYPE_HIS_HER = 4;

/**
 * Pagination record per page constant
 */
const DEFAULT_PAGINATION_PER_PAGE = 20;

/**
 * Country
 */
const COUNTRY_NIGERIA = 1;

/**
 * Breakdown RuntimeError Levels
 */
const ERROR_NEGLIGIBLE = -1;
const ERROR_LOW = -2;
const ERROR_AVERAGE = -4;
const ERROR_SUBSTANTIAL = -8;
const ERROR_HIGH = -16;
const ERROR_SEVERE = -32;
const ERROR_CRITICAL = -64;


/**
 * Activity
 */
const ACTIVITY_UNKNOWN = 0;
const ACTIVITY_AUTH = 1;
const ACTIVITY_ADD = 2;
const ACTIVITY_DELETE = 3;
const ACTIVITY_EDIT = 4;
const ACTIVITY_UPDATE = 5;
const ACTIVITY_VIEW = 6;
const ACTIVITY_STATS = 7;
const ACTIVITY_LOGIN = 8;
const ACTIVITY_LOGOUT = 9;
const ACTIVITY_SENT = 10;
const ACTIVITY_RECEIVED = 11;
const ACTIVITY_ACTIVATE = 12;
const ACTIVITY_DEACTIVATE = 13;
const ACTIVITY_CREDIT = 14;
const ACTIVITY_DEBIT = 15;

/**
 * General Approval States
 */
const APPROVAL_INVALID = -2;
const APPROVAL_FAILED = -1;
const APPROVAL_PENDING = 0;
const APPROVAL_SUCCESSFUL = 1;
const APPROVAL_PROCESSING = 2;
const APPROVAL_CANCELED = 3;
const APPROVAL_REJECTED = 4;
const APPROVAL_REVERSED = 5;
const APPROVAL_MODIFIED = 6;

/**
 * Connection
 */
const CONNECT_UNKNOWN = -8;
const CONNECT_DECLINED = -1;
const CONNECT_PENDING = 0;
const CONNECT_ACCEPTED = 1;
const CONNECT_BLOCKED = 2;
const CONNECT_TIMEOUT = 3;

/**
 * Date Format
 */
const DATE_FORMAT_MYSQL = "Y-m-d G:i:s";
const DATE_FORMAT_MYSQL_SHORT = "Y-m-d";
const DATE_FORMAT_ISSUE = "F Y";
const DATE_FORMAT_SELECT = "d/m/Y";
const DATE_FORMAT_REPORT = "F j, Y";
const DATE_FORMAT_DISPLAY = "d F Y";
const DATE_FORMAT_PICKER_DATETIME = "d F Y - G:i";
const DATE_FORMAT_JS = "F d, Y G:i:s";
const DATE_FORMAT_JS2 = "Y-m-d\TG:i:s";
const DATE_FORMAT_YMD_D = "Y-m-d";
const DATE_FORMAT_DMY_D = "d-m-Y";
const DATE_FORMAT_YMD_S = "Y/m/d";
const DATE_TIME_FORMAT_G_i = "G:i";
const DATE_TIME_FORMAT_g_i = "g:i";
const DATE_TIME_FORMAT_h_i = "h:i";
const DATE_TIME_FORMAT_H_i = "H:i";

/**
 * Date Duration
 */
const DATE_DURATION_NANO = 1;
const DATE_DURATION_MILLISECOND = 2;
const DATE_DURATION_SECOND = 3;
const DATE_DURATION_MINUTE = 4;
const DATE_DURATION_HOUR = 5;
const DATE_DURATION_DAILY = 6;
const DATE_DURATION_WEEKLY = 7;
const DATE_DURATION_FORTNIGHT = 8;
const DATE_DURATION_QUARTERLY = 9;
const DATE_DURATION_BIANNUAL = 10;
const DATE_DURATION_YEARLY = 11;

/**
 * Status
 */
const STATUS_OK = 1;
const STATUS_ERROR = 0;

/**
 * Currency
 */
const CURRENCY_NAIRA = '₦';
const CURRENCY_DOLLAR = '$';

/**
 * Gateway Settings
 */
const GATEWAY_PROVIDER_ETRANZACT = 88;
const GATEWAY_PROVIDER_INTERSWITCH = 99;

/**
 * Money Settings
 */
const MONEY_PRECISION = 6;
const MONEY_CENT_VALVE = 100;

/**
 * Money Format Settings
 */
const MONEY_FORMAT_THOUSAND = ",";
const MONEY_FORMAT_DECIMAL = ".";

/**
 * Money Config Settings
 */
const MONEY_CONFIG_PRECISION = 2;

// Origin
const ORIGIN_USER = 1;
const ORIGIN_ENTITY = 2;

/**
 * Payment LIMITS
 */
const PAYMENT_TOPUP_MIN = 1000;
const PAYMENT_TOPUP_MAX = 10000000;
const PAYMENT_TOPUP_MAX_PENDING = 3;

/**
 * Payment Type
 */
const PAYMENT_TYPE_BANK = 1;
const PAYMENT_TYPE_CARD = 2;
const PAYMENT_TYPE_TRANSFER = 3;

/**
 * Payment in Cents
 */
const PAYMENT_CHARGE_BANK = 20000;
const PAYMENT_CHARGE_CARD = 10000;


/**
 * Request
 */
const REQUEST_ERROR = 0;
const REQUEST_NEW = 1;
const REQUEST_REFRESHED = 2;

/**
 * Transaction State
 */
const TRANSACTION_PAYMENT = 1;
const TRANSACTION_TRANSFER = 2;
const TRANSACTION_SETTLEMENT = 3;
const TRANSACTION_CHARGE = 4;
const TRANSACTION_CLEARANCE = 5;
const TRANSACTION_TOPUP = 6;
const TRANSACTION_REVERSED = 7;

/**
 * Transaction Type
 */
const TRANSACTION_CREDIT = 1;
const TRANSACTION_DEBIT = 2;

/**
 * Wallet Configurations
 */
const WALLET_TRANSFER_MIN = 1000;
const WALLET_TRANSFER_MAX = 10000000;

/**
 * Basic Instances
 */
const WHEN_NOW = 1;
const WHEN_SECS = 2;
const WHEN_MINS = 4;
const WHEN_HOUR = 8;
const WHEN_DAY = 16;
const WHEN_WEEK = 32;
const WHEN_MONTH = 64;
const WHEN_YEAR = 128;
const WHEN_CENTURY = 256;

/**
 * Timeout Settings
 */
const TIMEOUT_ONE_HOUR = 3600; // 1hr
const TIMEOUT_THIRTY_MIN = 1800; // 1hr
const TIMEOUT_TEN_MIN = 600; // 10 mins
const TIMEOUT_FIVE_MIN = 300; // 5 mins

define('APP_TIME', time());
define('APP_YEAR', date('Y'));

const QUE_VERSION = "1.0";
