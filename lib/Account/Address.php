<?php

namespace Littled\Account;

use Littled\App\LittledGlobals;
use Littled\Exception\CommitException;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidRequestException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Exception\ResourceUnavailableException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\EmailTextField;
use Littled\Request\FloatTextField;
use Littled\Request\PhoneNumberTextField;
use Littled\Request\StringSelect;
use Littled\Request\StringTextarea;
use Littled\Request\StringTextField;
use Exception;
use Littled\Utility\LittledUtility;


/**
 * Class Address
 * @package Littled\Account
 */
class Address extends SerializedContent
{
    protected static string $table_name = 'address';

    /** @var string Google maps api key */
    protected static string $gmap_api_key;
    protected static string $api_keys_path;
    protected static string $address_data_template = 'forms/data/address_class_data.php';
    protected static string $street_address_data_template = 'forms/data/street_address_form_data.php';
    public const ID_KEY = 'adid';
    public const LOCATION_KEY = 'adlo';
    // possible values for formatting address data into strings
    public const FORMAT_ADDRESS_ONE_LINE = 'one_line';
    public const FORMAT_ADDRESS_HTML = 'html';
    public const FORMAT_ADDRESS_GOOGLE = 'google';
    protected const GOOGLE_MAPS_API_URI = 'https://maps.googleapis.com/maps/api/geocode/json?key=%s&address=';

    public StringSelect         $salutation;
    public StringTextField      $first_name;
    public StringTextField      $last_name;
    public StringTextField      $location;
    public StringTextField      $organization;
    public StringTextField      $address1;
    public StringTextField      $address2;
    public StringTextField      $city;
    public State                $state;
    public StringTextField      $non_us_state;
    public StringTextField      $zip;
    public StringTextField      $country;
    public PhoneNumberTextField $home_phone;
    public PhoneNumberTextField $work_phone;
    public PhoneNumberTextField $fax;
    public PhoneNumberTextField $mobile_phone;
    public EmailTextField       $email;
    public StringTextField      $title;
    public StringTextField      $url;
    public FloatTextField       $latitude;
    public FloatTextField       $longitude;
    public StringTextarea       $notes;
    /** @deprecated Use $state->abbreviation instead */
    public string               $state_abbrev;
    /** @var string Combined first and last name. */
    public string               $fullname;

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
        $this->organization = new StringTextField('Organization', 'lco', false, '', 100);
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
        $this->home_phone = new PhoneNumberTextField('Daytime phone number', 'hPho', false, '', 20);
        $this->work_phone = new PhoneNumberTextField('Evening phone number', 'wPho', false, '', 20);
        $this->mobile_phone = new PhoneNumberTextField('Evening phone number', 'mPho', false, '', 20);
        $this->fax = new PhoneNumberTextField('Fax number', 'fax', false, '', 20);
        $this->email = new EmailTextField('Email', 'lem', false, '', 200);
        $this->title = new EmailTextField('Title', 'ttl', false, '', 50);
        $this->url = new StringTextField('URL', 'lur', false, '', 255);
        $this->latitude = new FloatTextField('Latitude', 'stlt', false);
        $this->longitude = new FloatTextField('Longitude', 'stlg', false);
        $this->notes = (new StringTextarea())
            ->setLabel('Notes')
            ->setKey('addrNotes')
            ->setAsNotRequired()
            ->setSizeLimit(1000);
        $this->state->abbrev->value = '';
        $this->fullname = '';
    }

    /**
     * Checks a database to see if any identical addresses already exist.
     * @return bool True/false indicating that an existing record was or was not found.
     * @throws FailedQueryException
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
        return match ($style) {
            Address::FORMAT_ADDRESS_ONE_LINE => ($this->formatOneLineAddress()),
            Address::FORMAT_ADDRESS_HTML => ($this->formatHTMLAddress($include_name)),
            Address::FORMAT_ADDRESS_GOOGLE => ($this->formatGoogleAddress()),
            default => throw new InvalidValueException("Unhandled address format: \"$style\"."),
        };
    }

    /**
     * Returns string formatted with current city, state, country, and zip code values.
     * @return string Formatted location description.
     */
    public function formatCity(): string
    {
        $state = ($this->state->abbrev->value != '') ? $this->state->abbrev->value : $this->state->name->formatValueMarkup();
        $city_parts = array_filter(array(trim($this->city->formatValueMarkup()),
            trim($state),
            trim($this->country->formatValueMarkup())));
        $city = join(', ', $city_parts);
        $parts = array_filter(array($city, trim($this->zip->formatValueMarkup())));
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
     * Formats full address formatted for Google AI calls using current address values stored in the object.
     * @return string Formatted address.
     */
    public function formatGoogleAddress(): string
    {
        return (urlencode($this->formatOneLineAddress()));
    }

    /**
     * Formats full address HTML markup based on current address values stored in the object.
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
            } catch (Exception) {
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
        $address = $this->appendSeparator($this->address1->formatValueMarkup()) .
            $this->appendSeparator($this->address2->formatValueMarkup()) .
            $this->city->formatValueMarkup();
        if ($this->state->getRecordId()) {
            if ($this->state->abbrev->value) {
                $address .= $this->prependSeparator($this->state->abbrev->formatValueMarkup());
            } elseif ($this->state->name->value) {
                $address .= $this->prependSeparator($this->state->name->formatValueMarkup());
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
     * Inserts a Google Maps key into the URL to use to access Google Maps.
     * @return string google maps uri
     * @throws ConfigurationUndefinedException
     */
    protected static function getGoogleMapsURI(): string
    {
        return sprintf(static::GOOGLE_MAPS_API_URI, static::getGMapAPIKey());
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
     * @throws ConfigurationUndefinedException
     */
    public static function getGMapAPIKey(): string
    {
        if (!isset(static::$gmap_api_key) && isset(static::$api_keys_path)) {
            $json = json_decode(file_get_contents(static::getAPIKeysPath()));
            if (isset($json->{'google-api-key'})) {
                static::$gmap_api_key = $json->{'google-api-key'};
            }
        }
        return static::$gmap_api_key ?? '';
    }

    /**
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public static function getAPIKeysPath(): string
    {
        if ((static::$api_keys_path ?? '') === '') {
            throw new ConfigurationUndefinedException('API keys path not set.');
        }
        return LittledUtility::joinPaths(LittledGlobals::getKeysPath(), static::$api_keys_path);
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
     * Returns the tax rate associated with the current state object property. Returns the value from the following in order of precedence:
     * - Internal sales tax value in the state object.
     * - Sales tax rate associated with the record in the database matching the state objects record id value.
     * - Sales tax rate associated with the state name or abbreviation in the database.
     * @return float|null
     * @throws FailedQueryException
     */
    public function lookupSalesTaxRate(): ?float
    {
        $this->state->stashRecordsetPrefix();
        try {
            return $this->state->lookupSalesTaxRate();
        }
        finally {
            $this->state->restoreRecordsetPrefix();
        }
    }

    /**
     * Hydrates the state object with record data from the database that matches either the current state record id value
     * or the state name or abbreviation property. Returns the state record id if a matching record is found.
     * @return int|null
     * @throws FailedQueryException
     */
    public function lookupStateByName(): ?int
    {
        return $this->state->lookupByName();
    }

    /**
     * Returns the state id from the database that matches the current value of the object's state name or abbreviation property.
     * @return int|null
     * @throws FailedQueryException
     */
    public function lookupStateId(): ?int
    {
        return $this->state->lookupStateId();
    }

    /**
     * Retrieves longitude and latitude for the current address using Google Maps API.
     * @throws FailedQueryException
     * @throws InvalidRequestException
     * @throws RecordNotFoundException
     */
    public function lookupMapPosition(): void
    {
        if ($this->city->hasData() && $this->state->getRecordId()) {
            $this->lookupMapPositionByAddress();
            return;
        }
        if ($this->zip->hasData()) {
            $this->lookupMapPositionByZip();
        }
    }

    /**
     * Retrieves longitude and latitude using street address. Updates the internal longitude and latitude properties.
     * @return void
     * @throws InvalidRequestException
     * @throws RecordNotFoundException
     */
    public function lookupMapPositionByAddress(): void
    {
        $this->longitude->value = '0';
        $this->latitude->value = '0';

        try {
            if ($this->state->getRecordId()) {
                $this->readStateProperties();
            }
            $address = $this->city->value . ', ' . $this->state->name->value;
            if ($this->address1->value) {
                $address = $this->address1->value . ', ' . $address;
            }

            $response = file_get_contents(static::getGoogleMapsURI() . urlencode($address));
            $json = json_decode($response);
            switch ($json->status) {
                case 'OK':
                    $this->longitude->value = $json->results[0]->geometry->location->lng;
                    $this->latitude->value = $json->results[0]->geometry->location->lat;
                    break;
                case 'REQUEST_DENIED':
                    throw new InvalidRequestException($json->error_message);
                case 'ZERO_RESULTS':
                    throw new InvalidRequestException('No results found for address: "' . $address . '"');
                default:
                    // @codeCoverageIgnoreStart
                    throw new InvalidRequestException("Unhandled maps api status: \"$json->status\"");
                // @codeCoverageIgnoreEnd
            }
        }
        // @codeCoverageIgnoreStart
        catch (ConfigurationUndefinedException|FailedQueryException $e) {
            throw new InvalidRequestException($e->throwMessage('Unable to look up address coordinates'));
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Retrieves longitude and latitude values from zip code database.
     * @throws FailedQueryException
     */
    public function lookupMapPositionByZip(): void
    {
        $query = 'SEL' . 'ECT latitude, longitude FROM `zips` WHERE zipcode = ?';
        $rs = $this->fetchRecords($query, 's', $this->zip->value);
        /* zips table is not implemented yet */
        // @codeCoverageIgnoreStart
        if (count($rs) > 0) {
            list($this->longitude->value, $this->latitude->value) = $rs[0];
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Inject property values into an HTML form as hidden inputs.
     * @return void
     * @throws ResourceNotFoundException
     */
    public function preservePhysicalAddressInForm(): void
    {
        $addr_keys = ['address1', 'address2', 'city', 'zip', 'country'];
        $state_keys = ['id', 'name'];
        foreach($addr_keys as $key) {
            $this->{$key}->saveInForm();
        }
        foreach($state_keys as $key) {
            $this->state->{$key}->saveInForm();
        }
    }

    /**
     * Retrieves extended state properties (name and abbreviation) from the database.
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     */
    public function readStateProperties(): void
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
     * @param bool $do_coordinate_lookup (Optional) Flag to look up address longitude and latitude using Google Maps API. Defaults to false.
     * @param string $content_label (Optional) label describing the content type used to format error messages.
     * @throws CommitException
     * @throws ContentValidationException
     * @throws ResourceUnavailableException
     */
    public function save(bool $do_coordinate_lookup = false, string $content_label = 'address'): void
    {
        if (!$this->hasRecordData()) {
            throw new ContentValidationException(ucfirst($content_label) . ' has nothing to save.');
        }

        try {
            if ($do_coordinate_lookup) {
                /* translate street address into longitude and latitude */
                $this->lookupMapPosition();
            }
        }
        // @codeCoverageIgnoreStart
        catch (FailedQueryException|InvalidRequestException|RecordNotFoundException $e) {
            throw new ResourceUnavailableException($e->throwMessage('Unable to look up address coordinates'));
        }
        // @codeCoverageIgnoreEnd

        try {
            parent::save();
        }
        // @codeCoverageIgnoreStart
        catch(FailedQueryException |
            ContentValidationException |
            InvalidValueException |
            NotImplementedException |
            RecordNotFoundException $e) {
            throw new CommitException($e->throwMessage("Error saving $content_label record"));
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Sets Google Maps API key property value.
     * @param string $key Google Maps API key value.
     */
    public static function setGMapAPIKey(string $key): void
    {
        static::$gmap_api_key = $key;
    }

    /**
     * Street address data template file name setter
     * @param string $filename
     */
    public static function setStreetAddressDataTemplate(string $filename): void
    {
        static::$street_address_data_template = $filename;
    }

    /**
     * Address data template file name setter
     * @param string $filename
     */
    public static function setAddressDataTemplate(string $filename): void
    {
        static::$address_data_template = $filename;
    }

    /**
     * @param string $path
     * @return void
     */
    protected static function setAPIKeysPath(string $path): void
    {
        static::$api_keys_path = $path;
    }

    /**
     * Email setter.
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): static
    {
        $this->email->value = $email;
        return $this;
    }

    /**
     * First name setter.
     * @param string $name
     * @return $this
     */
    public function setFirstName(string $name): static
    {
        $this->first_name->value = $name;
        return $this;
    }

    /**
     * Home phone setter.
     * @param string $number
     * @return $this
     */
    public function setHomePhone(string $number): static
    {
        $this->home_phone->value = $number;
        return $this;
    }

    /**
     * Last name setter.
     * @param string $name
     * @return $this
     */
    public function setLastName(string $name): static
    {
        $this->last_name->value = $name;
        return $this;
    }

    /**
     * Validates email addresses used with member accounts to make sure that they are valid email addresses and that they do not already exist in the database.
     * @return void
     * @throws ContentValidationException
     * @throws FailedQueryException
     */
    public function validateUniqueEmail(): void
    {
        if ($this->email->value) {
            $query = 'CALL lookupUserEmail(?,?)';
            $result = $this->fetchRecords($query, 'si', $this->email->value, $this->id->value);
            if ($result[0]->count > 0) {
                $this->email->error = true;
                $err_msg = "The email address \"{$this->email->value}\" has already been registered.";
                $this->addValidationError($err_msg);
                throw new ContentValidationException($err_msg);
            }
        }
    }
}