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
use Littled\Request\StringSelect;
use Littled\Request\StringTextField;
use Littled\Validation\Validation;
use DOMDocument;
use Exception;

/**
 * Class Address
 * @package Littled\Account
 */
class Address extends SerializedContent
{
	const ID_PARAM = "adid";
	const LOCATION_PARAM = "adlo";

	const TABLE_NAME = "address";
	public static function TABLE_NAME (): string
    {
	    return (self::TABLE_NAME);
	}

    /**
     * Inserts google maps key into URL to use to access google maps.
     * @return string google maps uri
     */
	public static function GOOGLE_MAPS_URI(): string
    {
        return ("https://maps.googleapis.com/maps/api/geocode/xml?key=".GMAP_KEY."&address=");
    }

	/** @var IntegerInput Address record id. */
	public $id;
	/** @var StringSelect $salutation */
	public $salutation;
	/** @var StringTextField $firstname */
	public $firstname;
	/** @var StringTextField $lastname */
	public $lastname;
	/** @var StringTextField $location */
	public $location;
	/** @var StringTextField $company */
	public $company;
	/** @var StringTextField $address1 */
	public $address1;
	/** @var StringTextField $address2 */
	public $address2;
	/** @var StringTextField $city */
	public $city;
	/** @var IntegerSelect $state_id */
	public $state_id;
	/** @var StringTextField $province */
	public $province;
	/** @var StringTextField $zip */
	public $zip;
	/** @var StringTextField $country */
	public $country;
	/** @var StringTextField $day_phone */
	public $day_phone;
	/** @var StringTextField $eve_phone */
	public $eve_phone;
	/** @var EmailTextField $email */
	public $email;
	/** @var StringTextField $url */
	public $url;
	/** @var FloatTextField $latitude */
	public $latitude;
	/** @var FloatTextField $longitude */
	public $longitude;
	/** @var string $state Full state name. */
	public $state;
	/** @var string $state_abbrev Abbreviated state name. */
	public $state_abbrev;
	/** @var string $fullname Combined first and last name. */
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
		$this->firstname = new StringTextField("First Name", "{$prefix}adfn", true, "", 50);
		$this->lastname = new StringTextField("Last Name", "{$prefix}adln", true, "", 50);
		$this->location = new StringTextField("Location name", $prefix.self::LOCATION_PARAM, false, "", 200);
		$this->company = new StringTextField("Company", $prefix."lco", false, "", 100);
		$this->address1 = new StringTextField("Street", "{$prefix}ads1", true, "", 100);
		$this->address2 = new StringTextField("Street", "{$prefix}ads2", false, "", 100);
		$this->city = new StringTextField("City", "{$prefix}adct", true, "", 50);
		$this->state_id = new IntegerSelect("State", "{$prefix}adst", true);
		$this->province = new StringTextField("Province", "{$prefix}adpv", false, "", 50);
		$this->zip = new StringTextField("Zip Code", "{$prefix}adzc", true, "", 20);
		$this->country = new StringTextField("Country", "{$prefix}adcn", false, "", 100);
		$this->day_phone = new StringTextField("Daytime phone number", $prefix."ldp", false, "", 20);
		$this->eve_phone = new StringTextField("Evening phone number", $prefix."lep", false, "", 20);
		$this->email = new EmailTextField("Email", $prefix."lem", false, "", 200);
		$this->url = new StringTextField("URL", $prefix."lur", false, "", 255);
		$this->latitude = new FloatTextField("Latitude", "stlt", false);
		$this->longitude = new FloatTextField("Longitude", "stlg", false);
		$this->state = "";
		$this->state_abbrev = "";
		$this->fullname = "";
	}

    /**
     * Checks database to see if any identical addresses already exist.
     * @return boolean True/false indicating that an existing record was or was not found.
     * @throws Exception
     */
    public function checkForDuplicate ()
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
     * @param string[optional] $style Token indicating the type of formatting to apply to the address.
     * Options are "oneline"|"html"|"google". Defaults to "oneline".
     * @param boolean[optional] $include_name Flag to include the individual's first and last name. Defaults to FALSE.
     * @return string Formatted address.
     * @throws InvalidValueException
     */
    public function formatAddress($style="oneline", $include_name=false)
    {
        switch ($style)
        {
            case "oneline":
                return($this->formatOneLineAddress());
            case "html":
                return($this->formatHTMLAddress($include_name));
            case "google":
                return($this->formatGoogleAddress());
            default:
                throw new InvalidValueException("Unhandled address format: \"{$style}\".");
        }
    }

	/**
	 * Returns string formatted with current city, state, country, and zip code values.
	 * @return string Formatted location description.
	 */
	public function formatCity()
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
    public function formatContactName()
    {
    	$parts = array_filter(array(
    		trim(''.$this->firstname->value),
		    trim(''.$this->lastname->value)
	    ));
    	return(join(' ', $parts));
    }

	/**
	 * Formats full name based on current salutation, first name, and last name values stored in the object.
	 * @return string Formatted full name.
	 */
	public function formatFullName()
	{
		$parts = array_filter(array(trim(''.$this->salutation->value),
			trim(''.$this->firstname->value),
			trim(''.$this->lastname->value)));
		return(join(' ', $parts));
	}

	/**
     * Formats full address formatted for Google API calls using current address values stored in the object.
     * @return string Formatted address.
     */
    public function formatGoogleAddress()
    {
        return (urlencode($this->formatOneLineAddress()));
    }

    /**
     * Formats full address html markup based on current address values stored in the object.
     * @param boolean[optional] $include_name Flag to include the individual's first and last name. Defaults to FALSE.
     * @return string Formatted address.
     */
    public function formatHTMLAddress($include_name=false)
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
    public function formatOneLineAddress()
    {
        $addr = $this->appendSeparator($this->address1->value).
            $this->appendSeparator($this->address2->value).
            $this->city->value;
        if ($this->state_abbrev || $this->state) {
            if ($this->state_abbrev) {
                $addr .= $this->prependSeparator($this->state_abbrev);
            } elseif ($this->state) {
                $addr .= $this->prependSeparator($this->state);
            }
        }
        else
        {
            $addr = preg_replace('/, $/', '', $addr).$this->prependSeparator($this->country->value);
        }
        $addr = preg_replace('/, $/', '', $addr).$this->prependSeparator($this->zip->value, '');
        return ($addr);
    }

    /**
     * Format any available street address information into a single string.
     * @param int|null $limit (Optional) Limit the size of the string returned to $limit characters.
     * @return string
     */
    public function formatStreet($limit=null)
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
     * Indicates if any form data has been entered for the current instance of the object.
     * @return boolean Returns true if editing an existing record, a title has been entered, or if any gallery images
     * have been uploaded. Most likely should be overridden in derived classes.
     */
    public function hasData ()
    {
        return ($this->id->value!==null ||
            $this->firstname->value ||
            $this->lastname->value ||
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
     * @throws Exception
     */
    public function lookupMapPositionByAddress ( )
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
        $query = "SELECT latitude, longitude FROM `zips` WHERE zipcode = ".$this->zip->escapeSQL($this->mysqli);
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
    function preserveInForm ()
    {
    	$template = CMS_COMMON_TEMPLATE_DIR."forms/data/address_class_data.php";
    	$context = array('input' => $this);
    	PageContent::render($template, $context);
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
		if ($this->id->value===null || $this->id->value < 1)
		{
		    return;
		}
        parent::read();

        $this->fullname = $this->firstname->value;
        if ($this->firstname->value && $this->lastname->value) {
            $this->fullname .= " ";
        }
        $this->fullname .= $this->lastname->value;

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
        if ($this->state_id->value===null || $this->state_id->value<1)
        {
            throw new InvalidValueException("Invalid state id value.");
        }
        $query = "SELECT `name`, `abbrev` FROM `states` WHERE id = {$this->state_id->value}";
        $rs = $this->fetchRecords($query);
        if (count($rs) > 0)
        {
            list($this->state, $this->state_abbrev) = array_values((array)$rs[0]);
        }
        else
        {
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
        if ($do_gmap_lookup===true)
        {
            /* translate street address into longitude and latitude */
            $this->lookupMapPosition();
        }
        parent::save();
	}

	/**
	 * Validates address form data.
     * @param array[optional] $exclude_properties List of properties to exclude from validation.
	 * @throws Exception Throws exception if any invalid form data is detected. A detailed description of the errors is found through the GetMessage() routine of the Exception object.
	 */
	public function validateInput ($exclude_properties=[])
	{
		try
        {
		    parent::validateInput();
		}
		catch(Exception $ex)
        {
		    /* continue */
		}

		if ($this->email->value)
		{
			if (!Validation::validateEmailAddress($this->email->value))
			{
				$this->addValidationError("Email address is not in a recognized format.");
			}
		}		

		if ($this->validationErrors)
		{
			throw new ContentValidationException("Error validating address.");
		}
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