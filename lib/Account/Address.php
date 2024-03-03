<?php

namespace Littled\Account;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\EmailTextField;
use Littled\Request\FloatTextField;
use Littled\Request\IntegerSelect;
use Littled\Request\PhoneNumberTextField;
use Littled\Request\StringSelect;
use Littled\Request\StringTextField;
use DOMDocument;
use Exception;

/**
 * Class Address
 * @package Littled\Account
 */
class Address extends SerializedContent
{
    protected static string $table_name = 'address';

    /** @var string Google maps api key */
    protected static string $gmap_api_key;
    protected static string $address_data_template = 'forms/data/address_class_data.php';
    protected static string $street_address_data_template = 'forms/data/street_address_form_data.php';
    public const ID_KEY = "adid";
    public const LOCATION_KEY = "adlo";
    // possible values for formatting address data into strings
    public const FORMAT_ADDRESS_ONE_LINE = 'one_line';
    public const FORMAT_ADDRESS_HTML = 'html';
    public const FORMAT_ADDRESS_GOOGLE = 'google';

    /**
     * Inserts Google Maps key into URL to use to access Google Maps.
     * @return string google maps uri
     */
    public static function GOOGLE_MAPS_URI(): string
    {
        if (!isset(static::$gmap_api_key)) {
            return '';
        }
        return ("https://maps.googleapis.com/maps/api/geocode/xml?key=" . static::$gmap_api_key . "&address=");
    }

    public StringSelect $salutation;
    public StringTextField $first_name;
    public StringTextField $last_name;
    public StringTextField $location;
    public StringTextField $company;
    public StringTextField $address1;
    public StringTextField $address2;
    public StringTextField $city;
    public IntegerSelect $state_id;
    public StringTextField $state;
    public StringTextField $zip;
    public StringTextField $country;
    public PhoneNumberTextField $home_phone;
    public PhoneNumberTextField $work_phone;
    public PhoneNumberTextField $fax;
    public PhoneNumberTextField $mobile_phone;
    public EmailTextField $email;
    public StringTextField $title;
    public StringTextField $url;
    public FloatTextField $latitude;
    public FloatTextField $longitude;
    /** @var string Abbreviated state name. */
    public string $state_abbrev;
    /** @var string Combined first and last name. */
    public string $fullname;

    /**
     * Class constructor.
     * @param string $prefix (Optional) prefix to prepend to form elements.
     */
    function __construct($prefix = "")
    {
        parent::__construct();
        $this->id->setKey($prefix.self::ID_KEY)
            ->setLabel('Address id')
            ->setAsNotRequired();
        $this->salutation = new StringSelect("Salutation", "{$prefix}adsl", false, "", 10);
        $this->first_name = new StringTextField("First Name", "{$prefix}adfn", true, "", 50);
        $this->last_name = new StringTextField("Last Name", "{$prefix}adln", true, "", 50);
        $this->location = new StringTextField("Location name", $prefix . self::LOCATION_KEY, false, "", 200);
        $this->company = new StringTextField("Company", $prefix . "lco", false, "", 100);
        $this->address1 = new StringTextField("Street", "{$prefix}ads1", true, "", 100);
        $this->address2 = new StringTextField("Street", "{$prefix}ads2", false, "", 100);
        $this->city = new StringTextField("City", "{$prefix}adct", true, "", 50);
        $this->state_id = new IntegerSelect("State", "{$prefix}adst", true);
        $this->state = new StringTextField("Province", "{$prefix}adpv", false, "", 50);
        $this->zip = new StringTextField("Zip Code", "{$prefix}adzc", true, "", 20);
        $this->country = new StringTextField("Country", "{$prefix}adcn", false, "", 100);
        $this->home_phone = new PhoneNumberTextField("Daytime phone number", $prefix . "ldp", false, "", 20);
        $this->work_phone = new PhoneNumberTextField("Evening phone number", $prefix . "lep", false, "", 20);
        $this->mobile_phone = new PhoneNumberTextField("Evening phone number", $prefix . "lep", false, "", 20);
        $this->fax = new PhoneNumberTextField("Fax number", $prefix . "fax", false, "", 20);
        $this->email = new EmailTextField("Email", $prefix . "lem", false, "", 200);
        $this->title = new EmailTextField("Title", $prefix . "ttl", false, "", 50);
        $this->url = new StringTextField("URL", $prefix . "lur", false, "", 255);
        $this->latitude = new FloatTextField("Latitude", "stlt", false);
        $this->longitude = new FloatTextField("Longitude", "stlg", false);
        $this->state_abbrev = "";
        $this->fullname = "";
    }

    /**
     * Checks database to see if any identical addresses already exist.
     * @return bool True/false indicating that an existing record was or was not found.
     * @throws Exception
     */
    public function checkForDuplicate(): bool
    {
        $query = 'SEL'.'ECT id FROM `address` ' .
            'WHERE IFNULL(location, \'\') = ? ' .
            'AND IFNULL(address1, \'\') = ? ' .
            'AND IFNULL(zip, \'\') = ? ';
        $rs = $this->fetchRecords($query, 'sss', $this->location->value, $this->address1->value, $this->zip->value);
        return (count($rs) > 0);
    }

    /**
     * Formats plain string full address based on current address values stored in the object.
     * @param string $style (Optional) Token indicating the type of formatting to apply to the address.
     * Options are "oneline"|"html"|"google". Defaults to "oneline".
     * @param bool $include_name (Optional) Flag to include the individual's first and last name. Defaults to FALSE.
     * @return string Formatted address.
     * @throws InvalidValueException
     */
    public function formatAddress(string $style = Address::FORMAT_ADDRESS_ONE_LINE, bool $include_name = false): string
    {
        switch ($style) {
            case Address::FORMAT_ADDRESS_ONE_LINE:
                return ($this->formatOneLineAddress());
            case Address::FORMAT_ADDRESS_HTML:
                return ($this->formatHTMLAddress($include_name));
            case Address::FORMAT_ADDRESS_GOOGLE:
                return ($this->formatGoogleAddress());
            default:
                throw new InvalidValueException("Unhandled address format: \"$style\".");
        }
    }

    /**
     * Returns string formatted with current city, state, country, and zip code values.
     * @return string Formatted location description.
     */
    public function formatCity(): string
    {
        $state = ($this->state_abbrev != '') ? $this->state_abbrev : $this->state->safeValue();
        $city_parts = array_filter(array(trim($this->city->safeValue()),
            trim($state),
            trim($this->country->safeValue())));
        $city = join(', ', $city_parts);
        $parts = array_filter(array($city, trim($this->zip->safeValue())));
        return join(' ', $parts);
    }

    /**
     * Formats a more informal version of a contact's name, without a salutation.
     * @return string Formatted contact name.
     */
    public function formatContactName(): string
    {
        $parts = array_filter(array(
            trim('' . $this->first_name->value),
            trim('' . $this->last_name->value)
        ));
        return (join(' ', $parts));
    }

    /**
     * Formats full name based on current salutation, first name, and last name values stored in the object.
     * @return string Formatted full name.
     */
    public function formatFullName(): string
    {
        $parts = array_filter(array(trim('' . $this->salutation->value),
            trim('' . $this->first_name->value),
            trim('' . $this->last_name->value)));
        return (join(' ', $parts));
    }

    /**
     * Formats full address formatted for Google API calls using current address values stored in the object.
     * @return string Formatted address.
     */
    public function formatGoogleAddress(): string
    {
        return (urlencode($this->formatOneLineAddress()));
    }

    /**
     * Formats full address html markup based on current address values stored in the object.
     * @param bool $include_name (Optional) Flag to include the individual's first and last name. Defaults to FALSE.
     * @return string Formatted address.
     */
    public function formatHTMLAddress(bool $include_name = false): string
    {
        $parts = array();
        if ($include_name === true) {
            $parts[] = $this->formatFullName();
            $parts[] = trim('' . $this->company->value);
        }
        $parts[] = trim('' . $this->address1->value);
        $parts[] = trim('' . $this->address2->value);

        if ($this->state_id->value > 0) {
            try {
                $this->readStateProperties();
            } catch (Exception $ex) {
                /* continue */
            }
        }
        $parts[] = $this->formatCity();
        $parts = array_filter($parts);
        if (count($parts) > 0) {
            return ("<div>" . join("</div>\n<div>", $parts) . "</div>\n");
        }
        return ('');
    }

    /**
     * Formats address into a single line.
     * @return string Formatted address.
     */
    public function formatOneLineAddress(): string
    {
        $address = $this->appendSeparator($this->address1->safeValue()) .
            $this->appendSeparator($this->address2->safeValue()) .
            $this->city->safeValue();
        if ($this->state_abbrev || $this->state->safeValue()) {
            if ($this->state_abbrev) {
                $address .= $this->prependSeparator($this->state_abbrev);
            } elseif ($this->state->safeValue()) {
                $address .= $this->prependSeparator($this->state->safeValue());
            }
        } else {
            $address = preg_replace('/, $/', '', $address) . $this->prependSeparator($this->country->value);
        }
        $address = preg_replace('/, $/', '', $address) . $this->prependSeparator($this->zip->value, '');
        return ($address);
    }

    /**
     * Format any available street address information into a single string.
     * @param int|null $limit (Optional) Limit the size of the string returned to $limit characters.
     * @return string
     */
    public function formatStreet(?int $limit = null): string
    {
        $parts = array_filter(array(trim('' . $this->address1->value),
            trim('' . $this->address2->value)));
        $address = join(', ', $parts);
        if ($limit > 0) {
            return (substr($address, 0, $limit));
        }
        return ($address);
    }

    /**
     * Returns a query to use to store the current object property values in the database.
     * @return array
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    public function generateUpdateQuery(): array
    {
        $this->connectToDatabase();
        return array('CALL addressUpdate(@insert_id,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            'ssssssisssssssssssii',
            &$this->salutation->value,
            &$this->first_name->value,
            &$this->last_name->value,
            &$this->address1->value,
            &$this->address2->value,
            &$this->city->value,
            &$this->state_id->value,
            &$this->state->value,
            &$this->zip->value,
            &$this->country->value,
            &$this->home_phone->value,
            &$this->work_phone->value,
            &$this->fax->value,
            &$this->email->value,
            &$this->company->value,
            &$this->title->value,
            &$this->location->value,
            &$this->url->value,
            &$this->latitude->value,
            &$this->longitude->value);
    }

    /**
     * Address data template file name getter
     * @return string
     */
    public static function getAddressDataTemplate(): string
    {
        return static::$address_data_template;
    }

    /**
     * @inheritDoc
     */
    public function getContentLabel(): string
    {
        /* consider removing hard-coding */
        return 'Address';
    }

    /**
     * Street address data template file name getter
     * @return string
     */
    public static function getStreetAddressDataTemplate(): string
    {
        return static::$street_address_data_template;
    }

    /**
     * Returns current Google Maps API key value.
     * @return string Current Google Maps API key value
     */
    public static function getGMapAPIKey(): string
    {
        return static::$gmap_api_key;
    }

    /**
     * Returns TRUE if any valid address data is found assigned to the object's properties.
     * @return bool
     */
    public function hasAddressData(): bool
    {
        $st = ($this->state_id->value == null || $this->state_id->value < 1) ? ('') : ('' . $this->state_id->value);
        $data = substr('' . $this->address1->value, 0, 1) .
            substr('' . $this->address2->value, 0, 1) .
            substr('' . $this->city->value, 0, 1) .
            substr('' . $this->state->value, 0, 1) .
            substr($this->state_abbrev, 0, 1) .
            $st .
            substr('' . $this->zip->value, 0, 1);
        return (strlen($data) > 0);
    }

    /**
     * Indicates if any form data has been entered for the current instance of the object.
     * @return bool Returns true if editing an existing record, a title has been entered, or if any gallery images
     * have been uploaded. Most likely should be overridden in derived classes.
     */
    public function hasData(): bool
    {
        return ($this->id->value !== null ||
            $this->first_name->value ||
            $this->last_name->value ||
            $this->email->value ||
            $this->location->value ||
            $this->address1->value ||
            $this->address2->value ||
            $this->city->value ||
            $this->state_id->value > 0);
    }

    /**
     * Returns the state id from the database that matches the current value of the object's state name property.
     * @return int|null
     * @throws Exception
     */
    public function lookupStateByName(): ?int
    {
        $this->id->value = null;
        if ('' === $this->state->value || null === $this->state->value) {
            return null;
        }
        $data = $this->fetchRecords('CALL lookupStateByName(?)', 's', $this->state->value);
        if (1 > count($data)) {
            return null;
        }
        $this->id->setInputValue($data[0]->id);
        return $this->id->value;
    }

    /**
     * Retrieves longitude and latitude for the current address using Google Maps API.
     * @throws Exception
     */
    public function lookupMapPosition()
    {
        /**** LOOKUP BASED ON STREET ADDRESS, CITY & STATE ****/
        if ($this->city->value && $this->state_id->value > 0) {
            if (!$this->lookupMapPositionByAddress()) {
                /**** try zip code ****/
                if ($this->zip->value) {
                    $this->lookupMapPositionByZip();
                }
            }
        } /**** LOOKUP BASED ON ZIP CODE ****/
        else if ($this->zip->value) {
            $this->lookupMapPositionByZip();
        }
    }

    /**
     * Retrieves longitude and latitude using street address. Updates the internal longitude and latitude properties.
     * @returns bool TRUE if longitude and latitude values were found. FALSE otherwise.
     * @throws Exception
     */
    public function lookupMapPositionByAddress(): bool
    {
        $this->longitude->value = "0";
        $this->latitude->value = "0";

        if ($this->state->value == '') {
            $this->readStateProperties();
        }
        $address = $this->city->value . ", " . $this->state->value;
        if ($this->address1->value) {
            $address = $this->address1->value . ", " . $address;
        }

        $xml = new DOMDocument();
        if ($xml->load(self::GOOGLE_MAPS_URI() . urlencode($address))) {
            $nl = $xml->getElementsByTagName("coordinates");
            if ($nl->length >= 0 && is_object($nl->item(0))) {
                $n = $nl->item(0)->firstChild;
                if ($n) {
                    list($this->longitude->value, $this->latitude->value) = explode(',', $n->nodeValue);
                } else {
                    unset($xml);
                    return (false);
                }
            } else {
                unset($xml);
                return (false);
            }
        }
        return (true);
    }

    /**
     * Retrieves longitude and latitude values from zip code database.
     * @throws Exception
     */
    public function lookupMapPositionByZip()
    {
        $query = "SEL" . "ECT latitude, longitude FROM `zips` WHERE zipcode = " . $this->zip->escapeSQL($this->mysqli);
        $rs = $this->fetchRecords($query);
        if (count($rs) > 0) {
            list($this->longitude->value, $this->latitude->value) = $rs[0];
        }
    }

    /**
     * Saves internal data values as hidden form inputs.
     * @throws ResourceNotFoundException
     */
    function preserveInForm(array $excluded_keys = [])
    {
        $template_path = static::$common_cms_template_path . $this::getAddressDataTemplate();
        $context = array('input' => $this);
        ContentUtils::renderTemplate($template_path, $context);
    }

    /**
     * Inject property values into html form as hidden inputs.
     * @return void
     * @throws ResourceNotFoundException
     */
    function preservePhysicalAddressInForm()
    {
        $template_path = static::$common_cms_template_path . $this::getStreetAddressDataTemplate();
        $context = array('input' => $this);
        ContentUtils::renderTemplate($template_path, $context);
    }

    /**
     * @inheritDoc
     */
    public function read(): Address
    {
        if ($this->id->value === null || $this->id->value < 1) {
            return $this;
        }
        parent::read();

        $this->fullname = $this->first_name->value;
        if ($this->first_name->value && $this->last_name->value) {
            $this->fullname .= " ";
        }
        $this->fullname .= $this->last_name->value;

        $this->readStateProperties();
        return $this;
    }

    /**
     * Retrieves extended state properties (name and abbreviation) from database.
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function readStateProperties()
    {
        if ($this->state_id->value === null || $this->state_id->value < 1) {
            return;
        }
        $query = "SELECT `name`, `abbrev` FROM `states` WHERE id = ?";
        $data = $this->fetchRecords($query, 'i', $this->state_id->value);
        if (0 < count($data)) {
            $this->state->value = $data[0]->name;
            $this->state_abbrev = $data[0]->abbrev;
        } else {
            throw new RecordNotFoundException("Requested state properties not found.");
        }
    }

    /**
     * Commits current object data to the database.
     * @param bool $do_gmap_lookup (Optional) Flag to lookup address longitude and latitude using Google Maps API. Defaults to false.
     * @param string $content_label (Optional) label describing the content type used to format error messages.
     * @throws Exception
     */
    public function save(bool $do_gmap_lookup = false, string $content_label = "address")
    {
        if (!$this->hasData()) {
            throw new Exception(ucfirst($content_label) . " has nothing to save.");
        }

        if ($do_gmap_lookup === true) {
            /* translate street address into longitude and latitude */
            $this->lookupMapPosition();
        }

        parent::save();
    }

    /**
     * Sets Google Maps API key property value.
     * @param string $key Google Maps API key value.
     */
    public static function setGMapAPIKey(string $key)
    {
        static::$gmap_api_key = $key;
    }

    /**
     * Street address data template file name setter
     * @param string $filename
     */
    public static function setStreetAddressDataTemplate(string $filename)
    {
        static::$street_address_data_template = $filename;
    }

    /**
     * Address data template file name setter
     * @param string $filename
     */
    public static function setAddressDataTemplate(string $filename)
    {
        static::$address_data_template = $filename;
    }

    /**
     * Validates address form data.
     * @param string[] $exclude_properties List of properties to exclude from validation.
     * @throws Exception Throws exception if any invalid form data is detected. A detailed description of the errors is found through the GetMessage() routine of the Exception object.
     */
    public function validateInput(array $exclude_properties = [])
    {
        try {
            parent::validateInput();
        } catch (Exception $ex) {
            /* continue */
        }

        if ($this->hasValidationErrors()) {
            throw new ContentValidationException("Error validating address.");
        }
        return null;
    }

    /**
     * Validates email addresses used with member accounts to make sure that they are valid email addresses, and that they do not already exist in the database.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws Exception
     */
    public function validateUniqueEmail()
    {
        if ($this->email->value) {
            $this->connectToDatabase();
            $query = "SELECT c.email " .
                "FROM `address` c " .
                "INNER JOIN site_user l on c.id = l.contact_id " .
                "WHERE (c.email LIKE " . $this->email->escapeSQL($this->mysqli) . ") ";
            if ($this->id->value > 0) {
                $query .= "AND (l.id != {$this->id->value}) ";
            }
            $rs = $this->fetchRecords($query);
            $matches = count($rs);

            if ($matches > 0) {
                $this->email->error = true;
                $err_msg = "The email address \"{$this->email->value}\" has already been registered.";
                $this->addValidationError($err_msg);
                throw new ContentValidationException($err_msg);
            }
        }
    }
}