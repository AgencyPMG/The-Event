<?php
/**
 * Un-namespaced functions.
 *
 * @author          Christopher Davis <chris@pmg.co>
 * @copyright       Performance Media Group 2012
 * @since           0.1
 * @package         TheEvent
 * @license         GPLv2
 */

!defined('ABSPATH') && exit;

function pmg_events_autoload($cls)
{
    $cls = ltrim($cls, '\\');
    if (strpos($cls, 'PMG\\TheEvent') !== 0) {
        return false; // not this namespace
    }

    $cls = str_replace('PMG\\TheEvent', '', $cls);

    $path = PMG_TE_PATH . 'lib' .
        str_replace('\\', DIRECTORY_SEPARATOR, $cls) . '.php';

    require_once $path;
}

function pmg_events_country_name($code)
{
    $code = strtoupper($code);
    $countries = pmg_events_countries();
    return isset($countries[$code]) ? $countries[$code] : null;
}

function pmg_events_countries()
{
    static $countries = null;

    if (null === $countries) {
        $preferred = apply_filters('pmg_events_preferred_countries', array(
            'US' => __('United States of America', 'the-event'),
            'CA' => __('Canada', 'the-event'),
        ));

        $others = apply_filters('pmg_events_other_countries', array(
            'AD' => __('Andorra', 'the-event'),
            'AE' => __('United Arab Emirates', 'the-event'),
            'AF' => __('Afghanistan', 'the-event'),
            'AG' => __('Antigua and Barbuda', 'the-event'),
            'AI' => __('Anguilla', 'the-event'),
            'AL' => __('Albania', 'the-event'),
            'AM' => __('Armenia', 'the-event'),
            'AO' => __('Angola', 'the-event'),
            'AQ' => __('Antarctica', 'the-event'),
            'AR' => __('Argentina', 'the-event'),
            'AS' => __('American Samoa', 'the-event'),
            'AT' => __('Austria', 'the-event'),
            'AU' => __('Australia', 'the-event'),
            'AW' => __('Aruba', 'the-event'),
            'AX' => __('Aland Islands !Åland Islands', 'the-event'),
            'AZ' => __('Azerbaijan', 'the-event'),
            'BA' => __('Bosnia and Herzegovina', 'the-event'),
            'BB' => __('Barbados', 'the-event'),
            'BD' => __('Bangladesh', 'the-event'),
            'BE' => __('Belgium', 'the-event'),
            'BF' => __('Burkina Faso', 'the-event'),
            'BG' => __('Bulgaria', 'the-event'),
            'BH' => __('Bahrain', 'the-event'),
            'BI' => __('Burundi', 'the-event'),
            'BJ' => __('Benin', 'the-event'),
            'BL' => __('Saint Barthélemy', 'the-event'),
            'BM' => __('Bermuda', 'the-event'),
            'BN' => __('Brunei Darussalam', 'the-event'),
            'BO' => __('Bolivia, Plurinational State of', 'the-event'),
            'BQ' => __('Bonaire, Sint Eustatius and Saba', 'the-event'),
            'BR' => __('Brazil', 'the-event'),
            'BS' => __('Bahamas', 'the-event'),
            'BT' => __('Bhutan', 'the-event'),
            'BV' => __('Bouvet Island', 'the-event'),
            'BW' => __('Botswana', 'the-event'),
            'BY' => __('Belarus', 'the-event'),
            'BZ' => __('Belize', 'the-event'),
            'CC' => __('Cocos (Keeling) Islands', 'the-event'),
            'CD' => __('Congo, the Democratic Republic of the', 'the-event'),
            'CF' => __('Central African Republic', 'the-event'),
            'CG' => __('Congo', 'the-event'),
            'CH' => __('Switzerland', 'the-event'),
            'CI' => __('Cote d\'Ivoire !Côte d\'Ivoire', 'the-event'),
            'CK' => __('Cook Islands', 'the-event'),
            'CL' => __('Chile', 'the-event'),
            'CM' => __('Cameroon', 'the-event'),
            'CN' => __('China', 'the-event'),
            'CO' => __('Colombia', 'the-event'),
            'CR' => __('Costa Rica', 'the-event'),
            'CU' => __('Cuba', 'the-event'),
            'CV' => __('Cabo Verde', 'the-event'),
            'CW' => __('Curaçao', 'the-event'),
            'CX' => __('Christmas Island', 'the-event'),
            'CY' => __('Cyprus', 'the-event'),
            'CZ' => __('Czech Republic', 'the-event'),
            'DE' => __('Germany', 'the-event'),
            'DJ' => __('Djibouti', 'the-event'),
            'DK' => __('Denmark', 'the-event'),
            'DM' => __('Dominica', 'the-event'),
            'DO' => __('Dominican Republic', 'the-event'),
            'DZ' => __('Algeria', 'the-event'),
            'EC' => __('Ecuador', 'the-event'),
            'EE' => __('Estonia', 'the-event'),
            'EG' => __('Egypt', 'the-event'),
            'EH' => __('Western Sahara', 'the-event'),
            'ER' => __('Eritrea', 'the-event'),
            'ES' => __('Spain', 'the-event'),
            'ET' => __('Ethiopia', 'the-event'),
            'FI' => __('Finland', 'the-event'),
            'FJ' => __('Fiji', 'the-event'),
            'FK' => __('Falkland Islands (Malvinas)', 'the-event'),
            'FM' => __('Micronesia, Federated States of', 'the-event'),
            'FO' => __('Faroe Islands', 'the-event'),
            'FR' => __('France', 'the-event'),
            'GA' => __('Gabon', 'the-event'),
            'GB' => __('United Kingdom of Great Britain and Northern Ireland', 'the-event'),
            'GD' => __('Grenada', 'the-event'),
            'GE' => __('Georgia', 'the-event'),
            'GF' => __('French Guiana', 'the-event'),
            'GG' => __('Guernsey', 'the-event'),
            'GH' => __('Ghana', 'the-event'),
            'GI' => __('Gibraltar', 'the-event'),
            'GL' => __('Greenland', 'the-event'),
            'GM' => __('Gambia', 'the-event'),
            'GN' => __('Guinea', 'the-event'),
            'GP' => __('Guadeloupe', 'the-event'),
            'GQ' => __('Equatorial Guinea', 'the-event'),
            'GR' => __('Greece', 'the-event'),
            'GS' => __('South Georgia and the South Sandwich Islands', 'the-event'),
            'GT' => __('Guatemala', 'the-event'),
            'GU' => __('Guam', 'the-event'),
            'GW' => __('Guinea-Bissau', 'the-event'),
            'GY' => __('Guyana', 'the-event'),
            'HK' => __('Hong Kong', 'the-event'),
            'HM' => __('Heard Island and McDonald Islands', 'the-event'),
            'HN' => __('Honduras', 'the-event'),
            'HR' => __('Croatia', 'the-event'),
            'HT' => __('Haiti', 'the-event'),
            'HU' => __('Hungary', 'the-event'),
            'ID' => __('Indonesia', 'the-event'),
            'IE' => __('Ireland', 'the-event'),
            'IL' => __('Israel', 'the-event'),
            'IM' => __('Isle of Man', 'the-event'),
            'IN' => __('India', 'the-event'),
            'IO' => __('British Indian Ocean Territory', 'the-event'),
            'IQ' => __('Iraq', 'the-event'),
            'IR' => __('Iran, Islamic Republic of', 'the-event'),
            'IS' => __('Iceland', 'the-event'),
            'IT' => __('Italy', 'the-event'),
            'JE' => __('Jersey', 'the-event'),
            'JM' => __('Jamaica', 'the-event'),
            'JO' => __('Jordan', 'the-event'),
            'JP' => __('Japan', 'the-event'),
            'KE' => __('Kenya', 'the-event'),
            'KG' => __('Kyrgyzstan', 'the-event'),
            'KH' => __('Cambodia', 'the-event'),
            'KI' => __('Kiribati', 'the-event'),
            'KM' => __('Comoros', 'the-event'),
            'KN' => __('Saint Kitts and Nevis', 'the-event'),
            'KP' => __('Korea, Democratic People\'s Republic of', 'the-event'),
            'KR' => __('Korea, Republic of', 'the-event'),
            'KW' => __('Kuwait', 'the-event'),
            'KY' => __('Cayman Islands', 'the-event'),
            'KZ' => __('Kazakhstan', 'the-event'),
            'LA' => __('Lao People\'s Democratic Republic', 'the-event'),
            'LB' => __('Lebanon', 'the-event'),
            'LC' => __('Saint Lucia', 'the-event'),
            'LI' => __('Liechtenstein', 'the-event'),
            'LK' => __('Sri Lanka', 'the-event'),
            'LR' => __('Liberia', 'the-event'),
            'LS' => __('Lesotho', 'the-event'),
            'LT' => __('Lithuania', 'the-event'),
            'LU' => __('Luxembourg', 'the-event'),
            'LV' => __('Latvia', 'the-event'),
            'LY' => __('Libya', 'the-event'),
            'MA' => __('Morocco', 'the-event'),
            'MC' => __('Monaco', 'the-event'),
            'MD' => __('Moldova, Republic of', 'the-event'),
            'ME' => __('Montenegro', 'the-event'),
            'MF' => __('Saint Martin (French part)', 'the-event'),
            'MG' => __('Madagascar', 'the-event'),
            'MH' => __('Marshall Islands', 'the-event'),
            'MK' => __('Macedonia, the former Yugoslav Republic of', 'the-event'),
            'ML' => __('Mali', 'the-event'),
            'MM' => __('Myanmar', 'the-event'),
            'MN' => __('Mongolia', 'the-event'),
            'MO' => __('Macao', 'the-event'),
            'MP' => __('Northern Mariana Islands', 'the-event'),
            'MQ' => __('Martinique', 'the-event'),
            'MR' => __('Mauritania', 'the-event'),
            'MS' => __('Montserrat', 'the-event'),
            'MT' => __('Malta', 'the-event'),
            'MU' => __('Mauritius', 'the-event'),
            'MV' => __('Maldives', 'the-event'),
            'MW' => __('Malawi', 'the-event'),
            'MX' => __('Mexico', 'the-event'),
            'MY' => __('Malaysia', 'the-event'),
            'MZ' => __('Mozambique', 'the-event'),
            'NA' => __('Namibia', 'the-event'),
            'NC' => __('New Caledonia', 'the-event'),
            'NE' => __('Niger', 'the-event'),
            'NF' => __('Norfolk Island', 'the-event'),
            'NG' => __('Nigeria', 'the-event'),
            'NI' => __('Nicaragua', 'the-event'),
            'NL' => __('Netherlands', 'the-event'),
            'NO' => __('Norway', 'the-event'),
            'NP' => __('Nepal', 'the-event'),
            'NR' => __('Nauru', 'the-event'),
            'NU' => __('Niue', 'the-event'),
            'NZ' => __('New Zealand', 'the-event'),
            'OM' => __('Oman', 'the-event'),
            'PA' => __('Panama', 'the-event'),
            'PE' => __('Peru', 'the-event'),
            'PF' => __('French Polynesia', 'the-event'),
            'PG' => __('Papua New Guinea', 'the-event'),
            'PH' => __('Philippines', 'the-event'),
            'PK' => __('Pakistan', 'the-event'),
            'PL' => __('Poland', 'the-event'),
            'PM' => __('Saint Pierre and Miquelon', 'the-event'),
            'PN' => __('Pitcairn', 'the-event'),
            'PR' => __('Puerto Rico', 'the-event'),
            'PS' => __('Palestine, State of', 'the-event'),
            'PT' => __('Portugal', 'the-event'),
            'PW' => __('Palau', 'the-event'),
            'PY' => __('Paraguay', 'the-event'),
            'QA' => __('Qatar', 'the-event'),
            'RE' => __('Reunion !Réunion', 'the-event'),
            'RO' => __('Romania', 'the-event'),
            'RS' => __('Serbia', 'the-event'),
            'RU' => __('Russian Federation', 'the-event'),
            'RW' => __('Rwanda', 'the-event'),
            'SA' => __('Saudi Arabia', 'the-event'),
            'SB' => __('Solomon Islands', 'the-event'),
            'SC' => __('Seychelles', 'the-event'),
            'SD' => __('Sudan', 'the-event'),
            'SE' => __('Sweden', 'the-event'),
            'SG' => __('Singapore', 'the-event'),
            'SH' => __('Saint Helena, Ascension and Tristan da Cunha', 'the-event'),
            'SI' => __('Slovenia', 'the-event'),
            'SJ' => __('Svalbard and Jan Mayen', 'the-event'),
            'SK' => __('Slovakia', 'the-event'),
            'SL' => __('Sierra Leone', 'the-event'),
            'SM' => __('San Marino', 'the-event'),
            'SN' => __('Senegal', 'the-event'),
            'SO' => __('Somalia', 'the-event'),
            'SR' => __('Suriname', 'the-event'),
            'SS' => __('South Sudan', 'the-event'),
            'ST' => __('Sao Tome and Principe', 'the-event'),
            'SV' => __('El Salvador', 'the-event'),
            'SX' => __('Sint Maarten (Dutch part)', 'the-event'),
            'SY' => __('Syrian Arab Republic', 'the-event'),
            'SZ' => __('Swaziland', 'the-event'),
            'TC' => __('Turks and Caicos Islands', 'the-event'),
            'TD' => __('Chad', 'the-event'),
            'TF' => __('French Southern Territories', 'the-event'),
            'TG' => __('Togo', 'the-event'),
            'TH' => __('Thailand', 'the-event'),
            'TJ' => __('Tajikistan', 'the-event'),
            'TK' => __('Tokelau', 'the-event'),
            'TL' => __('Timor-Leste', 'the-event'),
            'TM' => __('Turkmenistan', 'the-event'),
            'TN' => __('Tunisia', 'the-event'),
            'TO' => __('Tonga', 'the-event'),
            'TR' => __('Turkey', 'the-event'),
            'TT' => __('Trinidad and Tobago', 'the-event'),
            'TV' => __('Tuvalu', 'the-event'),
            'TW' => __('Taiwan, Province of China', 'the-event'),
            'TZ' => __('Tanzania, United Republic of', 'the-event'),
            'UA' => __('Ukraine', 'the-event'),
            'UG' => __('Uganda', 'the-event'),
            'UM' => __('United States Minor Outlying Islands', 'the-event'),
            'UY' => __('Uruguay', 'the-event'),
            'UZ' => __('Uzbekistan', 'the-event'),
            'VA' => __('Holy See', 'the-event'),
            'VC' => __('Saint Vincent and the Grenadines', 'the-event'),
            'VE' => __('Venezuela, Bolivarian Republic of', 'the-event'),
            'VG' => __('Virgin Islands, British', 'the-event'),
            'VI' => __('Virgin Islands, U.S.', 'the-event'),
            'VN' => __('Viet Nam', 'the-event'),
            'VU' => __('Vanuatu', 'the-event'),
            'WF' => __('Wallis and Futuna', 'the-event'),
            'WS' => __('Samoa', 'the-event'),
            'YE' => __('Yemen', 'the-event'),
            'YT' => __('Mayotte', 'the-event'),
            'ZA' => __('South Africa', 'the-event'),
            'ZM' => __('Zambia', 'the-event'),
            'ZW' => __('Zimbabwe', 'the-event'),
        ));
        asort($others);

        $countries = apply_filters('pmg_events_countries', array_merge($preferred, $others));
    }

    return $countries;
}
