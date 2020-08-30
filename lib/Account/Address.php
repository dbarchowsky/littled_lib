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
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\FloatTextField;
use Littled\Request\IntegerInput;
use Littled\Request\IntegerSelect;
use Littled\Request\StringSelect;
use Littled\Request\StringTextField;
use Littled\Validation\Validation;
use \DOMDocument;
use \Exception;

/**
 * Class Address
 * @package Littled\Account
 */
class Address extends SerializedContent
{
	const ID_PARAM = "adid";
	const LOCATION_PARAM = "adlo";

	const TABLE_NAME = "address";
	public static function TABLE_NAME ()
    {
	    return (self::TABLE_NAME);
	}

    /**
     * Inserts google maps key into URL to use to access google maps.
     * @return string google maps uri
     */
	public static function GOOGLE_MAPS_URI()
    {
        return ("http://maps.google.com/maps/geo?key=".GMAP_KEY."&output=xml&q=");
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
	/** @var StringTextField $email */
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
		$this->email = new StringTextField("Email", $prefix."lem", false, "", 200);
		$this->url = new StringTextField("URL", $prefix."lur", false, "", 255);
		$this->latitude = new StringTextField("Latitude", "stlt", false);
		$this->longitude = new StringTextField("Longitude", "stlg", false);
		
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
     * @param $style string (Optional) Token indicating the type of formatting to apply to the address.
     * Options are "oneline"|"html"|"google". Defaults to "oneline".
     * @return string Formatted address.
     * @throws InvalidValueException
     */
    public function formatAddress($style="oneline")
    {
        switch ($style)
        {
            case "oneline":
                return($this->formatOneLineAddress());
            case "html":
                return($this->formatHTMLAddress());
            case "google":
                return($this->formatGoogleAddress());
            default:
                throw new InvalidValueException("Unhandled address format: \"{$style}\".");
        }
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
     * @param boolean $include_name (Optional) flag to include the individual's first and last name. Defaults to FALSE.
     * @return string Formatted address.
     */
    public function formatHTMLAddress($include_name=false)
    {
        $addr = "";
        if ($include_name==true)
        {
            if ($this->firstname->value || $this->lastname->value)
            {
                $addr .= "<div>".$this->fullname()."</div>\n";
            }
            if ($this->company->value)
            {
                $addr .= "<div>{$this->company->value}</div>\n";
            }
        }
        if ($this->address1->value)
        {
            $addr .= "<div>{$this->address1->value}</div>\n";
        }
        if ($this->address2->value)
        {
            $addr .= "<div>{$this->address2->value}</div>\n";
        }

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

        $locale = "";
        if ($this->city->value && $this->state_abbrev)
        {
            $locale .= $this->city->value.", ".$this->state_abbrev;
        }
        else
        {
            if ($this->city->value)
            {
                $locale .= $this->city->value;
            }
            if ($this->state)
            {
                $locale .= $this->state;
            }
        }
        if ($this->zip->value)
        {
            $locale .= " ".$this->zip->value;
        }
        if ($locale)
        {
            $locale = "<div>{$locale}</div>\n";
        }
        return ($addr.$locale);
    }

    /**
     * Formats address into a single line.
     * @return string Formatted address.
     */
    public function formatOneLineAddress()
    {
        $addr = $this->appendSeparator($this->address1->value).
            $this->appendSeparator($this->address2->value).
            $this->prependSeparator($this->city->value);
        if ($this->state_abbrev || $this->state) {
            if ($this->state_abbrev) {
                $addr .= $this->prependSeparator($this->state_abbrev);
            } elseif ($this->state) {
                $addr .= $this->prependSeparator($this->state);
            }
        }
        else
        {
            $addr .= $this->prependSeparator($this->country->value);
        }
        $addr .= $this->prependSeparator($this->zip->value, '');
        return ($addr);
    }

    /**
     * Format any available street address information into a single string.
     * @param $limit integer|null (optional) Limit the size of the string returned to $limit characters.
     * @return string
     */
    public function formatStreet($limit=null)
    {
        $addr = "";
        if ($this->address1->value)
        {
            $addr = $this->address1->value;
            if ($this->address2->value)
            {
                $addr .= ", ".$this->address2->value;
            }
        }
        elseif ($this->address2->value)
        {
            $addr = $this->address2->value;
        }
        if ($limit>0)
        {
            $addr = substr($addr, $limit);
        }
        return ($addr);
    }

    /**
     * Formats full name based on current salutation, first name, and last name values stored in the object.
     * @return string Formatted full name.
     */
    public function fullname()
    {
        $fullname = "";
        if ($this->salutation->value)
        {
            $fullname = $this->salutation->value." ";
        }
        if ($this->firstname->value && $this->lastname->value)
        {
            $fullname .= $this->firstname->value." ".$this->lastname->value;
        }
        else
        {
            $fullname .= $this->firstname->value.$this->lastname->value;
        }
        return ($fullname);
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
     */
    function preserveInForm ()
    {
        include (ADMIN_TEMPLATE_DIR."forms/data/address_class_data.php");
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
     * @throws Exception
     */
    public function readStateProperties ()
    {
        if ($this->state_id->value===null || $this->state_id->value<1)
        {
            return;
        }
        $query = "SELECT `name`, `abbrev` FROM `states` WHERE id = {$this->state_id->value}";
        $rs = $this->fetchRecords($query);
        if (count($rs) > 0)
        {
            list($this->state, $this->state_abbrev) = array_values((array)$rs[0]);
        }
    }

	/**
	 * Commits current object data to the database.
	 * @param boolean $do_gmap_lookup (Optional) Flag to lookup address longitude and latitude using Google Maps API. Defaults to false.
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
     * @param $exclude_properties array List of properties to exclude from validation.
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
}