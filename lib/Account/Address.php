<?php

namespace Littled\Account;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\EmailTextField;
use Littled\Request\FloatTextField;
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
    public const ID_KEY = 'adid';
    public const LOCATION_KEY = 'adlo';
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
        return ('https://maps.googleapis.com/maps/api/geocode/xml?key=' . static::$gmap_api_key . '&address=');
    }

    public StringSelect $salutation;
    public StringTextField $first_name;
    public StringTextField $last_name;
    public StringTextField $location;
    public StringTextField $organization;
    public StringTextField $address1;
    public StringTextField $address2;
    public StringTextField $city;
    public State $state;
    public StringTextField $non_us_state;
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
     */
    function __construct()
    {
        parent::__construct();
        $this->id->setKey(self::ID_KEY)
            ->setLabel('Address id')
            ->setAsNotRequired();
        $this->salutation = new StringSelect('Salutation', 'adsl', false, '', 10);
        $this->first_name = new StringTextField('First Name', 'adfn', true, '', 50);
        $this->last_name = new StringTextField('Last Name', 'adln', true, '', 50);
        $this->location = new StringTextField('Location name', self::LOCATION_KEY, false, '', 200);
        $this->organization = new StringTextField('Company', 'lco', false, '', 100);
        $this->address1 = new StringTextField('Street', 'ads1', true, '', 100);
        $this->address2 = new StringTextField('Street', 'ads2', false, '', 100);
        $this->city = new StringTextField('City', 'adct', true, '', 50);
        $this->state = (new State())
            ->setRecordsetPrefix('state_')
            ->applyInputKeyPrefix('a');
        $this->state->id
            ->setLabel('State')
            ->setKey('stateId')
            ->setAsRequired();
        $this->non_us_state = new StringTextField('Non US State', 'nonUSState', false, '', 100);
        $this->zip = new StringTextField('Zip Code', 'adzc', true, '', 20);
        $this->country = new StringTextField('Country', 'adcn', false, '', 100);
        $this->home_phone = new PhoneNumberTextField('Daytime phone number', 'ldp', false, '', 20);
        $this->work_phone = new PhoneNumberTextField('Evening phone number', 'lep', false, '', 20);
        $this->mobile_phone = new PhoneNumberTextField('Evening phone number', 'lep', false, '', 20);
        $this->fax = new PhoneNumberTextField('Fax number', 'fax', false, '', 20);
        $this->email = new EmailTextField('Email', 'lem', false, '', 200);
        $this->title = new EmailTextField('Title', 'ttl', false, '', 50);
        $this->url = new StringTextField('URL', 'lur', false, '', 255);
        $this->latitude = new FloatTextField('Latitude', 'stlt', false);
        $this->longitude = new FloatTextField('Longitude', 'stlg', false);
        $this->state_abbrev = '';
        $this->fullname = '';
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
     * Options are 'oneline'|'html'|'google'. Defaults to 'oneline'.
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
        $state = ($this->state->abbrev->value != '') ? $this->state->abbrev->value : $this->state->name->safeValue();
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
            $parts[] = trim('' . $this->organization->value);
        }
        $parts[] = trim('' . $this->address1->value);
        $parts[] = trim('' . $this->address2->value);

        if ($this->state->getRecordId()) {
            try {
                $this->readStateProperties();
            } catch (Exception $ex) {
                /* continue */
            }
        }
        $parts[] = $this->formatCity();
        $parts = array_filter($parts);
        if (count($parts) > 0) {
            return ('<div>' . join("</div>\n<div>", $parts) . "</div>\n");
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
        if ($this->state->getRecordId()) {
            if ($this->state->abbrev->value) {
                $address .= $this->prependSeparator($this->state->abbrev->safeValue());
            } elseif ($this->state->getRecordId()) {
                $address .= $this->prependSeparator($this->state->name->safeValue());
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
            &$this->state->id->value,
            &$this->non_us_state->value,
            &$this->zip->value,
            &$this->country->value,
            &$this->home_phone->value,
            &$this->work_phone->value,
            &$this->fax->value,
            &$this->email->value,
            &$this->organization->value,
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
        return $this->address1->hasData() ||
            $this->address2->hasData() ||
            $this->city->hasData() ||
            $this->state->hasData() ||
            $this->zip->hasData();
    }

    /**
     * @inheritDoc
     */
    public function hasRecordData(): bool
    {
        return ($this->first_name->hasData() ||
            $this->last_name->hasData() ||
            $this->email->hasData() ||
            $this->location->hasData() ||
            $this->hasAddressData());
    }

    /**
     * Returns the state id from the database that matches the current value of the object's state name property.
     * @return int|null
     * @throws Exception
     */
    public function lookupStateByName(): ?int
    {
        $this->id->value = null;
        if (!$this->state->name->hasData() && !$this->state->abbrev->hasData()) {
            return null;
        }
        $state = $this->state->name->value ?: $this->state->abbrev->value;
        $data = $this->fetchRecords('CALL lookupStateByName(?)', 's', $state);
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
        if ($this->city->hasData() && $this->state->getRecordId()) {
            if (!$this->lookupMapPositionByAddress()) {
                /**** try zip code ****/
                if ($this->zip->hasData()) {
                    $this->lookupMapPositionByZip();
                }
            }
        } /**** LOOKUP BASED ON ZIP CODE ****/
        else if ($this->zip->hasData()) {
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
        $this->longitude->value = '0';
        $this->latitude->value = '0';

        if ($this->state->getRecordId()) {
            $this->readStateProperties();
        }
        $address = $this->city->value . ', ' . $this->state->name->value;
        if ($this->address1->value) {
            $address = $this->address1->value . ', ' . $address;
        }

        $xml = new DOMDocument();
        if ($xml->load(self::GOOGLE_MAPS_URI() . urlencode($address))) {
            $nl = $xml->getElementsByTagName('coordinates');
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
        $query = 'SEL' . 'ECT latitude, longitude FROM `zips` WHERE zipcode = ' . $this->zip->escapeSQL($this->mysqli);
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
     * Retrieves extended state properties (name and abbreviation) from database.
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function readStateProperties()
    {
        if (!$this->state->getRecordId()) {
            return;
        }
        $query = 'SELECT `name`, `abbrev` FROM `states` WHERE id = ?';
        $data = $this->fetchRecords($query, 'i', $this->state->id->value);
        if (0 < count($data)) {
            $this->state->name->setInputValue($data[0]->name);
            $this->state->abbrev->setInputValue($data[0]->abbrev);
        } else {
            throw new RecordNotFoundException('Requested state properties not found.');
        }
    }

    /**
     * Commits current object data to the database.
     * @param bool $do_gmap_lookup (Optional) Flag to lookup address longitude and latitude using Google Maps API. Defaults to false.
     * @param string $content_label (Optional) label describing the content type used to format error messages.
     * @throws Exception
     */
    public function save(bool $do_gmap_lookup = false, string $content_label = 'address')
    {
        if (!$this->hasData()) {
            throw new Exception(ucfirst($content_label) . ' has nothing to save.');
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
            $query = 'SELECT c.email ' .
                'FROM `address` c ' .
                'INNER JOIN site_user l on c.id = l.contact_id ' .
                'WHERE (c.email LIKE ' . $this->email->escapeSQL($this->mysqli) . ') ';
            if ($this->id->value > 0) {
                $query .= 'AND (l.id != {$this->id->value}) ';
            }
            $rs = $this->fetchRecords($query);
            $matches = count($rs);

            if ($matches > 0) {
                $this->email->error = true;
                $err_msg = 'The email address \'{$this->email->value}\' has already been registered.';
                $this->addValidationError($err_msg);
                throw new ContentValidationException($err_msg);
            }
        }
    }
}