<?php
namespace Littled\Account;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageContent;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\EmailTextField;
use Littled\Request\FloatTextField;
use Littled\Request\IntegerInput;
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
    /** @var string Google maps api key */
    protected static $gmap_api_key;
    protected static $address_data_template = 'forms/data/address_class_data.php';

	const ID_PARAM = "adid";
	const LOCATION_PARAM = "adlo";

	// possible values for formatting address data into strings
	const FORMAT_ADDRESS_ONE_LINE   = 'one_line';
	const FORMAT_ADDRESS_HTML       = 'html';
	const FORMAT_ADDRESS_GOOGLE     = 'google';

	const TABLE_NAME = "address";
	public static function TABLE_NAME (): string {
	    return (self::TABLE_NAME);
	}

    /**
     * Inserts Google Maps key into URL to use to access Google Maps.
     * @return string google maps uri
     */
	public static function GOOGLE_MAPS_URI(): string
    {
        return ("https://maps.googleapis.com/maps/api/geocode/xml?key=".static::$gmap_api_key."&address=");
    }

	/** @var IntegerInput Address record id. */
	public $id;
	/** @var StringSelect */
	public $salutation;
	/** @var StringTextField */
	public $first_name;
	/** @var StringTextField */
	public $last_name;
	/** @var StringTextField */
	public $location;
	/** @var StringTextField */
	public $company;
	/** @var StringTextField */
	public $address1;
	/** @var StringTextField */
	public $address2;
	/** @var StringTextField */
	public $city;
	/** @var IntegerSelect */
	public $state_id;
	/** @var StringTextField */
	public $state;
	/** @var StringTextField */
	public $zip;
	/** @var StringTextField */
	public $country;
	/** @var PhoneNumberTextField */
	public $home_phone;
	/** @var PhoneNumberTextField */
	public $work_phone;
    /** @var PhoneNumberTextField */
    public $mobile_phone;
	/** @var EmailTextField */
	public $email;
	/** @var StringTextField */
	public $url;
	/** @var FloatTextField */
	public $latitude;
	/** @var FloatTextField */
	public $longitude;
	/** @var string Abbreviated state name. */
	public $state_abbrev;
	/** @var string Combined first and last name. */
	public $fullname;

	/**
	 * Class constructor.
	 * @param string $prefix (Optional) prefix to prepend to form elements.
	 */
	function __construct ( $prefix="" ) 
	{
		parent::__construct();
		$this->id = new IntegerInput("Address ID", $prefix.self::ID_PARAM, false, null);
		$this->salutation = new StringSelect("Salutation", "{$prefix}adsl", false, "", 10);
		$this->first_name = new StringTextField("First Name", "{$prefix}adfn", true, "", 50);
		$this->last_name = new StringTextField("Last Name", "{$prefix}adln", true, "", 50);
		$this->location = new StringTextField("Location name", $prefix.self::LOCATION_PARAM, false, "", 200);
		$this->company = new StringTextField("Company", $prefix."lco", false, "", 100);
		$this->address1 = new StringTextField("Street", "{$prefix}ads1", true, "", 100);
		$this->address2 = new StringTextField("Street", "{$prefix}ads2", false, "", 100);
		$this->city = new StringTextField("City", "{$prefix}adct", true, "", 50);
		$this->state_id = new IntegerSelect("State", "{$prefix}adst", true);
		$this->state = new StringTextField("Province", "{$prefix}adpv", false, "", 50);
		$this->zip = new StringTextField("Zip Code", "{$prefix}adzc", true, "", 20);
		$this->country = new StringTextField("Country", "{$prefix}adcn", false, "", 100);
		$this->home_phone = new PhoneNumberTextField("Daytime phone number", $prefix."ldp", false, "", 20);
		$this->work_phone = new PhoneNumberTextField("Evening phone number", $prefix."lep", false, "", 20);
        $this->mobile_phone = new PhoneNumberTextField("Evening phone number", $prefix."lep", false, "", 20);
		$this->email = new EmailTextField("Email", $prefix."lem", false, "", 200);
		$this->url = new StringTextField("URL", $prefix."lur", false, "", 255);
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
    public function checkForDuplicate (): bool
    {
        $query = "SELECT id FROM `address` ".
            "WHERE (IFNULL(location, '') = ".$this->location->escapeSQL($this->mysqli).") ".
            "AND (IFNULL(address1, '') = ".$this->address1->escapeSQL($this->mysqli).") ".
            "AND (IFNULL(zip, '') = ".$this->zip->escapeSQL($this->mysqli).") ";
        $rs = $this->fetchRecords($query);
        return (count($rs)>0);
    }

    /**
     * Formats plain string full address based on current address values stored in the object.
     * @param string $style (Optional) Token indicating the type of formatting to apply to the address.
     * Options are "oneline"|"html"|"google". Defaults to "oneline".
     * @param bool $include_name (Optional) Flag to include the individual's first and last name. Defaults to FALSE.
     * @return string Formatted address.
     * @throws InvalidValueException
     */
    public function formatAddress(string $style=Address::FORMAT_ADDRESS_ONE_LINE, bool $include_name=false): string
    {
        switch ($style)
        {
	        case Address::FORMAT_ADDRESS_ONE_LINE:
                return($this->formatOneLineAddress());
	        case Address::FORMAT_ADDRESS_HTML:
                return($this->formatHTMLAddress($include_name));
	        case Address::FORMAT_ADDRESS_GOOGLE:
                return($this->formatGoogleAddress());
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
		$state = ($this->state_abbrev!==null && $this->state_abbrev!='') ? $this->state_abbrev : $this->state;
		$city_parts = array_filter(array(trim(''.$this->city->value),
			trim(''.$state),
			trim(''.$this->country->value)));
		$city = join(', ', $city_parts);
		$parts = array_filter(array($city, trim(''.$this->zip->value)));
		return join(' ', $parts);
	}

	/**
	 * Formats a more informal version of a contact's name, without a salutation.
	 * @return string Formatted contact name.
	 */
    public function formatContactName(): string
    {
    	$parts = array_filter(array(
    		trim(''.$this->first_name->value),
		    trim(''.$this->last_name->value)
	    ));
    	return(join(' ', $parts));
    }

	/**
	 * Formats full name based on current salutation, first name, and last name values stored in the object.
	 * @return string Formatted full name.
	 */
	public function formatFullName(): string
	{
		$parts = array_filter(array(trim(''.$this->salutation->value),
			trim(''.$this->first_name->value),
			trim(''.$this->last_name->value)));
		return(join(' ', $parts));
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
    public function formatHTMLAddress(bool $include_name=false): string
    {
        $parts = array();
        if ($include_name==true)
        {
        	array_push($parts, $this->formatFullName());
        	array_push($parts, trim(''.$this->company->value));
        }
        array_push($parts, trim(''.$this->address1->value));
        array_push($parts, trim(''.$this->address2->value));

        if ($this->state_id->value>0 && !$this->state)
        {
            try
            {
                $this->readStateProperties();
            }
            catch (Exception $ex)
            {
                /* continue */
            }
        }
		array_push($parts, $this->formatCity());
        $parts = array_filter($parts);
        if (count($parts) > 0) {
        	return ("<div>".join("</div>\n<div>", $parts)."</div>\n");
        }
		return ('');
    }

    /**
     * Formats address into a single line.
     * @return string Formatted address.
     */
    public function formatOneLineAddress(): string
    {
        $address = $this->appendSeparator(''.$this->address1->value).
            $this->appendSeparator(''.$this->address2->value).
            $this->city->value;
        if ($this->state_abbrev || $this->state) {
            if ($this->state_abbrev) {
                $address .= $this->prependSeparator($this->state_abbrev);
            } elseif ($this->state) {
                $address .= $this->prependSeparator($this->state);
            }
        }
        else
        {
            $address = preg_replace('/, $/', '', $address).$this->prependSeparator($this->country->value);
        }
        $address = preg_replace('/, $/', '', $address).$this->prependSeparator($this->zip->value, '');
        return ($address);
    }

    /**
     * Format any available street address information into a single string.
     * @param int|null $limit (Optional) Limit the size of the string returned to $limit characters.
     * @return string
     */
    public function formatStreet(?int $limit=null): string
    {
    	$parts = array_filter(array(trim(''.$this->address1->value),
		    trim(''.$this->address2->value)));
    	$addr = join(', ', $parts);
        if ($limit>0)
        {
            return(substr($addr, 0, $limit));
        }
        return ($addr);
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
     * Returns current Google Maps API key value.
     * @return string Current Google Maps API key value
     */
    public function getGMapAPIKey(): string
    {
        return static::$gmap_api_key;
    }

    /**
     * Indicates if any form data has been entered for the current instance of the object.
     * @return bool Returns true if editing an existing record, a title has been entered, or if any gallery images
     * have been uploaded. Most likely should be overridden in derived classes.
     */
    public function hasData (): bool
    {
        return ($this->id->value!==null ||
            $this->first_name->value ||
            $this->last_name->value ||
            $this->email->value ||
            $this->location->value ||
            $this->address1->value ||
            $this->address2->value ||
            $this->city->value ||
            $this->state_id->value>0);
    }

    /**
     * Retrieves longitude and latitude for the current address using Google Maps API.
     * @throws Exception
     */
    public function lookupMapPosition ()
    {
        /**** LOOKUP BASED ON STREET ADDRESS, CITY & STATE ****/
        if ($this->city->value && $this->state_id->value>0)
        {
            if (!$this->lookupMapPositionByAddress())
            {
                /**** try zip code ****/
                if ($this->zip->value)
                {
                    $this->lookupMapPositionByZip();
                }
            }
        }

        /**** LOOKUP BASED ON ZIP CODE ****/
        else if ($this->zip->value)
        {
            $this->lookupMapPositionByZip();
        }
    }

    /**
     * Retrieves longitude and latitude using street address. Updates the internal longitude and latitude properties.
     * @returns bool TRUE if longitude and latitude values were found. FALSE otherwise.
     * @throws Exception
     */
    public function lookupMapPositionByAddress ( ): bool
    {
        $this->longitude->value = "0";
        $this->latitude->value = "0";

        if (!$this->state)
        {
            $this->readStateProperties();
        }
        $addr = $this->city->value.", ".$this->state;
        if ($this->address1->value)
        {
            $addr = $this->address1->value.", ".$addr;
        }

        $xml = new DOMDocument();
        if ($xml->load(self::GOOGLE_MAPS_URI().urlencode($addr)))
        {
            $nl = $xml->getElementsByTagName("coordinates");
            if ($nl->length>=0 && is_object($nl->item(0)))
            {
                $n = $nl->item(0)->firstChild;
                if ($n)
                {
                    list($this->longitude->value, $this->latitude->value) = explode(',', $n->nodeValue);
                }
                else
                {
                    unset($xml);
                    return(false);
                }
            }
            else
            {
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
    public function lookupMapPositionByZip ( )
    {
        $query = "SEL"."ECT latitude, longitude FROM `zips` WHERE zipcode = ".$this->zip->escapeSQL($this->mysqli);
        $rs = $this->fetchRecords($query);
        if (count($rs) > 0)
        {
            list($this->longitude->value, $this->latitude->value) = $rs[0];
        }
    }

    /**
     * Saves internal data values as hidden form inputs.
     * @throws ResourceNotFoundException
     */
    function preserveInForm (array $excluded_keys=[])
    {
    	$template_path = static::$common_cms_template_path.$this::getAddressDataTemplate();
    	$context = array('input' => $this);
    	PageContent::render($template_path, $context);
    }

    /**
	 * Retrieves address data from database.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     * @throws Exception
	 */
	public function read () 
	{
		if ($this->id->value===null || $this->id->value < 1) {
		    return;
		}
        parent::read();

        $this->fullname = $this->first_name->value;
        if ($this->first_name->value && $this->last_name->value) {
            $this->fullname .= " ";
        }
        $this->fullname .= $this->last_name->value;

        $this->readStateProperties();
    }

    /**
     * Retrieves extended state properties (name and abbreviation) from database.
     * @throws InvalidValueException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function readStateProperties ()
    {
        if ($this->state_id->value===null || $this->state_id->value<1) {
            throw new InvalidValueException("Invalid state id value.");
        }
        $query = "SELECT `name`, `abbrev` FROM `states` WHERE id = {$this->state_id->value}";
        $rs = $this->fetchRecords($query);
        if (count($rs) > 0) {
            list($this->state, $this->state_abbrev) = array_values((array)$rs[0]);
        }
        else {
        	throw new RecordNotFoundException("Requested state properties not found.");
        }
    }

	/**
	 * Commits current object data to the database.
	 * @param boolean[optional] $do_gmap_lookup Flag to lookup address longitude and latitude using Google Maps API. Defaults to false.
     * @throws Exception
	 */
	public function save($do_gmap_lookup=false)
	{
        if ($do_gmap_lookup===true) {
            /* translate street address into longitude and latitude */
            $this->lookupMapPosition();
        }
        parent::save();
	}

    /**
     * Sets Google Maps API key property value.
     * @param string $key Google Maps API key value.
     */
	public function setGMapAPIKey(string $key)
    {
        static::$gmap_api_key = $key;
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
     * @param array[optional] $exclude_properties List of properties to exclude from validation.
	 * @throws Exception Throws exception if any invalid form data is detected. A detailed description of the errors is found through the GetMessage() routine of the Exception object.
	 */
	public function validateInput ($exclude_properties=[])
	{
		try {
		    parent::validateInput();
		}
		catch(Exception $ex) {
		    /* continue */
		}

		if ($this->validationErrors) {
			throw new ContentValidationException("Error validating address.");
		}
        return null;
	}

	/**
	 * Validates email addresses used with member accounts to make sure that they are valid email addresses, and that they do not already exist in the database.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 */
	public function validateUniqueEmail()
	{
		if ($this->email->value)
		{
			$this->connectToDatabase();
			$query = "SELECT c.email ".
				"FROM `address` c ".
				"INNER JOIN site_user l on c.id = l.contact_id ".
				"WHERE (c.email LIKE ".$this->email->escapeSQL($this->mysqli).") ";
			if ($this->id->value>0)
			{
				$query .= "AND (l.id != {$this->id->value}) ";
			}
			$rs = $this->fetchRecords($query);
			$matches = count($rs);

			if ($matches>0)
			{
				$this->email->error = true;
				$err_msg = "The email address \"{$this->email->value}\" has already been registered.";
				$this->addValidationError($err_msg);
				throw new ContentValidationException($err_msg);
			}
		}
	}
}