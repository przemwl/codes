<?php
/**
 * Created by PhpStorm.
 * User: siemek
 * Date: 5/26/15
 * Time: 10:10 AM
 */

class Pos_Validator
{
    protected $_inputType;
    protected $_submitted;
    protected $_required;
    protected $_filtered;
    protected $_missing;
    public  $_filterArgs;
    public  $_errors;

    public function __construct($required = array(), $inputType = 'post')
    {
        if (!function_exists('filter_list')) {
            throw new Exception('The Pos_Validator class requires the Filter Functions in >= PHP 5.2 or PECL.');
        }
        if (!is_null($required) && !is_array($required)) {
            throw new Exception('The names of required fields must be an array, even if only one field is required.');
        }
        $this->_required = $required;
        $this->setInputType($inputType);
        if ($this->_required) {
            $this->checkRequired();
        }
        $this->_filterArgs = array();
        $this->_errors = array();
        $this->_booleans = array();
    }

    protected function setInputType($type)
    {
        switch (strtolower($type)) {
            case 'post':
                $this->_inputType = INPUT_POST;
                $this->_submitted = $_POST;
                break;
            case 'get':
                $this->_inputType = INPUT_GET;
                $this->_submitted = $_GET;
                break;
            default:
                throw new Exception('Invalid input type. Valid type are "post" and "get".');
        }
    }

    protected function checkRequired()
    {
        $OK = array();
        foreach ($this->_submitted as $name => $value) {
            $value = is_array($value) ? $value : trim($value);
            if(!empty($value)) {
                $OK[] = $name;
            }
        }
        $this->_missing = array_diff($this->_required, $OK );
    }

    protected function checkDuplicateFilter($fieldName)
    {
        if(isset($this->_filterArgs[$fieldName])) {
            throw new Exception('A filter has already been set for the following field. ');
        }
    }

    public function isInt($fieldName, $min = null, $max = null)
    {
        $this->checkDuplicateFilter($fieldName);
        $this->_filterArgs[$fieldName] = array('filter', FILTER_VALIDATE_INT);
        if (is_int($min)) {
            $this->_filterArgs[$fieldName]['options']['min_range'] = $min;
        }
        if (is_int($max)) {
            $this->_filterArgs[$fieldName]['options']['max_range'] = $max;
        }
    }

    public function isFloat($fieldName, $decimalPoint = '.', $allowThousandSeparator = true)
    {
        $this->checkDuplicateFilter($fieldName);
        if ($decimalPoint !=  '.' && $decimalPoint != ',') {
            throw new Exception('Decimal point must be a comma or period in isFloat().');
        }
        $this->_filterArgs[$fieldName] = array (
            'filter' => FILTER_VALIDATE_FLOAT,
            'options'=> array('decimal' => $decimalPoint)
        );
        if ($allowThousandSeparator) {
            $this->_filterArgs[$fieldName]['flags'] = FILTER_FLAG_ALLOW_THOUSAND;
        }
    }

    public function isNumericArray($fieldName, $decimalPoint = '.', $allowDecimalFractions = true,
                                   $allowThousandSeparator = true)
    {
        $this->checkDuplicateFilter($fieldName);
        if ($decimalPoint != '.' && $decimalPoint != ',') {
            throw new Exception('Decimal point must be a comma or period in isFloat().');
        }
        $this->_filterArgs[$fieldName] = array(
            'filter' => FILTER_VALIDATE_FLOAT,
            'flags'  => FILTER_REQUIRE_ARRAY,
            'options'=> array('decimal' => $decimalPoint)
        );
        if ($allowDecimalFractions) {
            $this->_filterArgs[$fieldname]['flags'] = FILTER_FLAG_ALLOW_FRACTION;
        }
        if ($allowThousandSeparator) {
            $this->_filterArgs[$fieldName]['flags'] = FILTER_FLAG_ALLOW_THOUSAND;
        }
    }

    public function isEmail($fieldName)
    {
        $this->checkDuplicateFilter($fieldName);
        $this->_filterArgs[$fieldName] = FILTER_VALIDATE_EMAIL;
    }

    public function isFullURL($fieldName, $queryStringRequired = false)
    {
        $this->checkDuplicateFilter($fieldName);
        $this->_filterArgs[$fieldName] = array(
            'filter' => FILTER_VALIDATE_URL,
            'flags'  => FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED | FILTER_FLAG_PATH_REQUIRED
        );
        if ($queryStringRequired) {
            $this->_filterArgs[$fieldName]['flags'] = FILTER_FLAG_QUERY_REQUIRED;
        }
    }

    public function isURL($fieldName, $queryStringRequired = false)
    {
        $this->checkDuplicateFilter($fieldName);
        $this->_filterArgs[$fieldName]['filter'] = FILTER_VALIDATE_URL;
        if ($queryStringRequired) {
            $this->_filterArgs[$fieldName]['flags'] = FILTER_FLAG_QUERY_REQUIRED;
        }
    }

    public function isBool($fieldName, $nullOnFaliure = false)
    {
        $this->checkDuplicateFilter($fieldName);
        $this->_booleans[] = $fieldName;
        $this->_filterArgs[$fieldName]['filter'] = FILTER_VALIDATE_BOOLEAN;
        if ($nullOnFaliure) {
            $this->_filterArgs[$fieldName]['flags'] = FILTER_NULL_ON_FAILURE;
        }
    }

    public function matches($fieldName, $pattern)
    {
        $this->checkDuplicateFilter($fieldName);
        $this->_filterArgs[$fieldName] = array(
            'filter' => FILTER_VALIDATE_REGEXP,
            'options'=> array('regexp' => $pattern)
        );
    }

    public function removeTags($fieldName, $encodeAmp = false, $preserveQuotes = false, $encodeLow = false,
                               $encodeHigh = false, $stripLow = false, $stripHigh = false)
    {
        $this->checkDuplicateFilter($fieldName);
        $this->_filterArgs[$fieldName]['filter'] = FILTER_SANITIZE_STRING;
        $this->_filterArgs[$fieldName]['flags'] = 0;
        if ($encodeAmp) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_ENCODE_AMP;
        }
        if ($preserveQuotes) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_NO_ENCODE_QUOTES;
        }
        if ($encodeLow) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_ENCODE_LOW;
        }
        if ($encodeHigh) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_ENCODE_HIGH;
        }
        if ($stripLow) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_STRIP_LOW;
        }
        if ($stripHigh) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_STRIP_HIGH;
        }
    }

    public function removeTagsFromArray($fieldName, $encodeAmp = false, $preserveQuotes = false, $encodeLow = false,
                                        $encodeHigh = false, $stripLow = false, $stripHigh = false)
    {
        $this->checkDuplicateFilter($fieldName);
        $this->_filterArgs[$fieldName]['filter'] = FILTER_SANITIZE_STRING;
        $this->_filterArgs[$fieldName]['flags'] = FILTER_REQUIRE_ARRAY;
        if ($encodeAmp) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_ENCODE_AMP;
        }
        if ($preserveQuotes) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_NO_ENCODE_QUOTES;
        }
        if ($encodeLow) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_ENCODE_LOW;
        }
        if ($encodeHigh) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_ENCODE_HIGH;
        }
        if ($stripLow) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_STRIP_LOW;
        }
        if ($stripHigh) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_STRIP_HIGH;
        }
    }

    public function useEntities($fieldName, $isArray = false, $encodeHigh = false, $stripLow = false, $stripHigh= false)
    {
        $this->checkDuplicateFilter($fieldName);
        $this->_filterArgs[$fieldName]['filter'] = FILTER_SANITIZE_SPECIAL_CHARS;
        $this->_filterArgs[$fieldName]['flags'] = 0;
        if ($isArray) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_REQUIRE_ARRAY;
        }
        if ($encodeHigh) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_ENCODE_HIGH;
        }
        if ($stripLow) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_STRIP_LOW;
        }
        if ($stripHigh) {
            $this->_filterArgs[$fieldName]['flags'] |= FILTER_FLAG_STRIP_HIGH;
        }
    }

    public function checkTextLength($fieldName, $min, $max = null)
    {
        $text = trim($this->_submitted[$fieldName]);
        if (!is_string($text)) {
            throw new Exception('The checkTextLength() can be aplied only to strings (field name:
            ' . $fieldName . '.');
        }
        if (!is_numeric($min)) {
            throw new Exception('The checkTextLength() method expects a number as the second argument (field name:
            ' . $fieldName . '.');
        }
        if (strlen($text) < $min) {
            if (is_numeric($max)) {
                $this->_errors[$fieldName] = ucfirst($fieldName) . " must be between $min and $max characters.";
            } else {
                $this->_errors[$fieldName] = ucfirst($fieldName) . " must be a minumum of $min characters.";
            }
        }
        if (is_numeric($max) && strlen($text) > $max) {
            if ($min == 0) {
                $this->_errors[$fieldName] = ucfirst($fieldName) . " must be no more than $max characters.";
            } else {
                $this->_errors[$fieldName] = ucfirst($fieldName) . " must be between $min and $max characters.";
            }
        }
    }

    public function noFilter($feldName, $isArray = false, $encodeAmp = false )
    {
        $this->checkDuplicateFilter($feldName);
        $this->_filterArgs[$fieldName]['filter'] = FILTER_UNSAFE_RAW;
        $this->_filterArgs[$fieldName]['flags'] = 0;
        if ($encodeAmp) {
            $this->_filterArgs[$fieldName]['flags'] = FILTER_FLAG_ENCODE_AMP;
            if ($isArray) {
                $this->_filterArgs[$fieldName]['flags'] = FILTER_REQUIRE_ARRAY;
            }
        }
    }

    public function validateInput()
    {
        $notFiltered = array();
        $tested = array_keys($this->_filterArgs);
        foreach($this->_required as $field) {
            if (in_array($field, $tested)) {
                $notFiltered[] = $field;
            }
        }
        if ($notFiltered) {
            throw new Exception('No filter has been set for the following required item(s): ' . implode($notFiltered));
        }
        $this->_filtered= filter_input_array($this->_inputType, $this->_filterArgs);
        foreach($this->_filtered as $key => $value) {
            if (in_array($key, $this->_booleans) || in_array($key, $this->_missing) ||
                !in_array($key, $this->_required)) {
                continue;
            }
            elseif ($value === false) {
                var_dump($key);
                $this->_errors[$key] = ucfirst($key) . ':invalid data supplied';
            }
        }
        return $this->_filtered;

    }

    public function getMissing() {
        return  $this->_missing;
    }

    public function getFiltered() {
        return $this->_filtered;
    }

    public function getErrors() {
        return $this->_errors;
    }


}